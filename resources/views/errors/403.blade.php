<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8" />
        <title>403 - Access Forbidden</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc."/>
        <meta name="author" content="Zoyothemes"/>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />

        <!-- App favicon -->
        <link rel="shortcut icon" href="{{ asset('backend/assets/images/favicon.ico')}}">

        <!-- App css -->
        <link href="{{ asset('backend/assets/css/app.min.css')}}" rel="stylesheet" type="text/css" id="app-style" />

        <!-- Icons -->
        <link href="{{ asset('backend/assets/css/icons.min.css')}}" rel="stylesheet" type="text/css" />

        <!-- GHTK Style Custom CSS -->
        <style>
            :root {
                --ghtk-primary: #0d6efd;
                --ghtk-secondary: #17a2b8;
                --ghtk-danger: #dc3545;
                --ghtk-dark: #212529;
            }

            body {
                font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
                background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .error-container {
                text-align: center;
                padding: 3rem;
                max-width: 600px;
            }

            .error-logo {
                margin-bottom: 2rem;
            }

            .error-icon {
                width: 120px;
                height: 120px;
                margin: 0 auto 2rem;
                background: linear-gradient(135deg, var(--ghtk-danger) 0%, #c82333 100%);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 8px 24px rgba(220, 53, 69, 0.3);
            }

            .error-icon i {
                font-size: 60px;
                color: white;
            }

            .error-title {
                font-size: 2rem;
                font-weight: 600;
                color: var(--ghtk-dark);
                margin-bottom: 1rem;
            }

            .error-message {
                color: #6c757d;
                font-size: 1.1rem;
                margin-bottom: 2rem;
                line-height: 1.6;
            }

            .btn-primary {
                background: linear-gradient(135deg, var(--ghtk-primary) 0%, var(--ghtk-secondary) 100%);
                border: none;
                border-radius: 8px;
                padding: 12px 30px;
                font-weight: 500;
                transition: all 0.3s ease;
                box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
            }

            .btn-primary:hover {
                background: linear-gradient(135deg, var(--ghtk-secondary) 0%, var(--ghtk-primary) 100%);
                transform: translateY(-2px);
                box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
            }
        </style>
    </head>

    <body>
        <div class="error-container">
            <div class="error-logo">
                <a href="{{ route('dashboard') }}">
                    <img src="{{ asset('backend/assets/images/logo-dark.svg')}}" alt="G14 Inventory" height="32" />
                </a>
            </div>

            <div class="error-icon">
                <i class="mdi mdi-lock-alert"></i>
            </div>

            <h1 class="error-title">403 - Access Forbidden</h1>
            <p class="error-message">
                You don't have permission to access this page.<br>
                Please contact your administrator if you believe this is an error.
            </p>

            <a class="btn btn-primary" href="{{ route('dashboard') }}">
                <i class="mdi mdi-home me-2"></i>Back to Dashboard
            </a>
        </div>

        <!-- Vendor -->
        <script src="{{ asset('backend/assets/libs/jquery/jquery.min.js')}}"></script>
        <script src="{{ asset('backend/assets/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>
        <script src="{{ asset('backend/assets/libs/feather-icons/feather.min.js')}}"></script>
        <script>
            feather.replace();
        </script>
    </body>
</html>
