@extends('admin.admin_master')
@section('admin')
  @php
    use Illuminate\Support\Facades\Storage;
  @endphp
  <div class="content">
    <!-- Start Content-->
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
          <h4 class="fs-18 fw-semibold m-0">Product Details</h4>
        </div>

        <div class="text-end">
          <ol class="breadcrumb m-0 py-0">
            <a href="{{ route('all.product') }}" class="btn btn-dark">Back</a>
          </ol>
        </div>
      </div>

      <hr />
      <div class="card">
        <div class="card-body">
          <div class="row">
            {{-- // Product Image --}}
            <div class="col-md-4">
              <h5 class="mb-3">Product Images</h5>
              <div class="d-flex flex-wrap">
                @if(isset($product->images) && $product->images->count() > 0)
                  @foreach($product->images as $image)
                    @php
                      $imageUrl = asset('upload/no_image.jpg');
                      try {
                        if ($image && !empty($image->image)) {
                          $imageUrl = Storage::disk($imageDisk ?? 'public')->url($image->image);
                        }
                      } catch (\Exception $e) {
                        $imageUrl = asset('upload/no_image.jpg');
                      }
                    @endphp
                    <img
                      src="{{ $imageUrl }}"
                      alt="image"
                      class="me-2 mb-2"
                      width="100"
                      height="100"
                      style="
                        object-fit: cover;
                        border: 1px solid #ddd;
                        border-radius: 5px;
                      "
                      onerror="this.src='{{ asset('upload/no_image.jpg') }}'"
                    />
                  @endforeach
                @else
                  <p class="text-danger">No Image Available</p>
                @endif
              </div>
            </div>

            {{-- // Product Details Data --}}
            <div class="col-md-8">
              <h5 class="mb-3">Product Information</h5>
              <ul class="list-group">
                <li class="list-group-item">
                  <strong>Name:</strong>
                  {{ $product->name ?? 'N/A' }}
                </li>
                <li class="list-group-item">
                  <strong>Code:</strong>
                  {{ $product->code ?? 'N/A' }}
                </li>
                <li class="list-group-item">
                  <strong>Warehouse:</strong>
                  {{ ($product->warehouse && $product->warehouse->name) ? $product->warehouse->name : 'N/A' }}
                </li>
                <li class="list-group-item">
                  <strong>Supplier:</strong>
                  {{ ($product->supplier && $product->supplier->name) ? $product->supplier->name : 'N/A' }}
                </li>
                <li class="list-group-item">
                  <strong>Category:</strong>
                  {{ ($product->category && $product->category->category_name) ? $product->category->category_name : 'N/A' }}
                </li>
                <li class="list-group-item">
                  <strong>Brand:</strong>
                  {{ ($product->brand && $product->brand->name) ? $product->brand->name : 'N/A' }}
                </li>
                <li class="list-group-item">
                  <strong>Price:</strong>
                  ${{ number_format($product->price ?? 0, 2) }}
                </li>
                <li class="list-group-item">
                  <strong>Stock Alert:</strong>
                  {{ $product->stock_alert ?? 0 }}
                </li>
                <li class="list-group-item">
                  <strong>Product Qty:</strong>
                  {{ $product->product_qty ?? 0 }}
                </li>
                <li class="list-group-item">
                  <strong>Product Status:</strong>
                  {{ $product->status ?? 'N/A' }}
                </li>
                <li class="list-group-item">
                  <strong>Product Note:</strong>
                  {{ $product->note ?? 'N/A' }}
                </li>
                <li class="list-group-item">
                  <strong>Create On:</strong>
                  @if($product->created_at)
                    {{ \Carbon\Carbon::parse($product->created_at)->format('d F Y') }}
                  @else
                    N/A
                  @endif
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
