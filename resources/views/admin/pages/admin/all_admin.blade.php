@extends('admin.admin_master')
@section('admin')
  <div class="content">
    <!-- Start Content-->
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
          <h4 class="fs-18 fw-semibold m-0">All Employee</h4>
        </div>

        <div class="text-end">
          <ol class="breadcrumb m-0 py-0">
            <a href="{{ route('add.employee') }}" class="btn btn-primary">
              Add Employee
            </a>
          </ol>
        </div>
      </div>

      <!-- Datatables  -->
      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-header"></div>
            <!-- end card header -->

            <div class="card-body">
              <table
                id="datatable"
                class="table table-bordered dt-responsive table-responsive nowrap"
              >
                <thead>
                  <tr>
                    <th>Sl</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($alladmin as $key => $item)
                    <tr>
                      <td>{{ $key + 1 }}</td>
                      <td>{{ $item->name }}</td>
                      <td>{{ $item->email }}</td>
                      <td>
                        @foreach ($item->roles as $role)
                          <span class="badge badge-pill bg-danger">
                            {{ $role->name ?? 'N/A' }}
                          </span>
                        @endforeach
                      </td>
                      <td>
                        @if (Auth::guard('web')->user()->can('role_and_permission.all'))
                          <a
                            href="{{ route('details.employee', $item->id) }}"
                            class="btn btn-info btn-sm"
                            title="View Details"
                          >
                            <i class="mdi mdi-eye me-1"></i>Details
                          </a>
                        @endif
                        @if (Auth::guard('web')->user()->can('role_and_permission.all'))
                          <a
                            href="{{ route('edit.employee.roles', $item->id) }}"
                            class="btn btn-warning btn-sm"
                            title="Edit Roles"
                          >
                            <i class="mdi mdi-account-edit me-1"></i>Edit Roles
                          </a>
                        @endif
                        @if (Auth::guard('web')->user()->can('role_and_permission.all'))
                          <a
                            href="{{ route('delete.employee', $item->id) }}"
                            class="btn btn-danger btn-sm"
                            id="delete"
                            data-delete-text="this employee"
                          >
                            <i class="mdi mdi-delete me-1"></i>Delete
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
