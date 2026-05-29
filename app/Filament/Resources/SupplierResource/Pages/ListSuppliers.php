<?php

namespace App\Filament\Resources\SupplierResource\Pages;

use App\Filament\Resources\SupplierResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSuppliers extends ListRecords
{
    protected static string $resource = SupplierResource::class;

    public function mount(): void
    {
        $this->redirect(url('/admin/admin-suppliers'));
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()->label('Tambah Supplier'),
        ];
    }
}
