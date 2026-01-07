# PHÂN TÍCH PERMISSIONS - SPATIE PERMISSION

## PERMISSIONS ĐÃ ĐƯỢC IMPLEMENT (3/24)

### Brand Group
- ✅ `all.brand` - **BrandController::AllBrand()** (dòng 87)
- ✅ `edit.brand` - **BrandController::EditBrand()** (dòng 166)
- ❌ `brand.menu` - Chưa có (menu permission cho sidebar)
- ❌ `delete.brand` - **BrandController::DeleteBrand()** (dòng 212) - Chưa có check

### Warehouse Group
- ✅ `all.warehouse` - **WarehouseController::AllWarehouse()** (dòng 14)
- ❌ `warehouse.menu` - Chưa có (menu permission cho sidebar)

---

## PERMISSIONS CHƯA ĐƯỢC IMPLEMENT (21/24)

### Supplier Group (2 permissions thiếu)
1. ❌ `supplier.menu` - **SupplierController** - Menu permission cho sidebar
2. ❌ `all.supplier` - **SupplierController::AllSupplier()** (dòng 13) - Chưa có check

### Customer Group (2 permissions thiếu)
3. ❌ `customer.menu` - **SupplierController** - Menu permission cho sidebar
4. ❌ `all.customer` - **SupplierController::AllCustomer()** (dòng 112) - Chưa có check

### Due Group (3 permissions thiếu)
5. ❌ `due.menu` - **SaleReturnController** - Menu permission cho sidebar
6. ❌ `due.sales` - **SaleReturnController::DueSale()** (dòng 237) - Chưa có check
7. ❌ `due.sales.return` - **SaleReturnController::DueSaleReturn()** (dòng 247) - Chưa có check

### Product Group (3 permissions thiếu)
8. ❌ `product.menu` - **ProductController, ProductCategoryController** - Menu permission cho sidebar
9. ❌ `all.category` - **ProductCategoryController::AllCategory()** (dòng 12) - Chưa có check
10. ❌ `all.product` - **ProductController::AllProduct()** (dòng 66) - Chưa có check

### Transfers Group (2 permissions thiếu)
11. ❌ `transfers.menu` - **TransferController** - Menu permission cho sidebar
12. ❌ `all.transfers` - **TransferController::AllTransfer()** (dòng 23) - Chưa có check

### Purchase Group (3 permissions thiếu)
13. ❌ `purchase.menu` - **PurchaseController, ReturnPurchaseController** - Menu permission cho sidebar
14. ❌ `all.purchase` - **PurchaseController::AllPurchase()** (dòng 18) - Chưa có check
15. ❌ `return.purchase` - **ReturnPurchaseController::AllReturnPurchase()** (dòng 20) - Chưa có check

### Sale Group (3 permissions thiếu)
16. ❌ `sale.menu` - **SaleController, SaleReturnController** - Menu permission cho sidebar
17. ❌ `all.sale` - **SaleController::AllSales()** (dòng 21) - Chưa có check
18. ❌ `return.sale` - **SaleReturnController::AllSalesReturn()** (dòng 18) - Chưa có check

### Brand Group (2 permissions thiếu - đã liệt kê ở trên)
19. ❌ `brand.menu` - Menu permission cho sidebar
20. ❌ `delete.brand` - **BrandController::DeleteBrand()** (dòng 212) - Chưa có check

### Warehouse Group (1 permission thiếu - đã liệt kê ở trên)
21. ❌ `warehouse.menu` - Menu permission cho sidebar

---

## TÓM TẮT

### Đã implement: 3 permissions
- `all.brand`
- `edit.brand`
- `all.warehouse`

### Chưa implement: 21 permissions
1. `brand.menu`
2. `delete.brand`
3. `warehouse.menu`
4. `supplier.menu`
5. `all.supplier`
6. `customer.menu`
7. `all.customer`
8. `due.menu`
9. `due.sales`
10. `due.sales.return`
11. `product.menu`
12. `all.category`
13. `all.product`
14. `transfers.menu`
15. `all.transfers`
16. `purchase.menu`
17. `all.purchase`
18. `return.purchase`
19. `sale.menu`
20. `all.sale`
21. `return.sale`

---

## CÁC CONTROLLER CẦN THÊM PERMISSION CHECK

### SupplierController.php
- `AllSupplier()` - Cần thêm: `hasPermissionTo('all.supplier')`
- `AllCustomer()` - Cần thêm: `hasPermissionTo('all.customer')`

### ProductCategoryController.php
- `AllCategory()` - Cần thêm: `hasPermissionTo('all.category')`

### ProductController.php
- `AllProduct()` - Cần thêm: `hasPermissionTo('all.product')`

### TransferController.php
- `AllTransfer()` - Cần thêm: `hasPermissionTo('all.transfers')`

### PurchaseController.php
- `AllPurchase()` - Cần thêm: `hasPermissionTo('all.purchase')`

### ReturnPurchaseController.php
- `AllReturnPurchase()` - Cần thêm: `hasPermissionTo('return.purchase')`

### SaleController.php
- `AllSales()` - Cần thêm: `hasPermissionTo('all.sale')`

### SaleReturnController.php
- `AllSalesReturn()` - Cần thêm: `hasPermissionTo('return.sale')`
- `DueSale()` - Cần thêm: `hasPermissionTo('due.sales')`
- `DueSaleReturn()` - Cần thêm: `hasPermissionTo('due.sales.return')`

### BrandController.php
- `DeleteBrand()` - Cần thêm: `hasPermissionTo('delete.brand')`

---

## LƯU Ý

1. **Menu Permissions** (`*.menu`): Thường được sử dụng trong sidebar để ẩn/hiện menu items, không nhất thiết phải check trong controller.

2. **Pattern hiện tại**: 
   - BrandController và WarehouseController chỉ check permission ở method `All*()` và `Edit*()`
   - Các method khác như `Add*()`, `Store*()`, `Update*()`, `Delete*()` chưa có check

3. **Recommendation**: 
   - Nên thêm permission check cho tất cả các method quan trọng (All, Add, Edit, Delete)
   - Menu permissions có thể được check trong view/sidebar thay vì controller

