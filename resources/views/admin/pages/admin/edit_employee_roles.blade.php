@extends('admin.admin_master')
@section('admin')
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <style>
    .form-check-label {
      text-transform: capitalize;
    }
    .permission-readonly {
      opacity: 0.7;
      pointer-events: none;
    }
    .permission-readonly input[type="checkbox"] {
      cursor: not-allowed;
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
                id="employeeRoleForm"
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
                  <label for="roles" class="form-label">
                    Role <span class="text-danger">*</span>
                  </label>
                  <select name="roles" class="form-select" id="roleSelect" required>
                    <option value="">Select Role</option>
                    @foreach ($roles as $role)
                      <option value="{{ $role->id }}" 
                        data-role-name="{{ $role->name }}"
                        {{ $employee->hasRole($role->name) ? 'selected' : '' }}>
                        {{ $role->name }}
                      </option>
                    @endforeach
                  </select>
                  <small class="text-muted">Changing the role will automatically update permissions to match the selected role.</small>
                </div>

                <div class="col-12">
                  <hr>
                  <h5 class="mb-3">Permissions <small class="text-muted">(Read-only - Shows permissions of selected role)</small></h5>
                  <div class="alert alert-info">
                    <i class="mdi mdi-information-outline me-2"></i>
                    Permissions are automatically derived from the assigned role. You cannot edit permissions directly.
                  </div>
                </div>

                <div id="permissionsContainer" class="col-12 permission-readonly">
                  @php
                    $selectedRole = $currentRole ?? null;
                    $rolePermissions = $selectedRole ? $selectedRole->permissions->pluck('name')->toArray() : [];
                  @endphp
                  
                  @foreach ($permission_groups as $group)
                    @php
                      $permissions = App\Models\User::getpermissionByGroupName($group->group_name);
                      $groupHasPermission = false;
                      foreach($permissions as $perm) {
                        if (in_array($perm->name, $rolePermissions)) {
                          $groupHasPermission = true;
                          break;
                        }
                      }
                    @endphp
                    
                    @if($groupHasPermission || !$selectedRole)
                      <div class="row mb-3">
                        <div class="col-3">
                          <div class="form-check mb-2">
                            <input
                              class="form-check-input permission-group"
                              type="checkbox"
                              disabled
                              {{ $groupHasPermission ? 'checked' : '' }}
                            />
                            <label class="form-check-label fw-semibold">
                              {{ $group->group_name }}
                            </label>
                          </div>
                        </div>

                        <div class="col-9">
                          @foreach ($permissions as $permission)
                            <div class="form-check mb-2">
                              <input
                                class="form-check-input"
                                type="checkbox"
                                disabled
                                {{ in_array($permission->name, $rolePermissions) ? 'checked' : '' }}
                                id="perm_{{ $permission->id }}"
                              />
                              <label
                                class="form-check-label"
                                for="perm_{{ $permission->id }}"
                              >
                                {{ $permission->name }}
                              </label>
                            </div>
                          @endforeach
                          <br />
                        </div>
                      </div>
                    @endif
                  @endforeach
                  
                  @if(!$selectedRole)
                    <div class="alert alert-warning">
                      <i class="mdi mdi-alert-outline me-2"></i>
                      Please select a role to view its permissions.
                    </div>
                  @endif
                </div>

                <div class="col-12">
                  <button class="btn btn-primary" type="submit">
                    Save Changes
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
    // Role permissions data for JavaScript
    const rolePermissions = {
      @foreach($roles as $role)
        {{ $role->id }}: [
          @foreach($role->permissions as $perm)
            "{{ $perm->name }}"{{ !$loop->last ? ',' : '' }}
          @endforeach
        ]{{ !$loop->last ? ',' : '' }}
      @endforeach
    };

    // All permissions grouped by group name
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

    // Update permissions display when role changes
    $('#roleSelect').on('change', function() {
      const roleId = $(this).val();
      const container = $('#permissionsContainer');
      
      if (!roleId) {
        container.html('<div class="alert alert-warning"><i class="mdi mdi-alert-outline me-2"></i>Please select a role to view its permissions.</div>');
        return;
      }

      const permissions = rolePermissions[roleId] || [];
      let html = '';

      // Build permissions HTML
      for (const [groupName, groupPerms] of Object.entries(allPermissions)) {
        const groupHasPermission = groupPerms.some(p => permissions.includes(p.name));
        
        if (groupHasPermission) {
          html += '<div class="row mb-3">';
          html += '<div class="col-3">';
          html += '<div class="form-check mb-2">';
          html += '<input class="form-check-input permission-group" type="checkbox" disabled checked />';
          html += '<label class="form-check-label fw-semibold">' + groupName + '</label>';
          html += '</div></div>';
          html += '<div class="col-9">';
          
          groupPerms.forEach(perm => {
            const hasPermission = permissions.includes(perm.name);
            html += '<div class="form-check mb-2">';
            html += '<input class="form-check-input" type="checkbox" disabled ' + (hasPermission ? 'checked' : '') + ' id="perm_' + perm.id + '" />';
            html += '<label class="form-check-label" for="perm_' + perm.id + '">' + perm.name + '</label>';
            html += '</div>';
          });
          
          html += '<br /></div></div>';
        }
      }

      if (html === '') {
        html = '<div class="alert alert-info"><i class="mdi mdi-information-outline me-2"></i>This role has no permissions assigned.</div>';
      }

      container.html(html);
    });

    // Prevent form submission if no role is selected
    $('#employeeRoleForm').on('submit', function(e) {
      const roleId = $('#roleSelect').val();
      if (!roleId) {
        e.preventDefault();
        alert('Please select a role before saving.');
        return false;
      }
    });
  </script>
@endsection
