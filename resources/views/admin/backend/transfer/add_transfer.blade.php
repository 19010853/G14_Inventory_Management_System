@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-4">
         <div class="d-md-flex align-items-center justify-content-between">
            <h3 class="mb-0">Transfer Warehouse </h3>
            <div class="text-end my-2 mt-md-0"><a class="btn btn-outline-primary" href="{{ route('all.transfer') }}">Back</a></div>
         </div>


 <div class="card">
    <div class="card-body">
    <form action="{{ route('store.transfer')}}" method="post" enctype="multipart/form-data">
       @csrf


<div class="row">
 <div class="col-xl-12">
    <div class="card">
       <div class="row">
          <div class="col-md-4 mb-3">
             <label class="form-label">Date:  <span class="text-danger">*</span></label>
             <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="form-control @error('date') is-invalid @enderror">
             @error('date')
             <span class="text-danger">{{ $message }}</span>
             @enderror
          </div>

          <div class="col-md-4 mb-3">
                <div class="form-group w-100">
                <label class="form-label" for="formBasic">From Warehouse : <span class="text-danger">*</span></label>
                <select name="from_warehouse_id" id="from_warehouse_id" class="form-control form-select @error('from_warehouse_id') is-invalid @enderror">
                      <option value="">Select Warehouse</option>
                      @foreach ($warehouses as $item)
                      <option value="{{ $item->id }}" {{ old('from_warehouse_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                      @endforeach
                </select>
                @error('from_warehouse_id')
                <span class="text-danger">{{ $message }}</span>
                @enderror
                <small id="warehouse_error" class="text-danger d-none">Please select the first warehouse.</small>
                </div>
          </div>

          <div class="col-md-4 mb-3">
            <div class="form-group w-100">
            <label class="form-label" for="formBasic">To Warehouse : <span class="text-danger">*</span></label>
            <select name="to_warehouse_id" id="to_warehouse_id" class="form-control form-select @error('to_warehouse_id') is-invalid @enderror">
                  <option value="">Select Warehouse</option>
                  @foreach ($warehouses as $item)
                  <option value="{{ $item->id }}" {{ old('to_warehouse_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                  @endforeach
            </select>
            @error('to_warehouse_id')
            <span class="text-danger">{{ $message }}</span>
            @enderror
            <small id="warehouse_error" class="text-danger d-none">Please select the first warehouse.</small>
            </div>
      </div>

       </div>


       <div class="row">
          <div class="col-md-12 mb-3">
             <label class="form-label">Product:</label>
             <div class="input-group">
                   <span class="input-group-text">
                      <i class="fas fa-search"></i>
                   </span>
                   <input type="search" id="product_search" name="search" class="form-control" placeholder="Search product by code or name">
             </div>
             <div id="product_list" class="list-group mt-2"></div>
             <div id="product_error" class="text-danger mt-2" style="display: none;"></div>
          </div>
       </div>




  <div class="row">
     <div class="col-md-12">
        <label class="form-label">Order items: <span class="text-danger">*</span></label>
        <table class="table table-striped table-bordered dataTable" style="width: 100%;">
           <thead>
              <tr role="row">
                 <th>Product</th>
                 <th>Net Unit Cost</th>
                 <th>Stock</th>
                 <th>Qty</th>
                 <th>Discount</th>
                 <th>Subtotal</th>
                 <th>Action</th>
              </tr>
           </thead>
           <tbody>

           </tbody>
        </table>
     </div>
  </div>

<div class="row">
 <div class="col-md-6 ms-auto">
    <div class="card">
       <div class="card-body pt-7 pb-2">
          <div class="table-responsive">
             <table class="table border">
                <tbody>
                   <tr>
                      <td class="py-3">Discount</td>
                      <td class="py-3" id="displayDiscount">TK 0.00</td>
                   </tr>
                   <tr>
                      <td class="py-3">Shipping</td>
                      <td class="py-3" id="shippingDisplay">TK 0.00</td>
                   </tr>
                   <tr>
                      <td class="py-3 text-primary">Grand Total</td>
                      <td class="py-3 text-primary" id="grandTotal">TK 0.00</td>
                      <input type="hidden" name="grand_total">
                   </tr>


                  <tr class="d-none">
                      <td class="py-3">Paid Amount</td>
                      <td class="py-3" id="paidAmount">
                      <input type="text" name="paid_amount" placeholder="Enter amount paid" class="form-control">
                      </td>
                   </tr>
                   <!-- new add full paid functionality  -->
                   <tr class="d-none">
                      <td class="py-3">Full Paid</td>
                      <td class="py-3" id="fullPaid">
                         <input type="text" name="full_paid" id="fullPaidInput">
                      </td>
                   </tr>
                   <tr class="d-none">
                      <td class="py-3">Due Amount</td>
                      <td class="py-3" id="dueAmount">TK 0.00</td>
                      <input type="hidden" name="due_amount">
                   </tr>


                </tbody>
             </table>
          </div>
       </div>
    </div>
 </div>
</div>


      <div class="row">
         <div class="col-md-4">
            <label class="form-label">Discount: </label>
            <input type="number" step="0.01" id="inputDiscount" name="discount" class="form-control @error('discount') is-invalid @enderror" value="{{ old('discount', '0.00') }}" min="0">
            @error('discount')
            <span class="text-danger">{{ $message }}</span>
            @enderror
         </div>
         <div class="col-md-4">
            <label class="form-label">Shipping: </label>
            <input type="number" step="0.01" id="inputShipping" name="shipping" class="form-control @error('shipping') is-invalid @enderror" value="{{ old('shipping', '0.00') }}" min="0">
            @error('shipping')
            <span class="text-danger">{{ $message }}</span>
            @enderror
         </div>
         <div class="col-md-4">
            <div class="form-group w-100">
               <label class="form-label" for="formBasic">Status : <span class="text-danger">*</span></label>
               <select name="status" id="status" class="form-control form-select @error('status') is-invalid @enderror">
                  <option value="">Select Status</option>
                  <option value="Transfer" {{ old('status') == 'Transfer' ? 'selected' : '' }}>Transfer</option>
                  <option value="Pending" {{ old('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                  <option value="Ordered" {{ old('status') == 'Ordered' ? 'selected' : '' }}>Ordered</option>
               </select>
               @error('status')
                  <span class="text-danger">{{ $message }}</span>
               @enderror
            </div>
         </div>
      </div>

      <div class="col-md-12 mt-2">
         <label class="form-label">Notes: </label>
         <textarea class="form-control @error('note') is-invalid @enderror" name="note" rows="3" placeholder="Enter Notes">{{ old('note') }}</textarea>
         @error('note')
         <span class="text-danger">{{ $message }}</span>
         @enderror
      </div>
   </div>
</div>
</div>

     <div class="col-xl-12">
        <div class="d-flex mt-5 justify-content-end">
           <button class="btn btn-primary me-3" type="submit">Save</button>
           <a class="btn btn-secondary" href="{{ route('all.transfer') }}">Cancel</a>
        </div>
     </div>
  </div>
</form>
            </div>
         </div>
      </div>
   </div>
</div>


<script>
    var productSearchUrl = "{{ route('purchase.product.search') }}"
</script>

<script>
// Override product search for transfer form - runs after custome.js
setTimeout(function() {
  let productSearchInput = document.getElementById('product_search');
  let fromWarehouseDropdown = document.getElementById('from_warehouse_id');
  let productList = document.getElementById('product_list');
  let productError = document.getElementById('product_error');
  
  // Only override if this is transfer form (has from_warehouse_id)
  if (productSearchInput && fromWarehouseDropdown && productList) {
    // Clone and replace to remove existing listeners
    let newInput = productSearchInput.cloneNode(true);
    productSearchInput.parentNode.replaceChild(newInput, productSearchInput);
    productSearchInput = newInput;
    
    productSearchInput.addEventListener('keyup', function (e) {
      e.stopPropagation(); // Prevent custome.js handler
      let query = this.value.trim();
      let warehouse_id = fromWarehouseDropdown.value;
      
      // Clear previous errors
      if (productError) {
        productError.style.display = 'none';
        productError.textContent = '';
      }
      
      if (!warehouse_id) {
        productList.innerHTML = '';
        if (productError) {
          productError.textContent = 'Please select the warehouse first';
          productError.style.display = 'block';
        }
        return;
      }
      
      if (query.length > 1) {
        fetchProductsForTransfer(query, warehouse_id);
      } else {
        productList.innerHTML = '';
        if (productError) {
          productError.style.display = 'none';
        }
      }
    });
    
    function fetchProductsForTransfer(query, warehouse_id) {
      fetch(
        productSearchUrl + '?query=' + encodeURIComponent(query) + '&warehouse_id=' + encodeURIComponent(warehouse_id),
        {
          method: 'GET',
          headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
          },
          credentials: 'same-origin'
        }
      )
        .then((response) => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then((data) => {
          productList.innerHTML = '';
          if (productError) {
            productError.style.display = 'none';
          }
          
          if (data && data.length > 0) {
            data.forEach((product) => {
              let item = `<a href="#" class="list-group-item list-group-item-action product-item"
                              data-id="${product.id}"
                              data-code="${product.code || ''}"
                              data-name="${product.name || ''}"
                              data-cost="${product.price || 0}"
                              data-stock="${product.product_qty || 0}">
                              <span class="mdi mdi-text-search"></span>
                              ${product.code || ''} - ${product.name || ''}
                              </a> `;
              productList.innerHTML += item;
            });

            // Add event listener for product selection
            document.querySelectorAll('.product-item').forEach((item) => {
              item.addEventListener('click', function (e) {
                e.preventDefault();
                addProductToTransferTable(this);
              });
            });
          } else {
            if (productError) {
              productError.textContent = 'Product not found';
              productError.style.display = 'block';
            }
          }
        })
        .catch((error) => {
          console.error('Error fetching products:', error);
          if (productError) {
            productError.textContent = 'Error loading products. Please try again.';
            productError.style.display = 'block';
          }
        });
    }
    
    // Function to add product to transfer table
    function addProductToTransferTable(productElement) {
      // Find the correct tbody for order items table (not summary table)
      let orderItemsTable = document.querySelector('.table.table-striped.table-bordered.dataTable');
      let orderItemsTableBody = orderItemsTable ? orderItemsTable.querySelector('tbody') : null;
      
      if (!orderItemsTableBody) {
        console.error('Order items table body not found');
        return;
      }
      
      let productId = productElement.getAttribute('data-id');
      let productCode = productElement.getAttribute('data-code');
      let productName = productElement.getAttribute('data-name');
      let netUnitCost = parseFloat(productElement.getAttribute('data-cost'));
      let stock = parseInt(productElement.getAttribute('data-stock'));

      // Check if product already exists in table
      if (orderItemsTableBody.querySelector(`tr[data-id="${productId}"]`)) {
        alert('Product already added.');
        return;
      }

      let row = `
        <tr data-id="${productId}">
            <td style="word-wrap: break-word; word-break: break-word; white-space: normal; line-height: 1.5;">
                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 5px;">
                  <span style="flex: 1; min-width: 0;">${productCode} - ${productName}</span>
                  <button type="button" class="btn btn-primary btn-sm edit-discount-btn"
                      data-id="${productId}"
                      data-name="${productName}"
                      data-cost="${netUnitCost}"
                      data-bs-toggle="modal"
                      style="flex-shrink: 0;">
                      <span class="mdi mdi-book-edit "></span>
                  </button>
                </div>
                <input type="hidden" name="products[${productId}][id]" value="${productId}">
                <input type="hidden" name="products[${productId}][name]" value="${productName}">
                <input type="hidden" name="products[${productId}][code]" value="${productCode}">
            </td>
            <td>${netUnitCost.toFixed(2)}
                <input type="hidden" name="products[${productId}][cost]" value="${netUnitCost}">
            </td>
            <td style="color:#ffc121">${stock}</td>
            <td>
                <div class="input-group">
                    <button class="btn btn-outline-secondary decrement-qty" type="button">−</button>
                    <input type="text" class="form-control text-center qty-input"
                        name="products[${productId}][quantity]" value="1" min="1" max="${stock}"
                        data-cost="${netUnitCost}" style="width: 30px;">
                    <button class="btn btn-outline-secondary increment-qty" type="button">+</button>
                </div>
            </td>
            <td>
                <input type="number" class="form-control discount-input"
                    name="products[${productId}][discount]" value="0" min="0" style="width:100px">
            </td>
            <td class="subtotal">${netUnitCost.toFixed(2)}</td>
            <td><button class="btn btn-danger btn-sm remove-product"><span class="mdi mdi-delete-circle mdi-18px"></span></button></td>
        </tr>
      `;

      orderItemsTableBody.innerHTML += row;
      productList.innerHTML = '';
      productSearchInput.value = '';

      // Update grand total
      updateTransferGrandTotal();
    }
    
    // Set up event delegation once for the transfer table
    let orderItemsTable = document.querySelector('.table.table-striped.table-bordered.dataTable');
    let orderItemsTableBody = orderItemsTable ? orderItemsTable.querySelector('tbody') : null;
    
    if (orderItemsTableBody) {
      // Quantity and discount inputs - use event delegation
      orderItemsTableBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('discount-input')) {
          let row = e.target.closest('tr');
          let qty = parseInt(row.querySelector('.qty-input').value) || 1;
          let unitCost = parseFloat(row.querySelector('.qty-input').getAttribute('data-cost')) || 0;
          let discount = parseFloat(row.querySelector('.discount-input').value) || 0;
          let subtotal = unitCost * qty - discount;
          row.querySelector('.subtotal').textContent = subtotal.toFixed(2);
          updateTransferGrandTotal();
        }
      });

      // Increment and decrement buttons - use event delegation
      orderItemsTableBody.addEventListener('click', function(e) {
        if (e.target.closest('.increment-qty')) {
          e.preventDefault();
          let button = e.target.closest('.increment-qty');
          let input = button.closest('.input-group').querySelector('.qty-input');
          let max = parseInt(input.getAttribute('max'));
          let value = parseInt(input.value);
          if (value < max) {
            input.value = value + 1;
            updateTransferSubtotal(button.closest('tr'));
          }
        } else if (e.target.closest('.decrement-qty')) {
          e.preventDefault();
          let button = e.target.closest('.decrement-qty');
          let input = button.closest('.input-group').querySelector('.qty-input');
          let min = parseInt(input.getAttribute('min'));
          let value = parseInt(input.value);
          if (value > min) {
            input.value = value - 1;
            updateTransferSubtotal(button.closest('tr'));
          }
        } else if (e.target.closest('.remove-product')) {
          e.preventDefault();
          e.target.closest('tr').remove();
          updateTransferGrandTotal();
        }
      });
    }
    
    function updateTransferSubtotal(row) {
      let qty = parseFloat(row.querySelector('.qty-input').value);
      let discount = parseFloat(row.querySelector('.discount-input').value) || 0;
      let netUnitCost = parseFloat(row.querySelector('.qty-input').dataset.cost);
      let subtotal = netUnitCost * qty - discount;
      row.querySelector('.subtotal').innerText = subtotal.toFixed(2);
      updateTransferGrandTotal();
    }
    
    // Grand total update function for transfer
    function updateTransferGrandTotal() {
      let grandTotal = 0;
      document.querySelectorAll('.subtotal').forEach(function (item) {
        grandTotal += parseFloat(item.textContent) || 0;
      });
      
      let discount = parseFloat(document.getElementById('inputDiscount').value) || 0;
      let shipping = parseFloat(document.getElementById('inputShipping').value) || 0;
      grandTotal = grandTotal - discount + shipping;
      
      if (grandTotal < 0) {
        grandTotal = 0;
      }
      
      document.getElementById('grandTotal').textContent = `TK ${grandTotal.toFixed(2)}`;
      document.querySelector("input[name='grand_total']").value = grandTotal.toFixed(2);
    }
    
    // Event listeners for discount and shipping
    let discountInput = document.getElementById('inputDiscount');
    let shippingInput = document.getElementById('inputShipping');
    
    if (discountInput) {
      discountInput.addEventListener('input', function() {
        updateTransferGrandTotal();
        document.getElementById('displayDiscount').textContent = 'TK ' + (this.value || '0.00');
      });
    }
    
    if (shippingInput) {
      shippingInput.addEventListener('input', function() {
        updateTransferGrandTotal();
        document.getElementById('shippingDisplay').textContent = 'TK ' + (this.value || '0.00');
      });
    }
    
    // Also listen to warehouse change to clear product list
    fromWarehouseDropdown.addEventListener('change', function() {
      if (!this.value) {
        productList.innerHTML = '';
        productSearchInput.value = '';
        if (productError) {
          productError.textContent = 'Please select the warehouse first';
          productError.style.display = 'block';
        }
      } else {
        if (productError) {
          productError.style.display = 'none';
        }
      }
    });
  }
}, 100); // Run after custome.js
</script>


@endsection
