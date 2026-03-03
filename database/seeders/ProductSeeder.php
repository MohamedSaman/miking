<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\BrandList;
use App\Models\CategoryList;
use App\Models\ProductSupplier;
use App\Models\ProductDetail;
use App\Models\ProductPrice;
use App\Models\ProductStock;
use App\Models\StaffSale;
use App\Models\StaffProduct;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Brand (required by product_details.brand_id FK)
        $brand = BrandList::firstOrCreate(
            ['brand_name' => 'Denim'],
            ['brand_name' => 'Denim']
        );

        // 2. Create Category (required by product_details.category_id FK)
        $category = CategoryList::firstOrCreate(
            ['category_name' => 'Kids Wear'],
            ['category_name' => 'Kids Wear']
        );

        // 3. Create Supplier (optional, but useful)
        $supplier = ProductSupplier::firstOrCreate(
            ['name' => 'Demo Supplier'],
            [
                'name' => 'Demo Supplier',
                'businessname' => 'Demo Trading Co.',
                'contact' => '0771234567',
                'phone' => '0771234567',
                'status' => 'active',
            ]
        );

        // 4. Create Product with required foreign keys
        $product = ProductDetail::firstOrCreate(
            ['code' => 'MT-1008'],
            [
                'code' => 'MT-1008',
                'name' => 'Kids YS Badge Denim Short (S,M,L)',
                'model' => 'MT-1008',
                'description' => 'Dummy product for testing staff sale flow',
                'barcode' => '1234567890123',
                'status' => 'active',
                'unit' => 'Piece',
                'brand_id' => $brand->id,
                'category_id' => $category->id,
                'supplier_id' => $supplier->id,
            ]
        );

        // 5. Create Price (linked via product_id)
        $price = ProductPrice::firstOrCreate(
            ['product_id' => $product->id],
            [
                'product_id' => $product->id,
                'supplier_price' => 600,
                'selling_price' => 950,
                'cash_price' => 950,
                'credit_price' => 1000,
                'cash_credit_price' => 950,
            ]
        );

        // 6. Create Stock (linked via product_id)
        ProductStock::firstOrCreate(
            ['product_id' => $product->id],
            [
                'product_id' => $product->id,
                'available_stock' => 72,
                'total_stock' => 72,
                'damage_stock' => 0,
                'sold_count' => 0,
                'restocked_quantity' => 0,
                'assigned_stock' => 0,
            ]
        );

        // =============================================
        // 7. CREATE PURCHASE ORDER HISTORY FOR ALL PRODUCTS
        // =============================================

        // Get all existing products to add purchase history
        $allProducts = ProductDetail::with('price')->get();

        foreach ($allProducts as $prod) {
            // Skip if already has purchase orders
            $existingPO = PurchaseOrderItem::where('product_id', $prod->id)->exists();
            if ($existingPO) continue;

            $supplierPrice = $prod->price ? $prod->price->supplier_price : 500;
            $quantity = 100; // Initial purchase quantity

            // Create Purchase Order
            $po = PurchaseOrder::create([
                'order_code' => 'PO-' . str_pad($prod->id, 5, '0', STR_PAD_LEFT),
                'supplier_id' => $supplier->id,
                'order_date' => now()->subDays(rand(30, 90)),
                'received_date' => now()->subDays(rand(10, 29)),
                'status' => 'received',
                'total_amount' => $supplierPrice * $quantity,
                'due_amount' => 0,
            ]);

            // Create Purchase Order Item
            PurchaseOrderItem::create([
                'order_id' => $po->id,
                'product_id' => $prod->id,
                'quantity' => $quantity,
                'received_quantity' => $quantity,
                'unit_price' => $supplierPrice,
                'discount' => 0,
                'discount_type' => 'fixed',
                'status' => 'received',
            ]);

            // Create a second purchase order for some products (restock)
            if ($prod->id % 2 == 0) {
                $po2 = PurchaseOrder::create([
                    'order_code' => 'PO-' . str_pad($prod->id, 5, '0', STR_PAD_LEFT) . '-R',
                    'supplier_id' => $supplier->id,
                    'order_date' => now()->subDays(rand(5, 15)),
                    'received_date' => now()->subDays(rand(1, 4)),
                    'status' => 'received',
                    'total_amount' => ($supplierPrice * 50) - ($supplierPrice * 2),
                    'due_amount' => 0,
                ]);

                PurchaseOrderItem::create([
                    'order_id' => $po2->id,
                    'product_id' => $prod->id,
                    'quantity' => 50,
                    'received_quantity' => 50,
                    'unit_price' => $supplierPrice,
                    'discount' => $supplierPrice * 2,
                    'discount_type' => 'fixed',
                    'status' => 'received',
                ]);
            }
        }

        // 8. Create Walking Customer (required for default customer in staff sales)
        $walkingCustomer = Customer::firstOrCreate(
            ['name' => 'Walking Customer', 'user_id' => null],
            [
                'name' => 'Walking Customer',
                'type' => 'retail',
                'address' => 'Walk-in Customer',
                'user_id' => null,
            ]
        );

        // 9. Get Staff and Admin users
        $staffUser = User::where('role', 'staff')->first();
        $adminUser = User::where('role', 'admin')->first();

        if ($staffUser && $adminUser) {
            // 10. Create StaffSale allocation record
            $staffSale = StaffSale::firstOrCreate(
                ['staff_id' => $staffUser->id],
                [
                    'staff_id' => $staffUser->id,
                    'admin_id' => $adminUser->id,
                    'total_quantity' => 72,
                    'total_value' => 72 * 950,
                    'sold_quantity' => 0,
                    'sold_value' => 0,
                    'status' => 'assigned',
                ]
            );

            // 11. Create StaffProduct allocation (allocate product to staff for selling)
            StaffProduct::firstOrCreate(
                ['staff_id' => $staffUser->id, 'product_id' => $product->id],
                [
                    'product_id' => $product->id,
                    'staff_id' => $staffUser->id,
                    'quantity' => 72,
                    'unit_price' => $price->selling_price,
                    'discount_per_unit' => 0,
                    'total_discount' => 0,
                    'total_value' => 72 * $price->selling_price,
                    'sold_quantity' => 0,
                    'sold_value' => 0,
                    'status' => 'assigned',
                ]
            );

            // Update ProductStock to reflect assignment
            $stock = ProductStock::where('product_id', $product->id)->first();
            if ($stock) {
                $stock->update([
                    'assigned_stock' => 72,
                    'available_stock' => 0, // All stock assigned to staff
                ]);
            }
        }
    }
}
