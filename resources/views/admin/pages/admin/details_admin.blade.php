@extends('admin.admin_master')
@section('admin')
  <div class="content">
    <!-- Start Content-->
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column justify-content-between mb-4">
        <div class="flex-grow-1">
          <h4 class="fs-20 fw-semibold m-0 mb-2" style="color: #212529;">Admin Details</h4>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb m-0">
              <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
              <li class="breadcrumb-item"><a href="{{ route('all.admin') }}">All Admin</a></li>
              <li class="breadcrumb-item active">Admin Details</li>
            </ol>
          </nav>
        </div>

        <div class="text-end mt-3 mt-sm-0">
          <a href="{{ route('all.admin') }}" class="btn btn-secondary">
            <i data-feather="arrow-left" style="width: 16px; height: 16px; margin-right: 6px;"></i>
            Back to List
          </a>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card border-0 shadow-sm" style="border-radius: 12px;">
            <div class="card-body">
              <div class="align-items-center mb-4">
                <div class="d-flex align-items-center">
                  <img
                    src="{{ ! empty($admin->photo) ? url('upload/user_images/' . $admin->photo) : url('upload/no_image.jpg') }}"
                    class="rounded-circle"
                    alt="admin profile"
                    style="width: 80px; height: 80px; object-fit: cover; border: 3px solid #0d6efd;"
                  />

                  <div class="overflow-hidden ms-4">
                    <h4 class="m-0 text-dark fs-20 fw-semibold">
                      {{ $admin->name }}
                    </h4>
                    <p class="my-1 text-muted fs-16">
                      <i class="mdi mdi-email-outline me-1"></i>{{ $admin->email }}
                    </p>
                    <div class="mt-2">
                      @foreach ($admin->roles as $role)
                        <span class="badge" style="background: linear-gradient(135deg, #0d6efd 0%, #17a2b8 100%); padding: 6px 12px; border-radius: 6px;">
                          {{ $role->name ?? 'N/A' }}
                        </span>
                      @endforeach
                    </div>
                  </div>
                </div>
              </div>

              <div class="row">
                <div class="col-lg-12">
                  <div class="card border mb-0" style="border-radius: 12px;">
                    <div class="card-header" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-bottom: 1px solid #e9ecef; border-radius: 12px 12px 0 0;">
                      <div class="row align-items-center">
                        <div class="col">
                          <h4 class="card-title mb-0 fw-semibold" style="color: #212529;">
                            <i class="mdi mdi-account-circle-outline me-2"></i>Personal Information
                          </h4>
                        </div>
                      </div>
                    </div>

                    <div class="card-body">
                      <div class="row">
                        <div class="col-md-6 mb-3">
                          <label class="form-label fw-semibold" style="color: #495057;">Name</label>
                          <div class="form-control" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 15px; min-height: 42px;">
                            {{ $admin->name ?? 'N/A' }}
                          </div>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label class="form-label fw-semibold" style="color: #495057;">Email</label>
                          <div class="form-control" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 15px; min-height: 42px;">
                            {{ $admin->email ?? 'N/A' }}
                          </div>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label class="form-label fw-semibold" style="color: #495057;">Phone</label>
                          <div class="form-control" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 15px; min-height: 42px;">
                            {{ $admin->phone ?? 'N/A' }}
                          </div>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label class="form-label fw-semibold" style="color: #495057;">Address</label>
                          <div class="form-control" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 15px; min-height: 42px;">
                            {{ $admin->address ?? 'N/A' }}
                          </div>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label class="form-label fw-semibold" style="color: #495057;">Role</label>
                          <div class="form-control" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 15px; min-height: 42px;">
                            @foreach ($admin->roles as $role)
                              <span class="badge me-1" style="background: linear-gradient(135deg, #0d6efd 0%, #17a2b8 100%); padding: 4px 8px; border-radius: 4px;">
                                {{ $role->name }}
                              </span>
                            @endforeach
                            @if($admin->roles->isEmpty())
                              <span class="text-muted">No role assigned</span>
                            @endif
                          </div>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label class="form-label fw-semibold" style="color: #495057;">Account Created</label>
                          <div class="form-control" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 15px; min-height: 42px;">
                            {{ $admin->created_at ? \Carbon\Carbon::parse($admin->created_at)->format('F d, Y h:i A') : 'N/A' }}
                          </div>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label class="form-label fw-semibold" style="color: #495057;">Last Updated</label>
                          <div class="form-control" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 15px; min-height: 42px;">
                            {{ $admin->updated_at ? \Carbon\Carbon::parse($admin->updated_at)->format('F d, Y h:i A') : 'N/A' }}
                          </div>
                        </div>

                        <div class="col-md-6 mb-3">
                          <label class="form-label fw-semibold" style="color: #495057;">Email Verified</label>
                          <div class="form-control" style="background: #f8f9fa; border: 1px solid #e9ecef; border-radius: 8px; padding: 10px 15px; min-height: 42px;">
                            @if($admin->email_verified_at)
                              <span class="badge bg-success">Verified on {{ \Carbon\Carbon::parse($admin->email_verified_at)->format('F d, Y') }}</span>
                            @else
                              <span class="badge bg-warning">Not Verified</span>
                            @endif
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!-- container-fluid -->
  </div>
  <!-- content -->
@endsection

