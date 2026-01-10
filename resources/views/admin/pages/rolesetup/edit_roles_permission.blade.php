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
          <h4 class="fs-18 fw-semibold m-0">Edit Role In Permission</h4>
        </div>

        <div class="text-end">
          <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item active">Edit Role In Permission</li>
          </ol>
        </div>
      </div>

      <!-- Form Validation -->
      <div class="row">
        <div class="col-xl-12">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">Edit Role In Permission</h5>
            </div>
            <!-- end card header -->

            <div class="card-body">
              <form
                action="{{ route('admin.roles.update', $role->id) }}"
                method="post"
                class="row g-3"
                enctype="multipart/form-data"
              >
                @csrf

                <div class="col-md-6">
                  <label for="validationDefault01" class="form-label">
                    Role Name
                  </label>
                  <h4>{{ $role->name }}</h4>
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
                          {{ App\Models\User::roleHasPermissions($role, $permissions) ? 'checked' : '' }}
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
                            class="form-check-input permission-checkbox"
                            name="permission[]"
                            value="{{ $permission->id }}"
                            type="checkbox"
                            id="flexCheckDefault{{ $permission->id }}"
                            data-permission-name="{{ $permission->name }}"
                            {{ $role->hasPermissionTo($permission->name) ? 'checked' : '' }}
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
    // Permission mapping: all.* -> *.menu
    const permissionMapping = {
      'all.brand': 'brand.menu',
      'all.warehouse': 'warehouse.menu',
      'all.supplier': 'supplier.menu',
      'all.customer': 'customer.menu',
      'all.category': 'category.menu',
      'all.product': 'product.menu',
      'all.purchase': 'purchase.menu',
      'all.return.purchase': 'return.purchase.menu',
      'all.sale': 'sale.menu',
      'all.return.sale': 'return.sale.menu',
      'due.sales': 'due.menu',
      'due.sales.return': 'due.return.sale.menu',
      'all.transfer': 'transfer.menu'
    };

    // Reverse mapping: *.menu -> all.*
    const reverseMapping = {};
    for (const [allPerm, menuPerm] of Object.entries(permissionMapping)) {
      reverseMapping[menuPerm] = allPerm;
    }

    // Find checkbox by permission name
    function findCheckboxByPermissionName(permissionName) {
      let found = null;
      $('.permission-checkbox').each(function() {
        if ($(this).data('permission-name') === permissionName) {
          found = $(this);
          return false; // break loop
        }
      });
      return found;
    }

    // Flag to prevent infinite loops during sync
    let isSyncing = false;

    // Sync checkboxes: when all.* is checked, check *.menu
    $(document).on('change', '.permission-checkbox', function() {
      // Prevent recursive calls
      if (isSyncing) return;
      
      const checkbox = $(this);
      const permissionName = checkbox.data('permission-name');
      
      if (!permissionName) return;

      isSyncing = true;

      // If this is an all.* permission and it's checked
      if (permissionMapping[permissionName] && checkbox.is(':checked')) {
        const menuPermission = permissionMapping[permissionName];
        const menuCheckbox = findCheckboxByPermissionName(menuPermission);
        if (menuCheckbox && !menuCheckbox.is(':checked')) {
          menuCheckbox.prop('checked', true);
        }
      }

      // If this is a *.menu permission and it's unchecked
      if (reverseMapping[permissionName] && !checkbox.is(':checked')) {
        const allPermission = reverseMapping[permissionName];
        const allCheckbox = findCheckboxByPermissionName(allPermission);
        if (allCheckbox && allCheckbox.is(':checked')) {
          allCheckbox.prop('checked', false);
        }
      }

      isSyncing = false;
    });

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
