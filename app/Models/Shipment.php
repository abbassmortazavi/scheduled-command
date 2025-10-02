<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shipment extends Model
{
    protected $fillable = [
        'tracking_number',
        'status',
        'last_status_check',
        'user_id',
    ];
    protected $casts = [
        'last_status_check' => 'datetime',
    ];
    /**
     * @param $query
     * @return mixed
     */
    public function scopeInProgress($query): mixed
    {
        return $query->where('status', 'in_progress');
    }
}
