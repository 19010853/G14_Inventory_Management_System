<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Login - Inventory Management System | G14</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="Login to G14 Inventory Management System - Secure access to your inventory management dashboard"
    />
    <meta name="author" content="Group 14 - Hoang, Khoi, Van, Tuyen" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    {{-- Google Site Verification --}}
    <meta name="google-site-verification" content="xNGJHYuGpHJBuivESbON96TW4_RdbtDtBjbt3oiyyxI" />
    
    {{-- Security and Verification Meta Tags --}}
    <meta name="robots" content="noindex, nofollow" />
    <meta name="googlebot" content="noindex, nofollow" />
    <link rel="canonical" href="{{ url('/login') }}" />

    <!-- App favicon -->
    <link
      rel="shortcut icon"
      href="{{ asset('backend/assets/images/favicon.ico') }}"
    />

    <!-- App css -->
    <link
      href="{{ asset('backend/assets/css/app.min.css') }}"
      rel="stylesheet"
      type="text/css"
      id="app-style"
    />

    <!-- Icons -->
    <link
      href="{{ asset('backend/assets/css/icons.min.css') }}"
      rel="stylesheet"
      type="text/css"
    />

    <link
      rel="stylesheet"
      type="text/css"
      href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css"
    />
  </head>

  <body class="bg-white">
    <!-- Begin page -->
    <div class="account-page">
      <div class="container-fluid p-0">
        <div class="row align-items-center g-0">
          <div class="col-xl-5">
            <div class="row">
              <div class="col-md-7 mx-auto">
                <div class="mb-0 border-0 p-md-5 p-lg-0 p-4">
                  <div class="mb-4 p-0">
                    <a href="index.html" class="auth-logo">
                      <img
                        src="{{ asset('backend/assets/images/logo-dark.png') }}"
                        alt="logo-dark"
                        class="mx-auto"
                        height="28"
                      />
                    </a>
                  </div>

                  <div class="pt-0">
                    <form
                      method="POST"
                      action="{{ route('login') }}"
                      class="my-4"
                    >
                      @csrf

                      @if (session('error'))
                        <div class="alert alert-danger">
                          {{ session('error') }}
                        </div>
                      @endif

                      <div class="form-group mb-3">
                        <label for="emailaddress" class="form-label">
                          Email address
                        </label>
                        <input
                          class="form-control"
                          name="email"
                          type="email"
                          id="email"
                          required=""
                          placeholder="Enter your email"
                        />
                        @error('email')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>

                      <div class="form-group mb-3">
                        <label for="password" class="form-label">
                          Password
                        </label>
                        <input
                          class="form-control"
                          name="password"
                          type="password"
                          required=""
                          id="password"
                          placeholder="Enter your password"
                        />
                        @error('password')
                          <small class="text-danger">{{ $message }}</small>
                        @enderror
                      </div>

                      <div class="form-group d-flex mb-3">
                        <div class="col-sm-6 text-end">
                          <a
                            class="text-muted fs-14"
                            href="{{ route('password.request') }}"
                          >
                            Forgot password?
                          </a>
                        </div>
                      </div>

                      <div class="form-group mb-0 row">
                        <div class="col-12">
                          <div class="d-grid">
                            <button class="btn btn-primary" type="submit">
                              Log In
                            </button>
                          </div>
                        </div>
                      </div>
                    </form>

                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-xl-7">
            <div class="account-page-bg p-md-5 p-4">
              <div class="text-center">
                <h3 class="text-dark mb-3 pera-title">
                  Login Page for Inventory Managment System
                </h3>
                <div class="auth-image">
                  <img
                    src="{{ asset('backend/assets/images/authentication.svg') }}"
                    class="mx-auto img-fluid"
                    alt="images"
                  />
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- END wrapper -->

    <!-- Vendor -->
    <script src="{{ asset('backend/assets/libs/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/simplebar/simplebar.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/node-waves/waves.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/waypoints/lib/jquery.waypoints.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/jquery.counterup/jquery.counterup.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/feather-icons/feather.min.js') }}"></script>

    <!-- App js-->
    <script src="{{ asset('backend/assets/js/app.js') }}"></script>
    <script
      type="text/javascript"
      src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"
    ></script>

    @if (Session::has('message'))
      <!-- Thẻ meta để lưu trữ dữ liệu từ Laravel Session -->
      <meta
        id="laravel-toastr"
        data-message="{{ Session::get('message') }}"
        data-type="{{ Session::get('alert-type', 'info') }}"
      />
    @endif

    <script>
      // Đoạn script này là JavaScript thuần túy, không có Blade
      document.addEventListener('DOMContentLoaded', function () {
        const toastrElement = document.getElementById('laravel-toastr');

        if (toastrElement) {
          const message = toastrElement.getAttribute('data-message');
          const type = toastrElement.getAttribute('data-type');

          if (message && type) {
            toastr.options.closeButton = true; // Tùy chọn: thêm nút đóng
            toastr.options.progressBar = true; // Tùy chọn: thêm thanh tiến trình

            switch (type) {
              case 'info':
                toastr.info(message);
                break;
              case 'success':
                toastr.success(message);
                break;
              case 'warning':
                toastr.warning(message);
                break;
              case 'error':
                toastr.error(message);
                break;
            }
          }
        }
      });
    </script>
  </body>
</html>
