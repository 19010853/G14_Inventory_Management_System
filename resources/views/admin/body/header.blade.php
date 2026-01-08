<div class="topbar-custom">
  <div class="container-xxl">
    <div class="d-flex justify-content-between align-items-center py-2">
      <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
        <li>
          <button class="button-toggle-menu nav-link ps-0" style="border-radius: 8px; padding: 8px 12px; transition: all 0.3s ease;">
            <i data-feather="menu" class="noti-icon"></i>
          </button>
        </li>
        <li class="d-none d-lg-block ms-3">
          <div class="position-relative topbar-search">
            <input
              type="text"
              class="form-control bg-white border border-light ps-4"
              placeholder="Search..."
              style="border-radius: 25px; padding: 10px 20px 10px 45px; width: 300px;"
            />
            <i
              class="mdi mdi-magnify fs-16 position-absolute text-muted top-50 translate-middle-y ms-3"
            ></i>
          </div>
        </li>
      </ul>

      <ul class="list-unstyled topnav-menu mb-0 d-flex align-items-center">
        <li class="d-none d-sm-flex">
          <button type="button" class="btn nav-link" data-toggle="fullscreen">
            <i
              data-feather="maximize"
              class="align-middle fullscreen noti-icon"
            ></i>
          </button>
        </li>

        <li class="dropdown notification-list topbar-dropdown">
        <a
            class="nav-link dropdown-toggle"
            data-bs-toggle="dropdown"
            href="#"
            role="button"
            aria-haspopup="false"
            aria-expanded="false"
            style="border-radius: 8px; padding: 8px 12px; transition: all 0.3s ease;"
          >
          <i data-feather="bell" class="noti-icon"></i>
            @php
                $unreadCount = auth()->user()->unreadNotifications()->count();
                $notifications = auth()->user()->unreadNotifications;
            @endphp
            @if($unreadCount > 0)
              <span class="badge bg-danger rounded-circle noti-icon-badge" style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%) !important;">
                {{ $unreadCount }}
              </span>
            @endif
          </a>
          <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0" style="border-radius: 12px; min-width: 350px;">
            <div class="p-3" style="background: linear-gradient(135deg, #0d6efd 0%, #17a2b8 100%); border-radius: 12px 12px 0 0;">
                <div class="row align-items-center">
                    <div class="col">
                        <h6 class="m-0 text-white fw-semibold"> Notifications ({{ $notifications->count() }}) </h6>
                    </div>
                </div>
            </div>
            <div data-simplebar style="max-height: 230px;" id="notification-list">
                @foreach($notifications as $notification)
                <a href="{{ route('notifications.redirect', $notification->id) }}" class="text-reset notification-item">
                    <div class="d-flex">
                        <div class="avatar-xs me-3">
                            <span class="avatar-title bg-primary rounded-circle font-size-16">
                                <i class="ri-shopping-cart-line"></i>
                            </span>
                        </div>
                        <div class="flex-1">
                            <h6 class="mb-1">{{ $notification->data['title'] }}</h6>
                            <div class="font-size-12 text-muted">
                                <p class="mb-1">{{ $notification->data['message'] }}</p>
                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> {{ $notification->created_at->diffForHumans() }}</p>
                            </div>
                        </div>
                    </div>
                </a>
                @endforeach
            </div>
            <div class="p-2 border-top">
                <a class="btn btn-sm btn-link font-size-14 w-100 text-center" href="{{ route('notifications.index') }}" style="border-radius: 8px; color: #0d6efd; font-weight: 500;">
                    <i class="mdi mdi-arrow-right-circle me-1"></i> View all...
                </a>
            </div>
        </div>
        </li>

        @php
          $id = Auth::user()->id;
          $profileData = App\Models\User::find($id);
        @endphp

        <li class="dropdown notification-list topbar-dropdown">
          <a
            class="nav-link dropdown-toggle nav-user me-0"
            data-bs-toggle="dropdown"
            href="#"
            role="button"
            aria-haspopup="false"
            aria-expanded="false"
            style="border-radius: 25px; padding: 5px 15px; transition: all 0.3s ease;"
          >
            <img
              src="{{ ! empty($profileData->photo) ? url('upload/user_images/' . $profileData->photo) : url('upload/no_image.jpg') }}"
              alt="user-image"
              class="rounded-circle"
              style="width: 32px; height: 32px; object-fit: cover; border: 2px solid #0d6efd;"
            />
            <span class="pro-user-name ms-2 fw-medium">
              {{ $profileData->name }}
              <i class="mdi mdi-chevron-down ms-1"></i>
            </span>
          </a>
          <div class="dropdown-menu dropdown-menu-end profile-dropdown" style="border-radius: 12px; min-width: 220px;">
            <!-- item-->
            <div class="dropdown-header noti-title" style="background: linear-gradient(135deg, #0d6efd 0%, #17a2b8 100%); color: white; border-radius: 12px 12px 0 0;">
              <h6 class="text-overflow m-0 fw-semibold">Welcome, {{ $profileData->name }}!</h6>
            </div>

            <!-- item-->
            <a
              href="{{ route('admin.profile') }}"
              class="dropdown-item notify-item"
            >
              <i class="mdi mdi-account-circle-outline fs-16 align-middle me-2"></i>
              <span>My Account</span>
            </a>

            <!-- item-->
            <a href="auth-lock-screen.html" class="dropdown-item notify-item">
              <i class="mdi mdi-lock-outline fs-16 align-middle me-2"></i>
              <span>Lock Screen</span>
            </a>

            <div class="dropdown-divider"></div>

            <!-- item-->
            <a
              href="{{ route('admin.logout') }}"
              class="dropdown-item notify-item text-danger"
            >
              <i class="mdi mdi-location-exit fs-16 align-middle me-2"></i>
              <span>Logout</span>
            </a>
          </div>
        </li>
      </ul>
    </div>
  </div>
</div>
