<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages\CreateSupplier;
use App\Filament\Resources\SupplierResource\Pages\EditSupplier;
use App\Filament\Resources\SupplierResource\Pages\ListSuppliers;
use App\Filament\Resources\SupplierResource\Pages\ViewSupplier;
use App\Models\Supplier;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema as DbSchema;
use UnitEnum;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationLabel = 'Supplier';

    protected static ?string $modelLabel = 'Supplier';

    protected static ?string $pluralModelLabel = 'Supplier';

    protected static UnitEnum|string|null $navigationGroup = 'Inventory';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Informasi Supplier')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')->label('Nama Supplier')->required()->maxLength(255),
                        TextInput::make('branch')
                            ->label('Nama Barang')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function (?Supplier $record): string {
                                if (! $record) {
                                    return '';
                                }

                                if (! empty($record->branch)) {
                                    return $record->branch;
                                }

                                $latestBatch = $record->productBatches()->with('product')->latest('id')->first();

                                return $latestBatch?->product?->name ?? '';
                            })
                            ->maxLength(255),
                        TextInput::make('phone')->label('Telepon')->maxLength(255),
                        Toggle::make('is_active')->label('Aktif')->default(true),
                        Textarea::make('address')->label('Alamat')->rows(4)->columnSpanFull(),
                        Textarea::make('note')
                            ->label('Catatan')
                            ->visible(fn (): bool => DbSchema::hasColumn('suppliers', 'note'))
                            ->dehydrated(fn (): bool => DbSchema::hasColumn('suppliers', 'note'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Supplier')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('total_products')
                    ->label('Total Barang')
                    ->state(fn (Supplier $record): int => $record->productBatches()->distinct('product_id')->count())
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                TextColumn::make('total_stock')
                    ->label('Total Stok')
                    ->state(fn (Supplier $record): int => (int) $record->productBatches()->sum('stock'))
                    ->badge()
                    ->color('success'),
                TextColumn::make('product_names')
                    ->label('Nama Barang')
                    ->state(function (Supplier $record): string {
                        return $record->productBatches()
                            ->with('product:id,name')
                            ->get()
                            ->pluck('product.name')
                            ->filter()
                            ->unique()
                            ->take(3)
                            ->implode(', ');
                    })
                    ->placeholder('-')
                    ->wrap()
                    ->toggleable(),
                TextColumn::make('branch')
                    ->label('Catatan Barang')
                    ->toggleable(),
                TextColumn::make('phone')
                    ->label('Telepon')
                    ->toggleable(),
                TextColumn::make('address')
                    ->label('Alamat')
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                ViewAction::make()->label('Lihat Barang'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('productBatches');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSuppliers::route('/'),
            'create' => CreateSupplier::route('/create'),
            'view' => ViewSupplier::route('/{record}'),
            'edit' => EditSupplier::route('/{record}/edit'),
        ];
    }
}
