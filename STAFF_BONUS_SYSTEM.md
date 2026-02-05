# Staff Bonus System Implementation

## Overview
The Staff Bonus System automatically calculates and records bonuses for staff members based on:
- **Sale Type**: Wholesale or Retail
- **Payment Method**: Cash or Credit

## Database Structure

### New Table: `staff_bonuses`
Stores individual bonus records for each product sold by staff members.

**Columns:**
- `id` - Primary key
- `sale_id` - Foreign key to sales table
- `staff_id` - Foreign key to users table (the staff member)
- `product_id` - Foreign key to product_details table
- `quantity` - Number of units sold
- `sale_type` - Enum: 'wholesale' or 'retail'
- `payment_method` - Enum: 'cash' or 'credit'
- `bonus_per_unit` - Bonus amount per unit (decimal)
- `total_bonus` - Total bonus for this line item (decimal)
- `created_at`, `updated_at` - Timestamps

### Updated Table: `sales`
Added two new columns:
- `customer_type_sale` - Enum: 'wholesale' or 'retail' (determines which bonus to use)
- `payment_method` - Enum: 'cash' or 'credit' (determines which bonus to use)

## How It Works

### 1. Product Bonus Configuration
Each product has 4 bonus fields (configured in Settings):
- `retail_cash_bonus` - Bonus for retail cash sales
- `retail_credit_bonus` - Bonus for retail credit sales
- `wholesale_cash_bonus` - Bonus for wholesale cash sales
- `wholesale_credit_bonus` - Bonus for wholesale credit sales

### 2. Sale Creation Process
When a sale is created:
1. Staff selects **Sale Type** (Wholesale/Retail)
2. Staff selects **Payment Method** (Cash/Credit)
3. Sale is processed normally
4. **Automatic Bonus Calculation** happens:
   - System checks each product in the sale
   - Determines correct bonus based on sale type + payment method
   - Records bonus in `staff_bonuses` table

### 3. Bonus Calculation Logic
```
IF sale_type = 'wholesale' AND payment_method = 'cash'
   → Use product.wholesale_cash_bonus

IF sale_type = 'wholesale' AND payment_method = 'credit'
   → Use product.wholesale_credit_bonus

IF sale_type = 'retail' AND payment_method = 'cash'
   → Use product.retail_cash_bonus

IF sale_type = 'retail' AND payment_method = 'credit'
   → Use product.retail_credit_bonus
```

### 4. Total Bonus Calculation
For each product line:
```
total_bonus = bonus_per_unit × quantity
```

## Key Components

### 1. StaffBonusService (`app/Services/StaffBonusService.php`)
Main service class that handles:
- `calculateBonusesForSale($sale)` - Automatically calculates bonuses when sale is created
- `getTotalBonusForStaff($staffId, $startDate, $endDate)` - Get total bonuses for a staff member
- `getBonusBreakdown($staffId, $startDate, $endDate)` - Get detailed breakdown by type

### 2. StaffBonus Model (`app/Models/StaffBonus.php`)
Eloquent model with relationships to:
- Sale
- User (Staff)
- ProductDetail

### 3. Updated Components
- **SalesSystem.php** - Added `customerTypeSale` and `paymentMethod` properties
- **Sale Model** - Added `customer_type_sale` and `payment_method` to fillable
- **Sale Model** - Added `staffBonuses()` relationship

## Usage Example

### Creating a Sale with Bonuses
```php
// In SalesSystem component
$this->customerTypeSale = 'wholesale'; // or 'retail'
$this->paymentMethod = 'cash'; // or 'credit'
$this->createSale();

// Bonuses are automatically calculated and recorded!
```

### Retrieving Staff Bonuses
```php
use App\Services\StaffBonusService;

// Get total bonuses for a staff member this month
$totalBonus = StaffBonusService::getTotalBonusForStaff(
    $staffId, 
    now()->startOfMonth(), 
    now()->endOfMonth()
);

// Get detailed breakdown
$breakdown = StaffBonusService::getBonusBreakdown(
    $staffId,
    now()->startOfMonth(),
    now()->endOfMonth()
);

// Returns:
// [
//     'wholesale_cash' => 1500.00,
//     'wholesale_credit' => 800.00,
//     'retail_cash' => 2000.00,
//     'retail_credit' => 1200.00,
//     'total' => 5500.00
// ]
```

### Querying Bonuses
```php
// Get all bonuses for a specific sale
$sale = Sale::with('staffBonuses')->find($saleId);
$bonuses = $sale->staffBonuses;

// Get all bonuses for a staff member
$staff = User::with('staffBonuses')->find($staffId);
$bonuses = $staff->staffBonuses;

// Get bonuses for a specific product
$product = ProductDetail::with('staffBonuses')->find($productId);
$bonuses = $product->staffBonuses;
```

## Next Steps (UI Integration)

### 1. Add Sale Type & Payment Method to Sales Form
In `resources/views/livewire/admin/sales-system.blade.php`, add:
```html
<!-- Sale Type Selection -->
<div class="mb-3">
    <label class="form-label">Sale Type</label>
    <select wire:model="customerTypeSale" class="form-select">
        <option value="retail">Retail</option>
        <option value="wholesale">Wholesale</option>
    </select>
</div>

<!-- Payment Method Selection -->
<div class="mb-3">
    <label class="form-label">Payment Method</label>
    <select wire:model="paymentMethod" class="form-select">
        <option value="cash">Cash</option>
        <option value="credit">Credit</option>
    </select>
</div>
```

### 2. Create Staff Bonus Report Page
Create a Livewire component to display:
- Total bonuses per staff member
- Breakdown by sale type and payment method
- Date range filtering
- Export to Excel/PDF

### 3. Add Bonus Display to Staff Dashboard
Show staff members their earned bonuses:
- Today's bonuses
- This week's bonuses
- This month's bonuses
- Breakdown by type

## Benefits

✅ **Automatic Calculation** - No manual bonus entry needed
✅ **Accurate Tracking** - Every sale is tracked with correct bonus
✅ **Flexible** - Different bonuses for different sale types
✅ **Transparent** - Staff can see exactly how bonuses are calculated
✅ **Reportable** - Easy to generate bonus reports for payroll
✅ **Auditable** - Complete history of all bonuses earned

## Database Migration Commands

```bash
# Run migrations
php artisan migrate

# If you need to rollback
php artisan migrate:rollback --step=2

# Fresh migration (WARNING: Drops all tables)
php artisan migrate:fresh
```

## Files Created/Modified

### Created:
1. `database/migrations/2026_01_20_161316_create_staff_bonuses_table.php`
2. `database/migrations/2026_01_20_161614_add_customer_type_and_payment_method_to_sales_table.php`
3. `app/Models/StaffBonus.php`
4. `app/Services/StaffBonusService.php`

### Modified:
1. `app/Models/Sale.php` - Added fillable fields and staffBonuses relationship
2. `app/Livewire/Admin/SalesSystem.php` - Added properties and bonus calculation call
