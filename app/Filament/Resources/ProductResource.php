<?php

namespace App\Filament\Resources;

use BackedEnum;
use UnitEnum;
use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Product;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cube';

    protected static ?string $navigationLabel = 'Daftar Stok';

    protected static ?string $modelLabel = 'Stok';

    protected static ?string $pluralModelLabel = 'Daftar Stok';

    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Stok')
                ->description('Kelola data master produk untuk inventory dan POS.')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('Nama Stok')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state): void {
                                $set('slug', Str::slug($state ?? ''));
                                $set('supplier_branch', $state ?? '');
                            })
                            ->columnSpan(1),
                        TextInput::make('barcode')
                            ->label('Barcode')
                            ->maxLength(100)
                            ->helperText('Boleh sama jika kondisi atau waktu input berbeda.')
                            ->columnSpan(1),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),
                        TextInput::make('category_name')
                            ->label('Kategori')
                            ->required()
                            ->formatStateUsing(fn (?Product $record): string => $record?->category?->name ?? '')
                            ->columnSpan(1),
                        TextInput::make('brand_name')
                            ->label('Brand')
                            ->required()
                            ->formatStateUsing(fn (?Product $record): string => $record?->brand?->name ?? '')
                            ->columnSpan(1),
                        TextInput::make('supplier_locked_name')
                            ->label('Nama Supplier')
                            ->visible(fn (string $operation): bool => $operation === 'edit')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (?Product $record): string => $record?->latestBatch?->supplier?->name ?? '-')
                            ->columnSpan(1),
                        TextInput::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->formatStateUsing(fn (?Product $record): int => (int) ($record?->latestBatch?->stock ?? 0))
                            ->columnSpan(1),
                        Toggle::make('is_active')
                            ->label('Aktif untuk dijual')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                        Textarea::make('description')
                            ->label('Deskripsi')
                            ->rows(4)
                            ->columnSpanFull(),
                    ]),
                ]),
            Section::make('Informasi Supplier')
                ->visible(fn (string $operation): bool => $operation === 'create')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('supplier_name')
                            ->label('Nama Supplier')
                            ->required()
                            ->formatStateUsing(fn (?Product $record): string => $record?->latestBatch?->supplier?->name ?? ''),
                        TextInput::make('supplier_branch')
                            ->label('Nama Stok')
                            ->readOnly()
                            ->formatStateUsing(fn (?Product $record): string => $record?->latestBatch?->supplier?->branch ?? ''),
                        TextInput::make('supplier_phone')
                            ->label('Nomor HP supplier')
                            ->formatStateUsing(fn (?Product $record): string => $record?->latestBatch?->supplier?->phone ?? ''),
                        TextInput::make('supplier_address')
                            ->label('Alamat supplier')
                            ->formatStateUsing(fn (?Product $record): string => $record?->latestBatch?->supplier?->address ?? ''),
                        Textarea::make('supplier_note')
                            ->label('Catatan supplier')
                            ->rows(3)
                            ->formatStateUsing(fn (?Product $record): string => $record?->latestBatch?->supplier?->note ?? '')
                            ->columnSpanFull(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Stok')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Product $record): string => collect([
                        $record->barcode ? "Barcode: {$record->barcode}" : null,
                        $record->category?->name,
                        $record->brand?->name,
                    ])->filter()->implode(' • ')),
                TextColumn::make('category.name')
                    ->label('Kategori')
                    ->badge()
                    ->color('gray')
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label('Brand')
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('Stok Total')
                    ->state(fn (Product $record): int => $record->total_stock)
                    ->badge()
                    ->color(fn (int $state): string => $state <= 10 ? 'warning' : 'success')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Kategori'),
                SelectFilter::make('brand')
                    ->relationship('brand', 'name')
                    ->label('Brand'),
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Nonaktif',
                    ]),
                Filter::make('low_stock')
                    ->label('Stok menipis')
                    ->query(fn (Builder $query): Builder => $query->whereIn('products.id', function ($subQuery): void {
                        $subQuery
                            ->select('product_id')
                            ->from('product_batches')
                            ->whereNull('deleted_at')
                            ->where('is_active', true)
                            ->groupBy('product_id')
                            ->havingRaw('SUM(stock) <= 10');
                    })),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                \Filament\Actions\CreateAction::make()
                    ->label('Tambah Stok'),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['category', 'brand', 'batches', 'latestBatch.supplier']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }
}
