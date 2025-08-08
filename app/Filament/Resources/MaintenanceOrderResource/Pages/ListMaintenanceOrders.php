<?php

namespace App\Filament\Resources\MaintenanceOrderResource\Pages;

use App\Filament\Resources\MaintenanceOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use App\Enums\UserRole;

class ListMaintenanceOrders extends ListRecords
{
    protected static string $resource = MaintenanceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->visible(fn () => 
                    auth()->user()->role === UserRole::Supervisor
                ),
        ];
    }
}
