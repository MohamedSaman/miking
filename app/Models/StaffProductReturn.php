<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffProductReturn extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'product_id',
        'return_quantity',
        'restock_quantity',
        'damaged_quantity',
        'status',
        'processed_by',
        'processed_at',
        'notes'
    ];

    protected $casts = [
        'processed_at' => 'datetime',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    public function product()
    {
        return $this->belongsTo(ProductDetail::class, 'product_id');
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
