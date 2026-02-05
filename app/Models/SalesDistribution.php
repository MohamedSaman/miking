<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesDistribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'staff_name',
        'dispatch_location',
        'distance_km',
        'travel_expense',
        'handover_to',
        'status',
        'description',
        'products',
        'distribution_date',
        'invoice_no',
        'selection_type',
        'created_by',
    ];

    protected $casts = [
        'products' => 'array',
        'distribution_date' => 'date',
        'distance_km' => 'decimal:2',
        'travel_expense' => 'decimal:2',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }
}
