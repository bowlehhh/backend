<?php

namespace App\Filament\Pages;

use App\Models\Supplier;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AdminSuppliers extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-truck';

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $title = 'Daftar Supplier';

    protected static ?string $slug = 'admin-suppliers';

    protected string $view = 'filament.pages.admin-suppliers';

    public function getViewData(): array
    {
        return [
            'stats' => $this->getStats(),
            'suppliers' => $this->getSuppliers(),
        ];
    }

    private function getStats(): array
    {
        if (! Schema::hasTable('suppliers')) {
            return [
                'total' => 0,
                'active' => 0,
                'total_stock' => 0,
            ];
        }

        $total = Supplier::query()->count();
        $active = Supplier::query()->where('is_active', true)->count();

        $totalStock = 0;
        if (Schema::hasTable('product_batches')) {
            $totalStock = (int) DB::table('product_batches')
                ->when(Schema::hasColumn('product_batches', 'deleted_at'), fn ($query) => $query->whereNull('deleted_at'))
                ->sum('stock');
        }

        return [
            'total' => $total,
            'active' => $active,
            'total_stock' => $totalStock,
        ];
    }

    private function getSuppliers(): array
    {
        if (! Schema::hasTable('suppliers')) {
            return [];
        }

        return Supplier::query()
            ->with('productBatches.product:id,name')
            ->latest('id')
            ->get()
            ->map(function (Supplier $supplier): array {
                $productCount = $supplier->productBatches->pluck('product_id')->filter()->unique()->count();
                $stockTotal = (int) $supplier->productBatches->sum('stock');

                return [
                    'id' => $supplier->id,
                    'name' => $supplier->name,
                    'type' => $supplier->branch,
                    'phone' => $supplier->phone,
                    'address' => $supplier->address,
                    'is_active' => (bool) $supplier->is_active,
                    'product_count' => $productCount,
                    'stock_total' => $stockTotal,
                ];
            })
            ->toArray();
    }
}
