@extends('admin.admin_master')
@section('admin')
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <style>
    .form-check-label {
      text-transform: capitalize;
    }
  </style>

  <div class="content">
    <!-- Start Content-->
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
          <h4 class="fs-18 fw-semibold m-0">Edit Employee Roles</h4>
        </div>

        <div class="text-end">
          <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item"><a href="{{ route('all.employee') }}">All Employee</a></li>
            <li class="breadcrumb-item active">Edit Employee Roles</li>
          </ol>
        </div>
      </div>

      <!-- Form Validation -->
      <div class="row">
        <div class="col-xl-12">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">Edit Employee Roles - {{ $employee->name }}</h5>
            </div>
            <!-- end card header -->

            <div class="card-body">
              <form
                action="{{ route('update.employee.roles', $employee->id) }}"
                method="post"
                class="row g-3"
                enctype="multipart/form-data"
              >
                @csrf

                <div class="col-md-6 mb-3">
                  <label for="validationDefault01" class="form-label">
                    Employee Name
                  </label>
                  <div class="form-control" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 15px; min-height: 42px;">
                    {{ $employee->name }}
                  </div>
                </div>

                <div class="col-md-6 mb-3">
                  <label for="validationDefault01" class="form-label">
                    Employee Email
                  </label>
                  <div class="form-control" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 15px; min-height: 42px;">
                    {{ $employee->email }}
                  </div>
                </div>

                <div class="col-md-12 mb-3">
                  <label for="validationDefault01" class="form-label">
                    Role
                  </label>
                  <select name="roles" class="form-select" id="example-select">
                    <option value="">Select Role</option>
                    @foreach ($roles as $role)
                      <option value="{{ $role->id }}" {{ $employee->hasRole($role->name) ? 'selected' : '' }}>
                        {{ $role->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-12">
                  <hr>
                  <h5 class="mb-3">Permissions</h5>
                </div>

                <div class="form-check mb-2">
                  <input
                    class="form-check-input"
                    type="checkbox"
                    id="formCheck1"
                  />
                  <label class="form-check-label" for="formCheck1">
                    Permission All
                  </label>
                </div>

                <hr />
                @foreach ($permission_groups as $group)
                  <div class="row">
                    <div class="col-3">
                      @php
                        $permissions = App\Models\User::getpermissionByGroupName($group->group_name);
                      @endphp

                      <div class="form-check mb-2">
                        <input
                          class="form-check-input permission-group"
                          type="checkbox"
                          value=""
                          @php
                            $hasAllPermissions = true;
                            foreach($permissions as $perm) {
                              if (!$employee->hasPermissionTo($perm->name)) {
                                $hasAllPermissions = false;
                                break;
                              }
                            }
                          @endphp
                          {{ $hasAllPermissions ? 'checked' : '' }}
                        />
                        <label class="form-check-label">
                          {{ $group->group_name }}
                        </label>
                      </div>
                    </div>

                    <div class="col-9">
                      @foreach ($permissions as $permission)
                        <div class="form-check mb-2">
                          <input
                            class="form-check-input"
                            name="permission[]"
                            value="{{ $permission->id }}"
                            type="checkbox"
                            id="flexCheckDefault{{ $permission->id }}"
                            {{ $employee->hasPermissionTo($permission->name) ? 'checked' : '' }}
                          />
                          <label
                            class="form-check-label"
                            for="flexCheckDefault{{ $permission->id }}"
                          >
                            {{ $permission->name }}
                          </label>
                        </div>
                      @endforeach

                      <br />
                    </div>
                  </div>
                  {{-- // End Row --}}
                @endforeach

                <div class="col-12">
                  <button class="btn btn-primary" type="submit">
                    Save Change
                  </button>
                  <a href="{{ route('all.employee') }}" class="btn btn-secondary">
                    Cancel
                  </a>
                </div>
              </form>
            </div>
            <!-- end card-body -->
          </div>
          <!-- end card-->
        </div>
        <!-- end col -->
      </div>
    </div>
    <!-- container-fluid -->
  </div>

  <script>
    // Permission All - chọn / bỏ chọn tất cả checkbox
    $('#formCheck1').on('click', function () {
      const checked = $(this).is(':checked');
      $('input[type=checkbox]').prop('checked', checked);
    });

    // Chọn / bỏ chọn theo từng group (Brand, Warehouse, ...)
    $(document).on('change', '.permission-group', function () {
      const checked = $(this).is(':checked');
      const row = $(this).closest('.row');
      // Tìm tất cả checkbox permission thuộc group này trong cột bên phải
      row.find('.col-9 input[type=checkbox]').prop('checked', checked);
    });
  </script>
@endsection
