# Blade Files Updated - Cash/Credit System

## Summary
All blade view files have been updated to reflect the new cash/credit pricing system, removing wholesale/retail terminology and changing from "bonus" to "commission" for staff earnings.

## Files Updated

### 1. resources/views/livewire/admin/Productes.blade.php
**Changes Made:**
- **Product View Modal (Lines ~880-950):**
  - Changed "Retail Price" → "Cash Price" (using `$viewProduct->price->cash_price`)
  - Changed "Wholesale Price" → "Credit Price" (using `$viewProduct->price->credit_price`)
  - Changed "Sales Bonus" → "Staff Commission"
  - Removed separate wholesale/retail bonus sections
  - Now shows only: "Cash Sale Commission" and "Credit Sale Commission"

- **Product View Modal Duplicate Section (Lines ~1140-1210):**
  - Same changes as above for consistency across the view

- **Create Product Form (Lines ~1460-1540):**
  - Removed "Wholesale Bonuses" section entirely
  - Removed "Retail Bonuses" section
  - Added unified "Staff Commission" section with:
    - Cash Sale Commission (wire:model="cash_commission")
    - Credit Sale Commission (wire:model="credit_commission")
  - Removed percentage inputs (now fixed amount only)

- **Create Product Price Fields (Lines ~1590-1630):**
  - Changed "Retail Price" → "Cash Price" (wire:model="cash_price")
  - Changed "Wholesale Price" → "Credit Price" (wire:model="credit_price")

- **Edit Product Form (Lines ~1870-1950):**
  - Same commission changes as create form
  - Changed wire:model bindings:
    - editCashCommission (instead of editRetailCashBonus, etc.)
    - editCreditCommission

- **Edit Product Price Fields (Lines ~1995-2020):**
  - Changed "Retail Price" → "Cash Price" (wire:model="editCashPrice")
  - Changed "Wholesale Price" → "Credit Price" (wire:model="editCreditPrice")

### 2. resources/views/livewire/admin/settings.blade.php
**Changes Made:**
- **Bulk Update Section (Lines ~30-100):**
  - Removed "Wholesale Bonuses" section
  - Removed "Retail Bonuses" section
  - Added unified "Sales Commission" section:
    - Cash Sale Commission (wire:model="bulkCashCommissionType" / "bulkCashCommissionValue")
    - Credit Sale Commission (wire:model="bulkCreditCommissionType" / "bulkCreditCommissionValue")
  - Changed button text: "Apply Bonuses" → "Apply Commissions"
  - Changed wire:target from "applyBulkBonus" → "applyBulkCommission"

- **Product List Table (Lines ~115-160):**
  - Removed 4 separate columns for wholesale/retail bonuses
  - Added 2 columns: "Cash Commission" and "Credit Commission"
  - Updated data bindings:
    - `$product->cash_sale_commission` (instead of retail_cash_bonus, etc.)
    - `$product->credit_sale_commission`

- **Edit Product Modal (Lines ~750-800):**
  - Removed "Wholesale Bonuses" section
  - Removed "Retail Bonuses" section
  - Added unified "Sales Commission" section:
    - Cash Sale Commission (wire:model="editCommissionCashType" / "editCommissionCash")
    - Credit Sale Commission (wire:model="editCommissionCreditType" / "editCommissionCredit")

### 3. resources/views/livewire/admin/purchase-order-list.blade.php
**Changes Made:**
- **Product Badge Display (Line ~697-700):**
  - Removed 4 separate badges (RC, RCR, WC, WCR)
  - Added 2 badges:
    - "Cash: X" showing `$item->product->cash_sale_commission`
    - "Credit: X" showing `$item->product->credit_sale_commission`
  - Updated tooltips from "Retail Cash Bonus" → "Cash Sale Commission"

### 4. resources/views/livewire/staff/billing.blade.php
**Changes Made (Already Done):**
- Removed customer type sale dropdown
- Removed sale type selection UI

### 5. resources/views/livewire/admin/staff-billing.blade.php
**Changes Made (Already Done):**
- Removed customer type sale dropdown
- Removed sale type selection UI

### 6. resources/views/livewire/admin/sales-system.blade.php
**Changes Made (Already Done):**
- Removed customer type sale dropdown
- Removed sale type selection UI

## Key Terminology Changes

### Price Fields
| Old Term | New Term | Database Column |
|----------|----------|-----------------|
| Retail Price | Cash Price | cash_price |
| Wholesale Price | Credit Price | credit_price |

### Commission Fields
| Old Terms | New Term | Database Column |
|-----------|----------|-----------------|
| Retail Cash Bonus | Cash Sale Commission | cash_sale_commission |
| Retail Credit Bonus | Credit Sale Commission | credit_sale_commission |
| Wholesale Cash Bonus | *(removed)* | *(removed)* |
| Wholesale Credit Bonus | *(removed)* | *(removed)* |

### Wire:model Bindings (Livewire)

#### Create Product Form
- `retail_cash_bonus` → `cash_commission`
- `retail_credit_bonus` → `credit_commission`
- `retail_price` → `cash_price`
- `wholesale_price` → `credit_price`

#### Edit Product Form
- `editRetailCashBonus` → `editCashCommission`
- `editRetailCreditBonus` → `editCreditCommission`
- `editRetailPrice` → `editCashPrice`
- `editWholesalePrice` → `editCreditPrice`

#### Settings Bulk Update
- `bulkWholesaleCashBonusType/Value` → `bulkCashCommissionType/Value`
- `bulkWholesaleCreditBonusType/Value` → `bulkCreditCommissionType/Value`
- `bulkRetailCashBonusType/Value` → *(removed)*
- `bulkRetailCreditBonusType/Value` → *(removed)*

#### Settings Edit Modal
- `editBonusWholesaleCashType` → `editCommissionCashType`
- `editBonusWholesaleCash` → `editCommissionCash`
- `editBonusWholesaleCreditType` → `editCommissionCreditType`
- `editBonusWholesaleCredit` → `editCommissionCredit`
- `editBonusRetailCashType` → *(removed)*
- `editBonusRetailCash` → *(removed)*
- `editBonusRetailCreditType` → *(removed)*
- `editBonusRetailCredit` → *(removed)*

## UI/UX Improvements

### Simplified Commission Structure
- Reduced from 4 commission types to 2
- Clearer labels: "Cash Sale" and "Credit Sale"
- More intuitive for users (matches payment method)

### Removed Complexity
- No more distinction between wholesale/retail sales
- Single commission rate per payment method
- Eliminated confusing percentage inputs in forms

### Consistent Terminology
- All "bonus" references changed to "commission"
- Unified language across all admin interfaces
- Professional business terminology

## Testing Checklist
- [ ] Product creation form shows Cash/Credit commission fields
- [ ] Product edit form loads and saves Cash/Credit commissions correctly
- [ ] Product view modal displays Cash/Credit prices and commissions
- [ ] Settings bulk update applies Cash/Credit commissions
- [ ] Settings edit modal updates individual product commissions
- [ ] Purchase order list shows correct commission badges
- [ ] Billing interfaces work without customer type selection
- [ ] Commission calculations use correct fields (cash_sale_commission, credit_sale_commission)

## Notes
- All blade files now align with the updated database schema
- Livewire component property names must match the wire:model bindings
- Commission labels changed from "bonus" to be more professional
- Simplified user experience by removing unnecessary wholesale/retail distinction
