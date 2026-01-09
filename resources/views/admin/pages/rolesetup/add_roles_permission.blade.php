@extends('admin.admin_master')
@section('admin')
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <style>
    .form-check-label {
      text-transform: capitalize;
    }
    .loading {
      opacity: 0.6;
      pointer-events: none;
    }
  </style>

  <div class="content">
    <!-- Start Content-->
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
          <h4 class="fs-18 fw-semibold m-0">Role In Permission</h4>
        </div>

        <div class="text-end">
          <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item active">Role In Permission</li>
          </ol>
        </div>
      </div>

      <!-- Form Validation -->
      <div class="row">
        <div class="col-xl-12">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">Role In Permission</h5>
            </div>
            <!-- end card header -->

            <div class="card-body">
              <form
                action="{{ route('store.role.permission') }}"
                method="post"
                class="row g-3"
                enctype="multipart/form-data"
                id="rolePermissionForm"
              >
                @csrf

                <div class="col-md-6">
                  <label for="roleSelect" class="form-label">
                    Role Name <span class="text-danger">*</span>
                  </label>
                  <select
                    name="role_id"
                    class="form-select"
                    id="roleSelect"
                    required
                  >
                    <option value="" selected>Select Role</option>
                    @foreach ($roles as $role)
                      <option value="{{ $role->id }}" data-role-name="{{ $role->name }}">
                        {{ $role->name }}
                      </option>
                    @endforeach
                  </select>
                  <small class="text-muted">Select a role to view and modify its permissions</small>
                </div>

                <div class="col-12" id="permissionsSection" style="display: none;">
                  <hr>
                  <div class="alert alert-info">
                    <i class="mdi mdi-information-outline me-2"></i>
                    <span id="roleInfo">Permissions for selected role will appear here</span>
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

                  <div id="permissionsContainer">
                    <!-- Permissions will be loaded here via JavaScript -->
                  </div>
                </div>

                <div class="col-12" id="submitSection" style="display: none;">
                  <button class="btn btn-primary" type="submit">
                    Save Changes
                  </button>
                  <button type="button" class="btn btn-secondary" onclick="resetForm()">
                    Reset
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
    // All permissions grouped by group name (for JavaScript)
    const allPermissions = {
      @foreach($permission_groups as $group)
        "{{ $group->group_name }}": [
          @php
            $permissions = App\Models\User::getpermissionByGroupName($group->group_name);
          @endphp
          @foreach($permissions as $perm)
            {
              id: {{ $perm->id }},
              name: "{{ $perm->name }}"
            }{{ !$loop->last ? ',' : '' }}
          @endforeach
        ]{{ !$loop->last ? ',' : '' }}
      @endforeach
    };

    let currentRolePermissions = [];

    // Load permissions when role is selected
    $('#roleSelect').on('change', function() {
      const roleId = $(this).val();
      const roleName = $(this).find('option:selected').data('role-name');
      
      if (!roleId) {
        $('#permissionsSection').hide();
        $('#submitSection').hide();
        return;
      }

      // Check if Super Admin
      if (roleName === 'Super Admin') {
        $('#permissionsSection').show();
        $('#submitSection').hide();
        $('#roleInfo').html('<strong>Super Admin</strong> role permissions cannot be modified. This is a protected system role.');
        $('#permissionsContainer').html('<div class="alert alert-warning"><i class="mdi mdi-alert-outline me-2"></i>Super Admin role has all permissions and cannot be edited.</div>');
        return;
      }

      // Show loading state
      $('#permissionsSection').show();
      $('#submitSection').show();
      $('#permissionsContainer').addClass('loading').html('<div class="text-center p-4"><i class="mdi mdi-loading mdi-spin mdi-24px"></i> Loading permissions...</div>');

      // Fetch role permissions via AJAX
      $.ajax({
        url: '{{ route("api.role.permissions", ":id") }}'.replace(':id', roleId),
        method: 'GET',
        success: function(response) {
          if (response.success) {
            currentRolePermissions = response.permissions;
            renderPermissions(currentRolePermissions);
            $('#roleInfo').html('Current permissions for <strong>' + roleName + '</strong>. You can add or remove permissions.');
          } else {
            $('#permissionsContainer').html('<div class="alert alert-danger">' + (response.error || 'Failed to load permissions') + '</div>');
          }
          $('#permissionsContainer').removeClass('loading');
        },
        error: function(xhr) {
          let errorMsg = 'Failed to load permissions';
          if (xhr.responseJSON && xhr.responseJSON.error) {
            errorMsg = xhr.responseJSON.error;
          }
          $('#permissionsContainer').html('<div class="alert alert-danger">' + errorMsg + '</div>');
          $('#permissionsContainer').removeClass('loading');
        }
      });
    });

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
      'all.transfer': 'transfer.menu',
      'reports.all': 'report.menu'
    };

    // Reverse mapping: *.menu -> all.*
    const reverseMapping = {};
    for (const [allPerm, menuPerm] of Object.entries(permissionMapping)) {
      reverseMapping[menuPerm] = allPerm;
    }

    // Create a map of permission name to permission object for quick lookup
    function createPermissionNameMap() {
      const nameMap = {};
      for (const [groupName, groupPerms] of Object.entries(allPermissions)) {
        groupPerms.forEach(perm => {
          nameMap[perm.name] = perm;
        });
      }
      return nameMap;
    }

    // Render permissions checkboxes
    function renderPermissions(selectedPermissionIds) {
      let html = '';
      const permissionNameMap = createPermissionNameMap();
      
      for (const [groupName, groupPerms] of Object.entries(allPermissions)) {
        const groupHasPermission = groupPerms.some(p => selectedPermissionIds.includes(p.id));
        
        html += '<div class="row mb-3">';
        html += '<div class="col-3">';
        html += '<div class="form-check mb-2">';
        html += '<input class="form-check-input permission-group" type="checkbox" ' + (groupHasPermission ? 'checked' : '') + ' />';
        html += '<label class="form-check-label fw-semibold">' + groupName + '</label>';
        html += '</div></div>';
        html += '<div class="col-9">';
        
        groupPerms.forEach(perm => {
          const hasPermission = selectedPermissionIds.includes(perm.id);
          html += '<div class="form-check mb-2">';
          html += '<input class="form-check-input permission-checkbox" name="permission[]" value="' + perm.id + '" type="checkbox" id="perm_' + perm.id + '" data-permission-name="' + perm.name + '" ' + (hasPermission ? 'checked' : '') + ' />';
          html += '<label class="form-check-label" for="perm_' + perm.id + '">' + perm.name + '</label>';
          html += '</div>';
        });
        
        html += '<br /></div></div>';
      }

      $('#permissionsContainer').html(html);
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
        const menuPermObj = createPermissionNameMap()[menuPermission];
        if (menuPermObj) {
          const menuCheckbox = $('#perm_' + menuPermObj.id);
          if (menuCheckbox.length && !menuCheckbox.is(':checked')) {
            menuCheckbox.prop('checked', true);
          }
        }
      }

      // If this is a *.menu permission and it's unchecked
      if (reverseMapping[permissionName] && !checkbox.is(':checked')) {
        const allPermission = reverseMapping[permissionName];
        const allPermObj = createPermissionNameMap()[allPermission];
        if (allPermObj) {
          const allCheckbox = $('#perm_' + allPermObj.id);
          if (allCheckbox.length && allCheckbox.is(':checked')) {
            allCheckbox.prop('checked', false);
          }
        }
      }

      isSyncing = false;
    });

    // Permission All - select/deselect all checkboxes
    $(document).on('click', '#formCheck1', function () {
      const checked = $(this).is(':checked');
      $('#permissionsContainer input[type=checkbox]').prop('checked', checked);
    });

    // Select/deselect by group
    $(document).on('change', '.permission-group', function () {
      const checked = $(this).is(':checked');
      const row = $(this).closest('.row');
      row.find('.col-9 input[type=checkbox]').prop('checked', checked);
    });

    // Reset form
    function resetForm() {
      $('#roleSelect').val('').trigger('change');
      $('#permissionsSection').hide();
      $('#submitSection').hide();
    }

    // Form validation
    $('#rolePermissionForm').on('submit', function(e) {
      const roleId = $('#roleSelect').val();
      if (!roleId) {
        e.preventDefault();
        alert('Please select a role before saving.');
        return false;
      }
    });
  </script>
@endsection
