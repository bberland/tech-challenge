<?php

namespace App\Filament\Resources\MaintenanceOrderResource\Pages;

use App\Filament\Resources\MaintenanceOrderResource;
use Filament\Resources\Pages\EditRecord;
use App\Enums\UserRole;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Forms\Components\Textarea;
use App\Enums\MaintenanceOrderStatus;

class EditMaintenanceOrder extends EditRecord
{
    protected static string $resource = MaintenanceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Mark as In Progress
            Action::make('markInProgress')
                ->label('Marcar como En Progreso')
                ->icon('heroicon-o-play')
                ->visible(fn () => 
                    auth()->user()->role === UserRole::Technician &&
                    $this->record->assigned_technician_id === auth()->id() &&
                    $this->record->status === MaintenanceOrderStatus::Created
                )
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => MaintenanceOrderStatus::InProgress,
                    ]);
                    Notification::make()
                        ->title('Orden marcada como En Progreso')
                        ->success()
                        ->send();
                }),

            // Mark as Pending Approval
            Action::make('markPendingApproval')
                ->label('Marcar como Pendiente de Aprobación')
                ->icon('heroicon-o-clock')
                ->visible(fn () =>
                    auth()->user()->role === UserRole::Technician &&
                    $this->record->assigned_technician_id === auth()->id() &&
                    $this->record->status === MaintenanceOrderStatus::InProgress
                )
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => MaintenanceOrderStatus::PendingApproval,
                    ]);

                    Notification::make()
                        ->title('Orden marcada como Pendiente de Aprobación')
                        ->success()
                        ->send();
                }),

            // Approve Order
            Action::make('approveOrder')
                ->label('Aprobar orden')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn () =>
                    auth()->user()->role === UserRole::Supervisor &&
                    $this->record->status === MaintenanceOrderStatus::PendingApproval
                )
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->update([
                        'status' => MaintenanceOrderStatus::Approved,
                        'rejection_reason' => null,
                    ]);
            
                    Notification::make()
                        ->title('Orden aprobada correctamente')
                        ->success()
                        ->send();
                }),

            // Reject Order
            Action::make('rejectOrder')
                ->label('Rechazar orden')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn () =>
                    auth()->user()->role === UserRole::Supervisor &&
                    $this->record->status === MaintenanceOrderStatus::PendingApproval
                )
                ->form([
                    Textarea::make('rejection_reason')
                        ->rows(3)
                        ->label('Razón del rechazo')
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->update([
                        'status' => MaintenanceOrderStatus::Rejected,
                        'rejection_reason' => $data['rejection_reason'],
                    ]);

                    $this->fillForm();
            
                    Notification::make()
                        ->title('Orden rechazada')
                        ->danger()
                        ->send();
                }),
        ];
    }
}
