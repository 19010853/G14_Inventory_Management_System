<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta
      name="description"
      content="A fully featured admin theme which can be used to build CRM, CMS, etc."
    />
    <meta name="author" content="Zoyothemes" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    
    {{-- Google Site Verification --}}
    <meta name="google-site-verification" content="xNGJHYuGpHJBuivESbON96TW4_RdbtDtBjbt3oiyyxI" />

    <!-- App favicon -->
    <link
      rel="shortcut icon"
      href="{{ asset('backend/assets/images/favicon.svg') }}"
      type="image/svg+xml"
    />
    <link
      rel="icon"
      href="{{ asset('backend/assets/images/favicon.svg') }}"
      type="image/svg+xml"
    />

    <!-- Datatables css -->
    <link
      href="{{ asset('backend/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}"
      rel="stylesheet"
      type="text/css"
    />
    <link
      href="{{ asset('backend/assets/libs/datatables.net-buttons-bs5/css/buttons.bootstrap5.min.css') }}"
      rel="stylesheet"
      type="text/css"
    />
    <link
      href="{{ asset('backend/assets/libs/datatables.net-keytable-bs5/css/keyTable.bootstrap5.min.css') }}"
      rel="stylesheet"
      type="text/css"
    />
    <link
      href="{{ asset('backend/assets/libs/datatables.net-responsive-bs5/css/responsive.bootstrap5.min.css') }}"
      rel="stylesheet"
      type="text/css"
    />
    <link
      href="{{ asset('backend/assets/libs/datatables.net-select-bs5/css/select.bootstrap5.min.css') }}"
      rel="stylesheet"
      type="text/css"
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
    
    <!-- GHTK Style Custom CSS - Complete UI Redesign -->
    <style>
      :root {
        --ghtk-primary: #0d6efd;
        --ghtk-secondary: #17a2b8;
        --ghtk-success: #28a745;
        --ghtk-warning: #ffc107;
        --ghtk-danger: #dc3545;
        --ghtk-info: #17a2b8;
        --ghtk-dark: #212529;
        --ghtk-light: #f8f9fa;
      }
      
      /* Global Typography */
      body {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
        background: #f8f9fa;
      }
      
      h1, h2, h3, h4, h5, h6 {
        font-weight: 600;
        color: #212529;
      }
      
      /* Cards - GHTK Style */
      .card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        overflow: hidden;
      }
      
      .card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12) !important;
      }
      
      .card-header {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-bottom: 1px solid #e9ecef;
        padding: 1.25rem;
        font-weight: 600;
      }
      
      .card-body {
        padding: 1.5rem;
      }
      
      /* Buttons - GHTK Style */
      .btn {
        border-radius: 8px;
        padding: 10px 20px;
        font-weight: 500;
        transition: all 0.3s ease;
        border: none;
      }
      
      .btn-primary {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
        color: white;
      }
      
      .btn-primary:hover {
        background: linear-gradient(135deg, var(--ghtk-secondary) 0%, var(--ghtk-primary) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
        color: white;
      }
      
      .btn-secondary {
        background: #6c757d;
        color: white;
      }
      
      .btn-secondary:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
        color: white;
      }
      
      .btn-success {
        background: linear-gradient(135deg, var(--ghtk-success) 0%, #20c997 100%);
        color: white;
      }
      
      .btn-success:hover {
        background: linear-gradient(135deg, #20c997 0%, var(--ghtk-success) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
        color: white;
      }
      
      .btn-danger {
        background: linear-gradient(135deg, var(--ghtk-danger) 0%, #c82333 100%);
        color: white;
      }
      
      .btn-danger:hover {
        background: linear-gradient(135deg, #c82333 0%, var(--ghtk-danger) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
        color: white;
      }
      
      .btn-info {
        background: linear-gradient(135deg, var(--ghtk-info) 0%, var(--ghtk-primary) 100%);
        color: white;
      }
      
      .btn-info:hover {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-info) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
        color: white;
      }
      
      .btn-warning {
        background: linear-gradient(135deg, var(--ghtk-warning) 0%, #fd7e14 100%);
        color: #212529;
      }
      
      .btn-warning:hover {
        background: linear-gradient(135deg, #fd7e14 0%, var(--ghtk-warning) 100%);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
        color: #212529;
      }
      
      .btn-sm {
        padding: 6px 12px;
        font-size: 0.875rem;
      }
      
      .btn-outline-primary {
        border: 2px solid var(--ghtk-primary);
        color: var(--ghtk-primary);
        background: transparent;
      }
      
      .btn-outline-primary:hover {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
        color: white;
        border-color: transparent;
      }
      
      /* Topbar */
      .topbar-custom {
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-bottom: 1px solid #e9ecef;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      }
      
      /* Sidebar */
      .app-sidebar-menu {
        background: linear-gradient(180deg, #ffffff 0%, #f8f9fa 100%);
        border-right: 1px solid #e9ecef;
      }
      
      /* Content Page */
      .content-page {
        background: #f8f9fa;
        min-height: 100vh;
      }
      
      .content {
        padding: 1.5rem;
      }
      
      /* Tables - GHTK Style */
      .table {
        border-radius: 12px;
        overflow: hidden;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      }
      
      .table thead {
        background: #ffffff;
        color: #212529;
      }
      
      .table thead th {
        border: none;
        padding: 15px;
        font-weight: 600;
        text-transform: uppercase;
        font-size: 12px;
        letter-spacing: 0.5px;
        color: #212529;
      }
      
      .table tbody {
        background: white;
      }
      
      .table tbody tr {
        transition: all 0.2s ease;
        border-bottom: 1px solid #f0f0f0;
      }
      
      .table tbody tr:hover {
        background: linear-gradient(90deg, #f8f9fa 0%, #ffffff 100%);
        transform: scale(1.005);
      }
      
      .table tbody td {
        padding: 15px;
        vertical-align: middle;
      }
      
      /* Forms - GHTK Style */
      .form-control, .form-select {
        border-radius: 8px;
        border: 1px solid #dee2e6;
        padding: 10px 15px;
        transition: all 0.3s ease;
        font-size: 14px;
      }
      
      .form-control:focus, .form-select:focus {
        border-color: var(--ghtk-primary);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
        outline: none;
      }
      
      .form-label {
        font-weight: 500;
        color: #495057;
        margin-bottom: 8px;
      }
      
      .form-control.is-invalid, .form-select.is-invalid {
        border-color: var(--ghtk-danger);
      }
      
      .form-control.is-valid, .form-select.is-valid {
        border-color: var(--ghtk-success);
      }
      
      /* Badges - GHTK Style */
      .badge {
        border-radius: 6px;
        padding: 6px 12px;
        font-weight: 500;
        font-size: 0.75rem;
      }
      
      .badge.bg-primary {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%) !important;
      }
      
      .badge.bg-success {
        background: linear-gradient(135deg, var(--ghtk-success) 0%, #20c997 100%) !important;
      }
      
      .badge.bg-danger {
        background: linear-gradient(135deg, var(--ghtk-danger) 0%, #c82333 100%) !important;
      }
      
      .badge.bg-info {
        background: linear-gradient(135deg, var(--ghtk-info) 0%, var(--ghtk-primary) 100%) !important;
      }
      
      .badge.bg-warning {
        background: linear-gradient(135deg, var(--ghtk-warning) 0%, #fd7e14 100%) !important;
        color: #212529;
      }
      
      .badge.text-bg-secondary {
        background: #6c757d !important;
      }
      
      /* Dropdowns - GHTK Style */
      .dropdown-menu {
        border-radius: 12px;
        border: none;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        padding: 8px;
        margin-top: 8px;
      }
      
      .dropdown-item {
        border-radius: 8px;
        padding: 10px 15px;
        transition: all 0.2s ease;
        margin: 2px 0;
      }
      
      .dropdown-item:hover {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
        color: white;
      }
      
      .dropdown-item.active {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
        color: white;
      }
      
      /* Modals - GHTK Style */
      .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
        overflow: hidden;
      }
      
      .modal-header {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
        color: white;
        border: none;
        padding: 1.25rem 1.5rem;
      }
      
      .modal-header .modal-title {
        color: white;
        font-weight: 600;
      }
      
      .modal-header .btn-close {
        filter: invert(1);
        opacity: 0.8;
      }
      
      .modal-header .btn-close:hover {
        opacity: 1;
      }
      
      .modal-body {
        padding: 1.5rem;
      }
      
      .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
      }
      
      /* Alerts - GHTK Style */
      .alert {
        border-radius: 8px;
        border: none;
        padding: 1rem 1.25rem;
        font-weight: 500;
      }
      
      .alert-success {
        background: linear-gradient(135deg, rgba(40, 167, 69, 0.1) 0%, rgba(32, 201, 151, 0.1) 100%);
        color: var(--ghtk-success);
        border-left: 4px solid var(--ghtk-success);
      }
      
      .alert-danger {
        background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(200, 35, 51, 0.1) 100%);
        color: var(--ghtk-danger);
        border-left: 4px solid var(--ghtk-danger);
      }
      
      .alert-warning {
        background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(253, 126, 20, 0.1) 100%);
        color: #856404;
        border-left: 4px solid var(--ghtk-warning);
      }
      
      .alert-info {
        background: linear-gradient(135deg, rgba(23, 162, 184, 0.1) 0%, rgba(13, 110, 253, 0.1) 100%);
        color: var(--ghtk-info);
        border-left: 4px solid var(--ghtk-info);
      }
      
      /* Pagination - GHTK Style */
      .pagination {
        border-radius: 8px;
        display: inline-flex;
      }
      
      .page-link {
        border-radius: 6px;
        margin: 0 2px;
        border: 1px solid #dee2e6;
        color: var(--ghtk-primary);
        padding: 8px 12px;
        transition: all 0.2s ease;
      }
      
      .page-link:hover {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
        color: white;
        border-color: var(--ghtk-primary);
      }
      
      .page-item.active .page-link {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
        border-color: var(--ghtk-primary);
        color: white;
      }
      
      /* Breadcrumb - GHTK Style */
      .breadcrumb {
        background: transparent;
        padding: 0;
        margin: 0;
      }
      
      .breadcrumb-item a {
        color: var(--ghtk-primary);
        text-decoration: none;
        transition: all 0.2s ease;
      }
      
      .breadcrumb-item a:hover {
        color: var(--ghtk-secondary);
      }
      
      /* Input Groups - GHTK Style */
      .input-group-text {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border: 1px solid #dee2e6;
        border-radius: 8px 0 0 8px;
      }
      
      /* Navbar - GHTK Style */
      .navbar {
        border-radius: 12px;
        padding: 0.75rem 1rem;
      }
      
      .navbar-dark {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
      }
      
      .nav-link {
        border-radius: 8px;
        padding: 8px 16px;
        transition: all 0.2s ease;
        margin: 0 4px;
      }
      
      .nav-link:hover {
        background: rgba(255, 255, 255, 0.1);
      }
      
      .nav-link.active {
        background: rgba(255, 255, 255, 0.2);
        font-weight: 600;
      }
      
      /* List Group - GHTK Style */
      .list-group-item {
        border-radius: 8px;
        margin-bottom: 4px;
        border: 1px solid #e9ecef;
        transition: all 0.2s ease;
      }
      
      .list-group-item:hover {
        background: #f8f9fa;
        transform: translateX(4px);
      }
      
      /* Progress Bars - GHTK Style */
      .progress {
        border-radius: 10px;
        height: 10px;
        background: #e9ecef;
      }
      
      .progress-bar {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
        border-radius: 10px;
      }
      
      /* Text Colors */
      .text-primary {
        color: var(--ghtk-primary) !important;
      }
      
      .text-success {
        color: var(--ghtk-success) !important;
      }
      
      .text-danger {
        color: var(--ghtk-danger) !important;
      }
      
      .text-info {
        color: var(--ghtk-info) !important;
      }
      
      .text-warning {
        color: #856404 !important;
      }
      
      /* Container */
      .container-xxl {
        padding: 0 1.5rem;
      }
      
      /* Page Headers */
      .py-3 h4 {
        color: #212529;
        font-weight: 600;
      }
      
      /* Custom Scrollbar */
      ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
      }
      
      ::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 10px;
      }
      
      ::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
        border-radius: 10px;
      }
      
      ::-webkit-scrollbar-thumb:hover {
        background: linear-gradient(135deg, var(--ghtk-secondary) 0%, var(--ghtk-primary) 100%);
      }
      
      /* File Input */
      input[type="file"] {
        border-radius: 8px;
        padding: 8px;
      }
      
      /* Textarea */
      textarea.form-control {
        border-radius: 8px;
        min-height: 100px;
      }
      
      /* Select Multiple */
      select[multiple].form-select {
        border-radius: 8px;
      }
      
      /* Checkbox & Radio */
      .form-check-input:checked {
        background-color: var(--ghtk-primary);
        border-color: var(--ghtk-primary);
      }
      
      .form-check-input:focus {
        border-color: var(--ghtk-primary);
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
      }
      
      /* Invalid Feedback */
      .invalid-feedback {
        color: var(--ghtk-danger);
        font-size: 0.875rem;
        margin-top: 4px;
      }
      
      /* Valid Feedback */
      .valid-feedback {
        color: var(--ghtk-success);
        font-size: 0.875rem;
        margin-top: 4px;
      }
      
      /* Card Title */
      .card-title {
        font-weight: 600;
        color: #212529;
        margin-bottom: 1rem;
      }
      
      /* Table Responsive */
      .table-responsive {
        border-radius: 12px;
      }
      
      /* Loading Spinner */
      .spinner-border {
        border-color: var(--ghtk-primary);
        border-right-color: transparent;
      }
      
      /* Toastr Customization */
      #toast-container > div {
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
      }
      
      /* Print Styles */
      @media print {
        .card {
          box-shadow: none;
          border: 1px solid #dee2e6;
        }
        
        .btn {
          display: none;
        }
      }
    </style>
  </head>

  <!-- body start -->
  <body data-menu-color="light" data-sidebar="default">
    <!-- Begin page -->
    <div id="app-layout">
      <!-- Topbar Start -->
      @include('admin.body.header')
      <!-- end Topbar -->

      <!-- Left Sidebar Start -->
      @include('admin.body.sidebar')
      <!-- Left Sidebar End -->

      <!-- ============================================================== -->
      <!-- Start Page Content here -->
      <!-- ============================================================== -->

      <div class="content-page">
        @yield('admin')

        <!-- content -->

        <!-- Footer Start -->
        @include('admin.body.footer')
        <!-- end Footer -->
      </div>
      <!-- ============================================================== -->
      <!-- End Page content -->
      <!-- ============================================================== -->
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

    <!-- Apexcharts JS -->
    <script src="{{ asset('backend/assets/libs/apexcharts/apexcharts.min.js') }}"></script>

    <!-- for basic area chart -->
    <script src="https://apexcharts.com/samples/assets/stock-prices.js"></script>

    <!-- Widgets Init Js -->
    <!-- Disabled to prevent hardcoded charts from interfering with dynamic charts -->
    <!-- <script src="{{ asset('backend/assets/js/pages/analytics-dashboard.init.js') }}"></script> -->

    <script src="{{ asset('backend/assets/js/validate.min.js') }}"></script>
    <script src="{{ asset('backend/assets/js/custome.js') }}"></script>
    <!-- App js-->
    <script src="{{ asset('backend/assets/js/app.js') }}"></script>

    <!-- Datatables js -->
    <script src="{{ asset('backend/assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>

    <!-- dataTables.bootstrap5 -->
    <script src="{{ asset('backend/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="{{ asset('backend/assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js') }}"></script>

    <!-- Datatable Demo App Js -->
    <script src="{{ asset('backend/assets/js/pages/datatable.init.js') }}"></script>

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

    <!-- Right bar overlay-->
    <div class="rightbar-overlay"></div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="deleteConfirmModalLabel">
              <i class="mdi mdi-alert-circle text-danger me-2"></i>
              Confirm Delete
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p class="mb-0">Are you sure you want to delete this data?</p>
            <p class="text-muted small mt-2 mb-0">This action cannot be undone.</p>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
              <i class="mdi mdi-close me-1"></i> Cancel
            </button>
            <a href="#" id="confirmDeleteBtn" class="btn btn-danger">
              <i class="mdi mdi-delete me-1"></i> Delete
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Delete Confirmation Script -->
    <script src="{{ asset('backend/assets/js/code.js') }}"></script>

    @vite('resources/js/app.js')
    <script type="module">
    window.Echo.private('App.Models.User.{{ auth()->id() }}')
        .notification((notification) => {
            // 1. Hiển thị thông báo dạng popup (Toastr)
            toastr.success(notification.message, notification.title);

            // 2. Cập nhật số lượng trên icon chuông thông báo (nếu có)
            let count = parseInt($('#notification-count').text());
            $('#notification-count').text(count + 1);

            // 3. Chèn thêm dòng thông báo mới vào danh sách dropdown mà không cần F5
            let newHtml = `
                <a href="${notification.link}" class="text-reset notification-item">
                    <div class="d-flex">
                        <div class="flex-1">
                            <h6 class="mb-1">${notification.title}</h6>
                            <div class="font-size-12 text-muted">
                                <p class="mb-1">${notification.message}</p>
                            </div>
                        </div>
                    </div>
                </a>`;
            $('#notification-list').prepend(newHtml);
        });
</script>


  </body>
</html>
