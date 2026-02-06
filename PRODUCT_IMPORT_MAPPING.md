# Product Import Excel Field Mapping

## Overview
This document describes how Excel columns are mapped to database fields when importing products.

## Excel Column → Database Field Mapping

| Excel Column Name | Database Table | Database Column | Data Type | Default Value | Notes |
|-------------------|----------------|-----------------|-----------|---------------|-------|
| Product Code * | product_details | code | string | - | Required, must be unique |
| Product Name * | product_details | name | string | - | Required field |
| Category | product_details | category_id | relation | Default Category | Auto-creates category if doesn't exist |
| Unit | product_details | unit | enum | Piece | Values: Piece, Dozen, Bundle |
| Description | product_details | description | text | null | Optional |
| Selling Price | product_prices | selling_price | decimal | 0.00 | Base selling price |
| Cash Price | product_prices | cash_price | decimal | null | Price for cash transactions |
| Credit Price | product_prices | credit_price | decimal | null | Price for credit transactions |
| Cash & Credit Price | product_prices | cash_credit_price | decimal | null | Price for partial payment buyers |
| Supplier Price | product_prices | supplier_price | decimal | 0.00 | Purchase cost from supplier |
| Cash Sale Commission | product_details | cash_sale_commission | decimal | 0.00 | Staff commission for cash sales |
| Credit Sale Commission | product_details | credit_sale_commission | decimal | 0.00 | Staff commission for credit sales |
| Opening Stock | product_stocks | available_stock | integer | 0 | Initial stock quantity |
| Opening Stock Rate | product_stocks | opening_stock_rate | decimal | 0.00 | Initial stock cost |
| Minimum Stock | product_stocks | restocked_quantity | integer | 0 | Restock threshold |
| Is Service (Yes / No) | product_details | status | enum | active | 'Yes'/'service' → inactive, others → active |

## Default Values

### Product Details (product_details)
- **model**: null
- **image**: null
- **barcode**: null
- **brand_id**: Default Brand (auto-created)
- **category_id**: Default Category (auto-created)
- **supplier_id**: Default Supplier (auto-created)

### Product Prices (product_prices)
- **cash_price**: null (optional - for cash transactions)
- **credit_price**: null (optional - for credit transactions)
- **cash_credit_price**: null (optional - for partial payment buyers)

### Product Stocks (product_stocks)
- **damage_stock**: 0
- **total_stock**: Calculated automatically
- **sold_count**: 0

## Excel Template Structure

The Excel file should have the following columns in the first row (header row):

```
| Product Code * | Product Name * | Category | Unit | Description | Selling Price | Cash Price | Credit Price | Cash & Credit Price | Supplier Price | Cash Sale Commission | Credit Sale Commission | Opening Stock | Opening Stock Rate | Minimum Stock | Is Service (Yes / No) |
```

**Note:** Columns marked with * are required.

## Sample Data

```
Product Code: USN0001
Product Name: Flasher Musical 12 V
Category: Electronics
Unit: Piece
Description: High quality musical flasher
Selling Price: 150.00
Cash Price: 145.00
Credit Price: 155.00
Cash & Credit Price: 148.00
Supplier Price: 120.00
Cash Sale Commission: 5.00
Credit Sale Commission: 3.00
Opening Stock: 100
Opening Stock Rate: 115.00
Minimum Stock: 20
Is Service: No
```

## Import Behavior

1. **Duplicate Detection**: Products with existing Product Code are skipped
2. **Validation**: Required fields are validated before import
3. **Transaction Safety**: Each product import is wrapped in a database transaction
4. **Error Handling**: Failed imports are skipped and counted separately
5. **Success Reporting**: Import summary shows successful and skipped products

## Unit Field Options

The Unit field accepts the following values (case-insensitive):
- **Bundle** or **bundle**
- **Dozen** or **dozen**
- **Piece** or **piece** (default if not specified)

## Is Service Field

The "Is Service" field determines the product status:
- **"Yes"** or **"service"** → Product status set to "inactive"
- **"No"** or any other value → Product status set to "active"

## Download Template

Users can download a pre-formatted Excel template with sample data by clicking the "Download Template" button in the import modal.
