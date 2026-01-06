@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-4">
         <div class="d-md-flex align-items-center justify-content-between">
            <h3 class="mb-0">Create Sales Return</h3>
            <div class="text-end my-2 mt-md-0"><a class="btn btn-outline-primary" href="{{ route('all.return.sale') }}">Back</a></div>
         </div>


 <div class="card">
    <div class="card-body">
    <form action="{{ route('store.return.sale')}}" method="post" enctype="multipart/form-data">
       @csrf


<div class="row">
 <div class="col-xl-12">
    <div class="card">
       <div class="row">
          <div class="col-md-4 mb-3">
                <label class="form-label">Date:  <span class="text-danger">*</span></label>
             <input type="date" name="date" value="{{ old('date', date('Y-m-d')) }}" class="form-control {{ $errors->has('date') ? 'is-invalid' : '' }}">
             @error('date')
             <span class="text-danger">{{ $message }}</span>
             @enderror
          </div>

          <div class="col-md-4 mb-3">
                <div class="form-group w-100">
                <label class="form-label" for="formBasic">Warehouse : <span class="text-danger">*</span></label>
                <select name="warehouse_id" id="warehouse_id" class="form-control form-select {{ $errors->has('warehouse_id') ? 'is-invalid' : '' }}">
                      <option value="">Select Warehouse</option>
                      @foreach ($warehouses as $item)
                      <option value="{{ $item->id }}" {{ old('warehouse_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                      @endforeach
                </select>
                @error('warehouse_id')
                <span class="text-danger">{{ $message }}</span>
                @enderror
                <small id="warehouse_error" class="text-danger d-none">Please select the first warehouse.</small>
                </div>
          </div>

          <div class="col-md-4 mb-3">
             <div class="form-group w-100">
                <label class="form-label" for="formBasic">Customer : <span class="text-danger">*</span></label>
                <select name="customer_id" id="customer_id" class="form-control form-select {{ $errors->has('customer_id') ? 'is-invalid' : '' }}">
                   <option value="">Select Customer</option>
                   @foreach ($customers as $item)
                   <option value="{{ $item->id }}" {{ old('customer_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                   @endforeach
                </select>
                @error('customer_id')
                <span class="text-danger">{{ $message }}</span>
                @enderror
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


                  <tr>
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
                   <tr>
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
            <input type="number" id="inputDiscount" name="discount" class="form-control" value="0.00">
         </div>
         <div class="col-md-4">
            <label class="form-label">Shipping: </label>
            <input type="number" id="inputShipping" name="shipping" class="form-control" value="0.00">
         </div>
         <div class="col-md-4">
            <div class="form-group w-100">
               <label class="form-label" for="formBasic">Status : <span class="text-danger">*</span></label>
               <select name="status" id="status" class="form-control form-select {{ $errors->has('status') ? 'is-invalid' : '' }}">
                  <option value="">Select Status</option>
                  <option value="Return" {{ old('status') == 'Return' ? 'selected' : '' }}>Return</option>
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
         <textarea class="form-control" name="note" rows="3" placeholder="Enter Notes"></textarea>
      </div>
   </div>
</div>
</div>

     <div class="col-xl-12">
        <div class="d-flex mt-5 justify-content-end">
           <button class="btn btn-primary me-3" type="submit">Save</button>
           <a class="btn btn-secondary" href="{{ route('all.return.sale') }}">Cancel</a>
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
    
    // Override addProductToTable function for sale return
    document.addEventListener('DOMContentLoaded', function() {
        // Wait for custome.js to load, then override
        setTimeout(function() {
            if (typeof addProductToTable === 'function') {
                // Store original function
                let originalAddProductToTable = addProductToTable;
                
                // Override function
                window.addProductToTable = function(productElement) {
                    let productId = productElement.getAttribute('data-id');
                    let productCode = productElement.getAttribute('data-code');
                    let productName = productElement.getAttribute('data-name');
                    let netUnitCost = parseFloat(productElement.getAttribute('data-cost'));
                    let stock = parseInt(productElement.getAttribute('data-stock'));
                    
                    // Check if product already exists in table
                    let orderItemsTableBody = document.querySelector('tbody');
                    if (orderItemsTableBody.querySelector(`tr[data-id="${productId}"]`)) {
                        alert('Product already added.');
                        return;
                    }
                    
                    let row = `
                        <tr data-id="${productId}">
                            <td style="word-wrap: break-word; word-break: break-word; white-space: normal; line-height: 1.5;">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 5px;">
                                    <span style="flex: 1; min-width: 0;">${productCode} - ${productName}</span>
                                </div>
                                <input type="hidden" name="products[${productId}][id]" value="${productId}">
                            </td>
                            <td>${netUnitCost.toFixed(2)}
                                <input type="hidden" name="products[${productId}][net_unit_cost]" value="${netUnitCost}">
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
                            <td><button type="button" class="btn btn-danger btn-sm remove-product"><span class="mdi mdi-delete-circle mdi-18px"></span></button></td>
                        </tr>
                    `;
                    
                    orderItemsTableBody.innerHTML += row;
                    document.getElementById('product_list').innerHTML = '';
                    document.getElementById('product_search').value = '';
                    
                    // Update events for new row
                    updateSaleReturnEvents();
                    updateSaleReturnGrandTotal();
                };
            }
        }, 100);
    });
    
    // Update events for sale return
    function updateSaleReturnEvents() {
        document.querySelectorAll('.qty-input, .discount-input').forEach((input) => {
            // Remove existing listeners by cloning
            let newInput = input.cloneNode(true);
            input.parentNode.replaceChild(newInput, input);
            
            newInput.addEventListener('input', function() {
                let row = this.closest('tr');
                let qty = parseInt(row.querySelector('.qty-input').value) || 1;
                let netUnitCost = parseFloat(row.querySelector('.qty-input').getAttribute('data-cost')) || 0;
                let discount = parseFloat(row.querySelector('.discount-input').value) || 0;
                
                let subtotal = netUnitCost * qty - discount;
                row.querySelector('.subtotal').textContent = subtotal.toFixed(2);
                
                updateSaleReturnGrandTotal();
            });
        });
        
        // Increment quantity
        document.querySelectorAll('.increment-qty').forEach((button) => {
            let newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function() {
                let input = this.closest('.input-group').querySelector('.qty-input');
                let max = parseInt(input.getAttribute('max'));
                let value = parseInt(input.value) || 1;
                if (value < max) {
                    input.value = value + 1;
                    input.dispatchEvent(new Event('input'));
                }
            });
        });
        
        // Decrement quantity
        document.querySelectorAll('.decrement-qty').forEach((button) => {
            let newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function() {
                let input = this.closest('.input-group').querySelector('.qty-input');
                let min = parseInt(input.getAttribute('min'));
                let value = parseInt(input.value) || 1;
                if (value > min) {
                    input.value = value - 1;
                    input.dispatchEvent(new Event('input'));
                }
            });
        });
        
        // Remove product row
        document.querySelectorAll('.remove-product').forEach((button) => {
            let newButton = button.cloneNode(true);
            button.parentNode.replaceChild(newButton, button);
            
            newButton.addEventListener('click', function() {
                this.closest('tr').remove();
                updateSaleReturnGrandTotal();
            });
        });
    }
    
    // Update grand total for sale return
    function updateSaleReturnGrandTotal() {
        let grandTotal = 0;
        
        document.querySelectorAll('.subtotal').forEach(function(item) {
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
        
        updateSaleReturnDueAmount();
    }
    
    // Update due amount for sale return
    function updateSaleReturnDueAmount() {
        let grandTotal = parseFloat(document.querySelector("input[name='grand_total']").value) || 0;
        let paidAmount = parseFloat(document.querySelector("input[name='paid_amount']").value) || 0;
        let dueAmount = grandTotal - paidAmount;
        
        if (dueAmount < 0) {
            dueAmount = 0;
        }
        
        document.getElementById('dueAmount').textContent = `TK ${dueAmount.toFixed(2)}`;
        document.querySelector("input[name='due_amount']").value = dueAmount.toFixed(2);
    }
    
    // Add event listeners for discount, shipping, and paid amount
    document.addEventListener('DOMContentLoaded', function() {
        let discountInput = document.getElementById('inputDiscount');
        let shippingInput = document.getElementById('inputShipping');
        let paidAmountInput = document.querySelector("input[name='paid_amount']");
        
        if (discountInput) {
            discountInput.addEventListener('input', updateSaleReturnGrandTotal);
        }
        if (shippingInput) {
            shippingInput.addEventListener('input', updateSaleReturnGrandTotal);
        }
        if (paidAmountInput) {
            paidAmountInput.addEventListener('input', updateSaleReturnDueAmount);
        }
    });
</script>


@endsection
