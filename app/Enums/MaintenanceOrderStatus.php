<?php

namespace App\Enums;

enum MaintenanceOrderStatus: string
{
    case Created = 'created';
    case InProgress = 'in_progress';
    case PendingApproval = 'pending_approval';
    case Approved = 'approved';
    case Rejected = 'rejected';
    
    public function label(): string
    {
        return match($this) {
            self::Created => 'Created',
            self::InProgress => 'In Progress',
            self::PendingApproval => 'Pending Approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::Created => 'gray',
            self::InProgress => 'info',
            self::PendingApproval => 'warning',
            self::Approved => 'success',
            self::Rejected => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [$case->value => $case->label()])
            ->toArray();
    }
}
