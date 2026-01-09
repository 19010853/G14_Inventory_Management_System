@extends('admin.admin_master')
@section('admin')
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>

  <div class="content">
    <!-- Start Content-->
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column justify-content-between mb-4">
        <div class="flex-grow-1">
          <h4 class="fs-20 fw-semibold m-0 mb-2" style="color: #212529;">Add Brand</h4>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
              <li class="breadcrumb-item"><a href="{{ route('all.brand') }}">All Brand</a></li>
              <li class="breadcrumb-item active">Add Brand</li>
            </ol>
          </nav>
        </div>
      </div>

      <!-- Form Validation -->
      <div class="row">
        <div class="col-xl-12">
          <div class="card">
            <div class="card-header">
              <h5 class="card-title mb-0 fw-semibold">
                <i data-feather="plus-circle" style="width: 18px; height: 18px; margin-right: 8px;"></i>
                Add New Brand
              </h5>
            </div>
            <!-- end card header -->

            <div class="card-body">
              <form
                action="{{ route('store.brand') }}"
                method="post"
                class="row g-3"
                enctype="multipart/form-data"
              >
                @csrf
                <div class="col-md-12">
                  <label for="validationDefault01" class="form-label">
                    Brand Name
                  </label>
                  <input type="text" class="form-control" name="name" />
                </div>
                <div class="col-md-6">
                  <label for="validationDefault02" class="form-label">
                    Brand Image
                  </label>
                  <input
                    type="file"
                    class="form-control"
                    name="image"
                    id="image"
                    accept="image/*"
                  />
                </div>

                <div class="col-md-6">
                  <label for="validationDefault02" class="form-label">Preview</label>
                  <div class="d-flex justify-content-start">
                    <img
                      id="showImage"
                      src="{{ url('upload/no_image.jpg') }}"
                      class="rounded img-thumbnail"
                      alt="image profile"
                      style="width: 150px; height: 150px; object-fit: cover; border: 2px solid #e9ecef;"
                    />
                  </div>
                </div>

                <div class="col-12 mt-4">
                  <button class="btn btn-primary" type="submit">
                    <i data-feather="save" style="width: 16px; height: 16px; margin-right: 6px;"></i>
                    Save Brand
                  </button>
                  <a href="{{ route('all.brand') }}" class="btn btn-secondary ms-2">
                    <i data-feather="x" style="width: 16px; height: 16px; margin-right: 6px;"></i>
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

  <script type="text/javascript">
    $(document).ready(function () {
      $('#image').change(function (e) {
        var reader = new FileReader();
        reader.onload = function (e) {
          $('#showImage').attr('src', e.target.result);
        };
        reader.readAsDataURL(e.target.files['0']);
      });
    });
  </script>
@endsection
