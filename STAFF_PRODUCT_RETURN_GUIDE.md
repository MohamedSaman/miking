# Staff Product Return Permission Guide

## Overview
This guide explains how staff can return allocated products and how admins can grant this permission.

---

## For Admin: How to Give Permission

### Step 1: Navigate to Settings
1. Log in as **Admin**
2. Go to **Settings** (in the sidebar or top menu)

### Step 2: Access Staff Permissions Tab
1. In the Settings page, look for the **"Staff Permissions"** tab
2. Click on it to view all staff members

### Step 3: Select Staff Member
1. Find the staff member you want to grant permission to
2. Click the **"Manage Permissions"** or **"Edit Permissions"** button next to their name

### Step 4: Enable Return Permission
1. In the permissions modal/page, look for the category: **"Staff Product Management"**
2. Find the permission: **"Return Allocated Products"**
3. **Check the checkbox** to enable this permission
4. Click **"Save"** or **"Update Permissions"**

### Visual Guide:
```
Settings → Staff Permissions Tab → Select Staff → Enable "Return Allocated Products" → Save
```

---

## For Staff: How to Return Products

### Prerequisites
- Admin must have granted you the **"Return Allocated Products"** permission
- You must have allocated products with available quantity (not sold)

### Step 1: Navigate to My Allocated Products
1. Log in as **Staff**
2. Go to **"My Allocated Products"** in the sidebar
   - Located after "Staff POS Sale"

### Step 2: View Your Products
- You'll see a table with all your allocated products
- Columns show:
  - **Allocated Qty**: Total quantity given to you
  - **Sold Qty**: How many you've sold
  - **Available Qty**: How many you can return

### Step 3: Select Products to Return
1. **Check the checkbox** next to each product you want to return
2. You can select multiple products at once
3. Only products with **Available Qty > 0** can be selected

### Step 4: Process Return
1. After selecting products, a blue alert bar will appear at the top
2. Click the **"Return Selected Products"** button
3. Confirm the action in the popup
4. Wait for the success message

### What Happens After Return:
- ✅ Products are removed from your allocated stock
- ✅ Products are added back to the main warehouse stock
- ✅ Product status changes to "returned"
- ✅ Admin can see these returned products in the Stock Re-entry page

---

## For Admin: Processing Returned Products

### Step 1: Navigate to Staff Allocation List
1. Go to **Staff Management → Allocated List**
2. Find the staff member who returned products

### Step 2: Access Stock Re-entry
1. Click the **Return button** (↩️ icon) next to the staff member's name
2. This opens the "Stock Re-entry" page

### Step 3: View Returned Products
- You'll see all products that the staff has returned
- Products are displayed as cards showing:
  - Product name and code
  - Allocated quantity
  - Sold quantity
  - **Returned quantity** (available to process)

### Step 4: Select Action for Each Product
For each selected product, choose one of two actions:

#### Option A: To Stock (Green Button)
- Re-enters the product to **available stock**
- Product can be sold again
- Best for products in good condition

#### Option B: Damaged (Red Button)
- Marks the product as **damaged stock**
- Product is not available for sale
- Best for broken or defective items

### Step 5: Process Selected Products
1. Select the products you want to process (checkbox)
2. For each selected product, choose the action (To Stock or Damaged)
3. Click **"Process Selected"** button
4. Confirm the action

### Result:
- Products marked "To Stock" → Added to `available_stock`
- Products marked "Damaged" → Added to `damage_stock`
- Staff product status updated to `restocked` or `damaged`

---

## Permission Denied Message

If a staff member tries to return products without permission, they will see:

```
⚠️ Note: You do not have permission to return products. 
Please contact your administrator if you need to return allocated products.
```

**Solution**: Admin needs to grant the "Return Allocated Products" permission in Settings.

---

## Database Changes

### Staff Product Status Values:
- `allocated` - Initially allocated to staff
- `sold` - Staff has sold the product
- `returned` - Staff has returned the product
- `restocked` - Admin re-entered to available stock
- `damaged` - Admin marked as damaged

### Stock Updates:
When admin processes returns:
- **To Stock**: `product_stock.available_stock` increases
- **Damaged**: `product_stock.damage_stock` increases
- Both update: `product_stock.total_stock` increases

---

## Troubleshooting

### Staff Can't See Return Option
**Problem**: Checkboxes don't appear, or "Return Selected Products" button is missing
**Solution**: Admin must grant "Return Allocated Products" permission in Settings

### No Products to Return
**Problem**: "No products found" message appears
**Solution**: Staff has either:
- No allocated products
- All allocated products are sold (Available Qty = 0)
- All products already returned

### Admin Can't See Returned Products
**Problem**: Stock Re-entry page shows "No returned products available"
**Solution**: Staff hasn't returned any products yet, or all returned products have been processed

---

## Quick Reference

### Admin Actions:
1. **Grant Permission**: Settings → Staff Permissions → Enable "Return Allocated Products"
2. **Process Returns**: Staff Management → Allocated List → Return Button → Select Action → Process

### Staff Actions:
1. **Return Products**: My Allocated Products → Select Products → Return Selected Products

### Permission Key:
- Permission Name: `staff_product_return`
- Category: Staff Product Management
- Description: Return Allocated Products

---

## Notes
- Staff can only return products they haven't sold
- Admin has final control over what happens to returned products
- All actions are logged for audit purposes
- Returns update stock in real-time
