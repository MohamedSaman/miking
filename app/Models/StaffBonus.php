<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffBonus extends Model
{
    use HasFactory;

    protected $fillable = [
        'sale_id',
        'staff_id',
        'product_id',
        'quantity',
        'sale_type',
        'payment_method',
        'bonus_per_unit',
        'total_bonus',
    ];

    protected $casts = [
        'bonus_per_unit' => 'decimal:2',
        'total_bonus' => 'decimal:2',
    ];

    /**
     * Get the sale that owns the bonus
     */
    public function sale()
    {
        return $this->belongsTo(Sale::class);
    }

    /**
     * Get the staff member who earned the bonus
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Get the product associated with the bonus
     */
    public function product()
    {
        return $this->belongsTo(ProductDetail::class, 'product_id');
    }
}
