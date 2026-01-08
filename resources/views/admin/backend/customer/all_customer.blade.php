@extends('admin.admin_master')
@section('admin')
  <div class="content">
    <!-- Start Content-->
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column justify-content-between mb-4">
        <div class="flex-grow-1">
          <h4 class="fs-20 fw-semibold m-0 mb-2" style="color: #212529;">All Customer</h4>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
              <li class="breadcrumb-item active">All Customer</li>
            </ol>
          </nav>
        </div>

        <div class="text-end mt-3 mt-sm-0">
          <a href="{{ route('add.customer') }}" class="btn btn-primary">
            <i data-feather="plus" style="width: 16px; height: 16px; margin-right: 6px;"></i>
            Add Customer
          </a>
        </div>
      </div>

      <!-- Datatables  -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0 fw-semibold">
                <i data-feather="users" style="width: 18px; height: 18px; margin-right: 8px;"></i>
                Customer List
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
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($customer as $key => $item)
                    <tr>
                      <td>{{ $key + 1 }}</td>
                      <td>{{ $item->name }}</td>
                      <td>{{ $item->email }}</td>
                      <td>{{ $item->phone }}</td>
                      <td>{{ Str::limit($item->address, 50, '...') }}</td>
                      <td>
                        <a
                          href="{{ route('edit.customer', $item->id) }}"
                          class="btn btn-success btn-sm"
                        >
                          Edit
                        </a>
                        <a
                          href="{{ route('delete.customer', $item->id) }}"
                          class="btn btn-danger btn-sm"
                          id="delete"
                          data-delete-text="this customer"
                        >
                          Delete
                        </a>
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
