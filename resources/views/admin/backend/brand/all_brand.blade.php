@extends('admin.admin_master')
@section('admin')
  <div class="content">
    <!-- Start Content-->
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column justify-content-between mb-4">
        <div class="flex-grow-1">
          <h4 class="fs-20 fw-semibold m-0 mb-2" style="color: #212529;">All Brand</h4>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
              <li class="breadcrumb-item active">All Brand</li>
            </ol>
          </nav>
        </div>

        <div class="text-end mt-3 mt-sm-0">
          <a href="{{ route('add.brand') }}" class="btn btn-primary">
            <i data-feather="plus" style="width: 16px; height: 16px; margin-right: 6px;"></i>
            Add Brand
          </a>
        </div>
      </div>

      <!-- Datatables  -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0 fw-semibold">
                <i data-feather="tag" style="width: 18px; height: 18px; margin-right: 8px;"></i>
                Brand List
              </h5>
            </div>
            <!-- end card header -->

            <div class="card-body">
              <table
                id="datatable"
                class="table table-bordered dt-responsive table-responsive nowrap"
              >
                <thead>
                  <tr>
                    <th>Sl</th>
                    <th>Brand Name</th>
                    <th>Image</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($brand as $key => $item)
                    <tr>
                      <td>{{ $key + 1 }}</td>
                      <td>{{ $item->name }}</td>
                      <td>
                        @php
                          try {
                            $imageUrl = $item->image 
                              ? Storage::disk($imageDisk ?? 'public')->url($item->image) 
                              : asset('upload/no_image.jpg');
                          } catch (\Exception $e) {
                            $imageUrl = asset('upload/no_image.jpg');
                          }
                        @endphp
                        <img
                          src="{{ $imageUrl }}"
                          alt="{{ $item->name }}"
                          style="width: 70px; height: 40px; object-fit: cover; border-radius: 8px; border: 2px solid #e9ecef;"
                          onerror="this.src='{{ asset('upload/no_image.jpg') }}'"
                        />
                      </td>
                      <td>
                        @if (Auth::guard('web')->user()->can('edit.brand'))
                          <a
                            href="{{ route('edit.brand', $item->id) }}"
                            class="btn btn-success btn-sm"
                            title="Edit"
                          >
                            Edit
                          </a>
                        @endif

                        @if (Auth::guard('web')->user()->can('delete.brand'))
                          <a
                            href="{{ route('delete.brand', $item->id) }}"
                            class="btn btn-danger btn-sm"
                            id="delete"
                            title="Delete"
                            data-delete-text="this brand"
                          >
                            Delete
                          </a>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- container-fluid -->
  </div>
  <!-- content -->
@endsection
