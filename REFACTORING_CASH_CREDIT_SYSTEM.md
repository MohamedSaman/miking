# System Refactoring: Wholesale/Retail → Cash/Credit

## Overview
This document outlines the complete refactoring of the system from wholesale/retail terminology to cash/credit pricing and commission structure.

## ✅ Database Changes Completed

### Current Database State:
- **product_prices**: `cash_price`, `credit_price` (Already correctly named!)
- **product_details**: `cash_sale_commission`, `credit_sale_commission` (Migrated from cash_bonus/credit_bonus)
- **sales**: `customer_type_sale` column removed (All sales are wholesale)

### Migrations Applied:
1. ✅ `2026_02_05_000001_rename_bonuses_to_commissions.php` - Renamed bonus columns to commission
2. ✅ `2026_02_05_000002_remove_customer_type_sale_from_sales.php` - Removed customer_type_sale

## ✅ Model Updates Completed

- **ProductPrice**: Updated fillable to use `cash_price`, `credit_price`
- **ProductDetail**: Updated fillable to use `cash_sale_commission`, `credit_sale_commission`
- **Sale**: Removed `customer_type_sale` from fillable

## ✅ Service Layer Completed

- **StaffBonusService**: 
  - Simplified (no more wholesale/retail distinction)
  - Uses "commission" terminology  
  - Only considers payment_method (cash/credit)
  - Sets all sales to 'wholesale' type in bonus records

## 🔄 Remaining Code Updates Needed

### Property/Variable Name Changes Required:

**In Livewire Components:**
- `$retail_price` → `$cash_price`
- `$wholesale_price` → `$credit_price`
- `$editRetailPrice` → `$editCashPrice`
- `$editWholesalePrice` → `$editCreditPrice`
- `$retail_cash_bonus` → `$cash_commission` (remove retail_ prefix)
- `$retail_credit_bonus` → `$credit_commission` (remove retail_ prefix)
- `$wholesale_cash_bonus` → REMOVE (not needed)
- `$wholesale_credit_bonus` → REMOVE (not needed)
- `$editRetailCashBonus` → `$editCashCommission`
- `$editRetailCreditBonus` → `$editCreditCommission`
- `$editWholesaleCashBonus` → REMOVE
- `$editWholesaleCreditBonus` → REMOVE
- `$customerTypeSale` → REMOVE (not needed)
- `customer_type_sale` → REMOVE (not needed)

**In Views (Blade files):**
- `retail_price` → `cash_price`
- `wholesale_price` → `credit_price`  
- `Retail Price` → `Cash Price` (labels)
- `Wholesale Price` → `Credit Price` (labels)
- `Retail Cash Bonus` → `Cash Commission`
- `Retail Credit Bonus` → `Credit Commission`
- `Wholesale Cash Bonus` → REMOVE sections
- `Wholesale Credit Bonus` → REMOVE sections
- `wholesale_cash_bonus` → REMOVE
- `wholesale_credit_bonus` → REMOVE
- `customerTypeSale` → REMOVE

### Files Requiring Updates:

#### Critical Livewire Components:
1. ✅ `app/Livewire/Admin/Products.php` - NEEDS UPDATE
2. ✅ `app/Livewire/Admin/Settings.php` - NEEDS UPDATE
3. ✅ `app/Livewire/Admin/StaffBilling.php` - NEEDS UPDATE  
4. ✅ `app/Livewire/Admin/SalesSystem.php` - NEEDS UPDATE
5. ✅ `app/Livewire/Admin/PurchaseOrderList.php` - NEEDS UPDATE
6. ✅ `app/Livewire/Staff/Billing.php` - NEEDS UPDATE
7. ✅ `app/Livewire/Staff/StaffQuotationSystem.php` - NEEDS UPDATE
8. ✅ `app/Livewire/Staff/StaffQuotationList.php` - NEEDS UPDATE

#### View Files:
1. `resources/views/livewire/admin/Productes.blade.php` - NEEDS UPDATE
2. `resources/views/livewire/admin/settings.blade.php` - NEEDS UPDATE
3. Billing and quotation views - NEEDS UPDATE

#### Documentation:
1. `STAFF_BONUS_SYSTEM.md` - NEEDS UPDATE
2. `PRODUCT_IMPORT_MAPPING.md` - NEEDS UPDATE

## Important Notes

**System Simplification:**
- The system now only deals with wholesale sales (all sales are wholesale)
- No need for wholesale/retail sale type selection
- Only payment method matters: Cash or Credit
- Product pricing simplified to: Cash Price and Credit Price
- Staff commissions simplified to: Cash Commission and Credit Commission

**No More Wholesale Bonus Fields:**
Since all sales are wholesale, we don't need separate wholesale bonus fields. The system now only has:
- `cash_sale_commission` - Commission for cash payment sales
- `credit_sale_commission` - Commission for credit payment sales

## Next Manual Steps

1. Update all Livewire component property declarations
2. Update all Livewire validation rules
3. Update all database query references
4. Update all Blade view references
5. Update documentation
6. Test thoroughly before deployment

## Search and Replace Patterns

Use these patterns carefully (test in a dev environment first):

### In PHP files:
- `'retail_price'` → `'cash_price'`
- `'wholesale_price'` → `'credit_price'`
- `retail_price` → `cash_price` (in object property access)
- `wholesale_price` → `credit_price` (in object property access)
- `$retail_price` → `$cash_price`
- `$wholesale_price` → `$credit_price`
- `retail_cash_bonus` → `cash_sale_commission`
- `retail_credit_bonus` → `credit_sale_commission`
- Remove all references to `wholesale_cash_bonus`, `wholesale_credit_bonus`
- Remove all references to `customer_type_sale`, `customerTypeSale`

### In Blade files:
- `Retail Price` → `Cash Price`
- `Wholesale Price` → `Credit Price`
- `Retail Cash Bonus` → `Cash Commission`
- `Retail Credit Bonus` → `Credit Commission`
- Remove all `Wholesale Bonus` sections
- Remove all `customer type sale` selection dropdowns
