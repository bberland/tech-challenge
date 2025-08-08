<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\MaintenanceOrderStatus;
use App\Enums\MaintenanceOrderPriority;

class MaintenanceOrder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'title',
        'asset_id',
        'status',
        'priority',
        'assigned_technician_id',
        'rejection_reason',
    ];

    protected $casts = [
        'status' => MaintenanceOrderStatus::class,
        'priority' => MaintenanceOrderPriority::class,
    ];

    protected static function booted(): void
    {
        static::creating(function ($order) {
            $order->status = MaintenanceOrderStatus::Created;
        });
    }

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function assignedTechnician()
    {
        return $this->belongsTo(User::class, 'assigned_technician_id');
    }
    
}
