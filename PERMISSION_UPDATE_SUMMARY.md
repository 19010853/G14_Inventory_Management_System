# Permission System Update - Summary

## Overview
This document details all changes made to implement the new permission system where:
- `.menu` permissions allow read-only access to list pages
- `all.*` permissions grant full access and automatically include `.menu` permissions

## Changes Made

### 1. PermissionSeeder (`database/seeders/PermissionSeeder.php`)
**Updated permissions structure:**
- Added `category.menu` for Category module
- Added `return.purchase.menu` and `all.return.purchase` for Return Purchase
- Added `return.sale.menu` and `all.return.sale` for Return Sale
- Added `due.return.sale.menu` for Due Sales Return
- Changed `transfers.menu` to `transfer.menu` and `all.transfers` to `all.transfer`
- Added `report.menu` for Report module

### 2. Controllers Updated
All list page methods now check for both `all.*` and `.menu` permissions:

**Files Modified:**
- `app/Http/Controllers/Backend/BrandController.php` - `AllBrand()`
- `app/Http/Controllers/Backend/WarehouseController.php` - `AllWarehouse()`
- `app/Http/Controllers/Backend/SupplierController.php` - `AllSupplier()`, `AllCustomer()`
- `app/Http/Controllers/Backend/ProductCategoryController.php` - `AllCategory()`
- `app/Http/Controllers/Backend/ProductController.php` - `AllProduct()`
- `app/Http/Controllers/Backend/PurchaseController.php` - `AllPurchase()`
- `app/Http/Controllers/Backend/ReturnPurchaseController.php` - `AllReturnPurchase()`
- `app/Http/Controllers/Backend/SaleController.php` - `AllSales()`
- `app/Http/Controllers/Backend/SaleReturnController.php` - `AllSalesReturn()`, `DueSale()`, `DueSaleReturn()`
- `app/Http/Controllers/Backend/TransferController.php` - `AllTransfer()`
- `app/Http/Controllers/Backend/ReportController.php` - `AllReport()`

**Change Pattern:**
```php
// Before
if (!auth()->user()->hasPermissionTo('all.brand')) {
    abort(403, 'Unauthorized Action');
}

// After
$user = auth()->user();
if (!$user->hasPermissionTo('all.brand') && !$user->hasPermissionTo('brand.menu')) {
    abort(403, 'Unauthorized Action');
}
```

### 3. Sidebar (`resources/views/admin/body/sidebar.blade.php`)
**Updated permission checks:**
- Product section: Added checks for `category.menu` and `product.menu`
- Purchase section: Added checks for `return.purchase.menu`
- Sale section: Added checks for `return.sale.menu`
- Due section: Added checks for `due.return.sale.menu`
- Transfer section: Changed from `transfers.menu` to `transfer.menu` and `all.transfers` to `all.transfer`
- Report section: Added check for `report.menu`

### 4. RoleController (`app/Http/Controllers/Backend/RoleController.php`)
**Added automatic `.menu` permission assignment:**
- New method: `addMenuPermissions()` that automatically adds `.menu` permission when `all.*` is assigned
- Updated `StoreRolePermission()` and `AdminRolesUpdate()` to use this method
- Mapping includes all module permissions

**Permission Mapping:**
```php
'all.brand' => 'brand.menu',
'all.warehouse' => 'warehouse.menu',
'all.supplier' => 'supplier.menu',
'all.customer' => 'customer.menu',
'all.category' => 'category.menu',
'all.product' => 'product.menu',
'all.purchase' => 'purchase.menu',
'all.return.purchase' => 'return.purchase.menu',
'all.sale' => 'sale.menu',
'all.return.sale' => 'return.sale.menu',
'due.sales' => 'due.menu',
'due.sales.return' => 'due.return.sale.menu',
'all.transfer' => 'transfer.menu',
'reports.all' => 'report.menu',
```

### 5. Migration (`database/migrations/2026_01_09_171216_add_new_permissions_to_permissions_table.php`)
**Creates new permissions and migrates existing roles:**
- Adds all new permissions to database
- Updates existing roles to include `.menu` permissions when they have `all.*` permissions
- Migrates old permission names to new ones:
  - `all.transfers` → `all.transfer`
  - `transfers.menu` → `transfer.menu`
  - `return.purchase` → `return.purchase.menu` (if only menu access)
  - `return.sale` → `return.sale.menu` (if only menu access)

### 6. GrokChatController (`app/Http/Controllers/Backend/GrokChatController.php`)
**Updated permission checks for chatbot:**
- Added checks for new permissions (category, return purchase, return sale, due return, transfer, report)
- Updated permission mapping to include all new permission names

## Permission Structure

### Brand
- `brand.menu` - View Brand list page only
- `all.brand` - Full Brand access (includes `brand.menu`)

### Warehouse
- `warehouse.menu` - View Warehouse list page only
- `all.warehouse` - Full Warehouse access (includes `warehouse.menu`)

### Supplier
- `supplier.menu` - View Supplier list page only
- `all.supplier` - Full Supplier access (includes `supplier.menu`)

### Customer
- `customer.menu` - View Customer list page only
- `all.customer` - Full Customer access (includes `customer.menu`)

### Category
- `category.menu` - View Category list page only
- `all.category` - Full Category access (includes `category.menu`)

### Product
- `product.menu` - View Product list page only
- `all.product` - Full Product access (includes `product.menu`)

### Purchase
- `purchase.menu` - View Purchase list page only
- `all.purchase` - Full Purchase access (includes `purchase.menu`)

### Return Purchase
- `return.purchase.menu` - View Return Purchase list page only
- `all.return.purchase` - Full Return Purchase access (includes `return.purchase.menu`)

### Sale
- `sale.menu` - View Sale list page only
- `all.sale` - Full Sale access (includes `sale.menu`)

### Return Sale
- `return.sale.menu` - View Return Sale list page only
- `all.return.sale` - Full Return Sale access (includes `return.sale.menu`)

### Due Sales
- `due.menu` - View Due Sales page only
- `due.sales` - Full Due Sales access (includes `due.menu`)

### Due Sales Return
- `due.return.sale.menu` - View Due Sales Return page only
- `due.sales.return` - Full Due Sales Return access (includes `due.return.sale.menu`)

### Transfer
- `transfer.menu` - View Transfer list page only
- `all.transfer` - Full Transfer access (includes `transfer.menu`)

### Report
- `report.menu` - View Report page only
- `reports.all` - Full Report access (includes `report.menu`)

### System Permission
- `role_and_permission.all` - Full access to Role & Permission management

## Testing Checklist

- [ ] Verify `.menu` permissions allow access to list pages only
- [ ] Verify `all.*` permissions grant full access and automatically include `.menu`
- [ ] Test role assignment: assigning `all.brand` should automatically add `brand.menu`
- [ ] Test sidebar visibility based on permissions
- [ ] Test controller authorization for all list pages
- [ ] Verify migration runs successfully and updates existing roles
- [ ] Test chatbot permission checks

## Notes

- Super Admin role automatically has all permissions (handled by `AuthServiceProvider`)
- Existing roles will be automatically updated by the migration
- Old permission names (`all.transfers`, `transfers.menu`, `return.purchase`, `return.sale`) are migrated to new names
