# Staff Allocation System - Current Flow

## Overview
The staff allocation system manages product allocations from admin to staff members with proper inventory tracking.

## Key Features

### 1. **Immediate Stock Reduction**
**Behavior:**
- When admin allocates products to staff, admin's stock is **immediately reduced**
- This ensures accurate inventory tracking at all times
- Stock is deducted using FIFO (First-In-First-Out) method from ProductBatch

### 2. **Aggregated Product Allocations**
**Behavior:**
- Multiple allocations of the same product to the same staff member are **aggregated**
- Example: Allocate Product A (10 qty), then allocate Product A (5 qty) again = 1 record with 15 qty total
- The system checks for existing allocations and updates the quantity instead of creating duplicates

## Flow Diagram

```
ADMIN ALLOCATES PRODUCTS TO STAFF
         ↓
Check if product already allocated to this staff
         ↓
    YES: Update existing record (add quantity)
    NO: Create new record
         ↓
Admin Stock: REDUCED IMMEDIATELY ✓ (using FIFO)
Staff Allocated Qty: INCREASED ✓
         ↓
STAFF USES PRODUCTS
         ↓
STAFF CREATES RETURN REQUEST
         ↓
Staff Allocated Qty: REDUCED ✓
Return Status: PENDING
         ↓
ADMIN PROCESSES RETURN
         ↓
Admin chooses: Restock OR Damage
         ↓
Admin Stock: UPDATED (available or damage) ✓
Return Status: PROCESSED ✓
```

## Database Impact

### ProductStock Table (Admin Inventory)
**On Allocation:**
- `available_stock`: REDUCED by allocated quantity
- `sold_count`: INCREASED by allocated quantity
- `total_stock`: REDUCED (synced as available + damage)

**On Return Processing:**
- `available_stock`: INCREASED if marked as restock
- `damage_stock`: INCREASED if marked as damage
- `total_stock`: SYNCED (available + damage)

### ProductBatch Table
**On Allocation:**
- Uses FIFO to deduct from oldest batches first
- `remaining_quantity`: REDUCED
- `status`: Changed to 'depleted' when quantity reaches 0

**On Return Processing:**
- Stock added back to admin inventory
- New batches may be created if needed

### StaffProduct Table
**On Allocation:**
- If product exists: `quantity` INCREASED (aggregated)
- If new product: New record created
- `sold_quantity`: Tracks what staff has sold

**On Return Request:**
- `quantity`: REDUCED by return amount
- Return record created with 'pending' status

**On Return Processing:**
- No change to staff_products (already reduced on return request)

## Example Scenario

**Day 1 - Initial Allocation:**
```
Admin Stock: 100 units → 50 units (REDUCED) ✓
Staff A Allocated: 0 → 50 units ✓
```

**Day 2 - Second Allocation (Same Product):**
```
Admin Stock: 50 units → 20 units (REDUCED) ✓
Staff A Allocated: 50 → 80 units (AGGREGATED) ✓
```

**Day 3 - Staff Returns 20 Units:**
```
Staff Creates Return Request:
  Staff A Allocated: 80 → 60 units ✓
  Return Status: PENDING
  Admin Stock: 20 units (NO CHANGE YET)

Admin Processes Return (15 restock, 5 damage):
  Admin Available: 20 → 35 units ✓
  Admin Damage: 0 → 5 units ✓
  Admin Total: 20 → 40 units ✓
  Return Status: PROCESSED ✓
```

**Day 4 - Staff Sells 40 Units:**
```
Staff A Allocated: 60 units (unchanged)
Staff A Sold: 0 → 40 units ✓
Staff A Available: 60 → 20 units (calculated)
Admin Stock: 40 units (unchanged)
```

## Benefits

1. **Real-time Inventory**: Admin sees accurate available stock at all times
2. **No Duplicates**: Same product allocated multiple times = single aggregated record
3. **FIFO Compliance**: Stock deduction follows First-In-First-Out principle
4. **Audit Trail**: Complete tracking from allocation → return → processing
5. **Flexible Returns**: Admin controls how returns affect inventory (restock vs damage)

## Important Notes

- Admin stock is reduced **immediately** when allocating to staff
- Staff can only return products they haven't sold yet
- Returns must be processed by admin to add stock back
- All stock movements use FIFO method for consistency
- Same product allocations are automatically aggregated per staff member
