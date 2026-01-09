@extends('admin.admin_master')
@section('admin')
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <div class="content">
    <!-- Start Content-->
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
          <h4 class="fs-18 fw-semibold m-0">Add Employee</h4>
        </div>

        <div class="text-end">
          <ol class="breadcrumb m-0 py-0">
            <li class="breadcrumb-item active">Add Employee</li>
          </ol>
        </div>
      </div>

      <!-- Form Validation -->
      <div class="row">
        <div class="col-xl-12">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0">Add Employee</h5>
            </div>
            <!-- end card header -->

            <div class="card-body">
              <form
                action="{{ route('store.employee') }}"
                method="post"
                class="row g-3"
                enctype="multipart/form-data"
              >
                @csrf

                <div class="col-md-6">
                  <label for="validationDefault01" class="form-label">
                    Employee Name
                  </label>
                  <input type="text" class="form-control" name="name" />
                </div>

                <div class="col-md-6">
                  <label for="validationDefault01" class="form-label">
                    Employee Email
                  </label>
                  <input type="email" class="form-control" name="email" required />
                  @error('email')
                    <small class="text-danger">{{ $message }}</small>
                  @enderror
                </div>

                <div class="col-md-6">
                  <label for="validationDefault01" class="form-label">
                    Role
                  </label>
                  <select name="roles" class="form-select" id="example-select">
                    <option value="" selected>Select Role</option>
                    @foreach ($roles as $role)
                      <option value="{{ $role->id }}">
                        {{ $role->name }}
                      </option>
                    @endforeach
                  </select>
                </div>

                <div class="col-12">
                  <div class="alert alert-info">
                    <i class="mdi mdi-information-outline me-2"></i>
                    A randomly generated password will be sent to the employee's email address.
                  </div>
                </div>

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
@endsection
