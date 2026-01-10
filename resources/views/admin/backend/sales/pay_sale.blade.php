@extends('admin.admin_master')
@section('admin')

<div class="content d-flex flex-column flex-column-fluid">
   <div class="d-flex flex-column-fluid">
      <div class="container-fluid my-4">
         <div class="d-md-flex align-items-center justify-content-between">
            <h3 class="mb-0">Pay Sale</h3>
            <div class="text-end my-2 mt-md-0">
               <a class="btn btn-outline-primary" href="{{ route('due.sale') }}">Back</a>
            </div>
         </div>

         <div class="card">
            <div class="card-body">
               <form action="{{ route('update.sale.payment', $sale->id) }}" method="post">
                  @csrf

                  <div class="row mb-4">
                     <div class="col-md-6">
                        <div class="card shadow-sm border-0">
                           <div class="card-header text-white text-center" style="background: linear-gradient(135deg, #17a2b8, #0d6efd);">
                              <h5 class="mb-0 fw-bold">Sale Information</h5>
                           </div>
                           <div class="card-body p-4">
                              <div class="mb-3">
                                 <strong class="text-muted">Sale ID:</strong>
                                 <span class="ms-2">#{{ $sale->id }}</span>
                              </div>
                              <div class="mb-3">
                                 <strong class="text-muted">Customer:</strong>
                                 <span class="ms-2">{{ $sale->customer->name ?? 'N/A' }}</span>
                              </div>
                              <div class="mb-3">
                                 <strong class="text-muted">Warehouse:</strong>
                                 <span class="ms-2">{{ $sale->warehouse->name ?? 'N/A' }}</span>
                              </div>
                              <div class="mb-3">
                                 <strong class="text-muted">Date:</strong>
                                 <span class="ms-2">{{ \Carbon\Carbon::parse($sale->date)->format('Y-m-d') }}</span>
                              </div>
                              <div class="mb-3">
                                 <strong class="text-muted">Status:</strong>
                                 <span class="ms-2 badge bg-info">{{ $sale->status }}</span>
                              </div>
                           </div>
                        </div>
                     </div>

                     <div class="col-md-6">
                        <div class="card shadow-sm border-0">
                           <div class="card-header text-white text-center" style="background: linear-gradient(135deg, #28a745, #20c997);">
                              <h5 class="mb-0 fw-bold">Payment Summary</h5>
                           </div>
                           <div class="card-body p-4">
                              <div class="table-responsive">
                                 <table class="table border">
                                    <tbody>
                                       <tr>
                                          <td class="py-3"><strong>Grand Total:</strong></td>
                                          <td class="py-3 text-primary"><strong>${{ number_format($sale->grand_total, 2) }}</strong></td>
                                       </tr>
                                       <tr>
                                          <td class="py-3"><strong>Paid Amount:</strong></td>
                                          <td class="py-3">${{ number_format($sale->paid_amount, 2) }}</td>
                                       </tr>
                                       <tr>
                                          <td class="py-3"><strong>Full Paid:</strong></td>
                                          <td class="py-3">${{ number_format($sale->full_paid ?? 0, 2) }}</td>
                                       </tr>
                                       <tr>
                                          <td class="py-3"><strong>Due Amount:</strong></td>
                                          <td class="py-3">
                                             <span class="badge bg-danger">${{ number_format($sale->due_amount, 2) }}</span>
                                          </td>
                                       </tr>
                                    </tbody>
                                 </table>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>

                  <div class="row">
                     <div class="col-md-8 mx-auto">
                        <div class="card shadow-sm border-0">
                           <div class="card-header text-white text-center" style="background: linear-gradient(135deg, #6f42c1, #e83e8c);">
                              <h5 class="mb-0 fw-bold">Update Payment</h5>
                           </div>
                           <div class="card-body p-4">
                              <div class="mb-3">
                                 <label class="form-label"><strong>Paid Amount: <span class="text-danger">*</span></strong></label>
                                 <input type="number" 
                                        name="paid_amount" 
                                        id="paid_amount" 
                                        class="form-control" 
                                        value="{{ $sale->paid_amount }}" 
                                        step="0.01" 
                                        min="0" 
                                        max="{{ $sale->grand_total }}"
                                        required>
                                 @error('paid_amount')
                                    <span class="text-danger">{{ $message }}</span>
                                 @enderror
                                 <small class="text-muted">Current due amount: ${{ number_format($sale->due_amount, 2) }}</small>
                              </div>

                              <div class="mb-3">
                                 <label class="form-label"><strong>Full Paid:</strong></label>
                                 <input type="number" 
                                        name="full_paid" 
                                        id="full_paid" 
                                        class="form-control" 
                                        value="{{ $sale->full_paid ?? 0 }}" 
                                        step="0.01" 
                                        min="0"
                                        placeholder="0.00">
                                 @error('full_paid')
                                    <span class="text-danger">{{ $message }}</span>
                                 @enderror
                                 <small class="text-muted">Additional payment amount</small>
                              </div>

                              <div class="mb-3">
                                 <label class="form-label"><strong>New Due Amount:</strong></label>
                                 <input type="text" 
                                        id="new_due_amount" 
                                        class="form-control" 
                                        readonly 
                                        style="background-color: #f8f9fa;">
                                 <small class="text-muted">This will be calculated automatically</small>
                              </div>

                              <div class="text-end mt-4">
                                 <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="mdi mdi-check-circle me-2"></i>Update Payment
                                 </button>
                              </div>
                           </div>
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
document.addEventListener('DOMContentLoaded', function() {
   const paidAmountInput = document.getElementById('paid_amount');
   const fullPaidInput = document.getElementById('full_paid');
   const newDueAmountInput = document.getElementById('new_due_amount');
   const grandTotal = {{ $sale->grand_total }};

   function calculateDueAmount() {
      const paidAmount = parseFloat(paidAmountInput.value) || 0;
      const fullPaid = parseFloat(fullPaidInput.value) || 0;
      const newDueAmount = Math.max(0, grandTotal - paidAmount - fullPaid);
      
      newDueAmountInput.value = '$' + newDueAmount.toFixed(2);
      
      if (newDueAmount === 0) {
         newDueAmountInput.style.backgroundColor = '#d4edda';
         newDueAmountInput.style.color = '#155724';
      } else {
         newDueAmountInput.style.backgroundColor = '#f8d7da';
         newDueAmountInput.style.color = '#721c24';
      }
   }

   paidAmountInput.addEventListener('input', calculateDueAmount);
   fullPaidInput.addEventListener('input', calculateDueAmount);
   
   // Calculate on page load
   calculateDueAmount();
});
</script>

@endsection
