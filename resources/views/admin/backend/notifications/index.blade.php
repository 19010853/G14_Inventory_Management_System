@extends('admin.admin_master')
@section('admin')

  <div class="content">
    <div class="container-xxl">
      <div class="py-3 d-flex align-items-sm-center flex-sm-row flex-column">
        <div class="flex-grow-1">
          <h4 class="fs-18 fw-semibold m-0">Notifications</h4>
        </div>
      </div>

      <div class="row">
        <div class="col-12">
          <div class="card">
            <div class="card-body">
              @if ($notifications->count() === 0)
                <p class="mb-0">You have no notifications.</p>
              @else
                <div class="list-group">
                  @foreach ($notifications as $notification)
                    <a
                      href="{{ $notification->data['link'] ?? '#' }}"
                      class="list-group-item list-group-item-action d-flex justify-content-between align-items-start"
                    >
                      <div class="ms-2 me-auto">
                        <div class="fw-semibold">
                          {{ $notification->data['title'] ?? 'Notification' }}
                        </div>
                        <div class="small text-muted">
                          {{ $notification->data['message'] ?? '' }}
                        </div>
                      </div>
                      <span class="text-muted small">
                        {{ $notification->created_at->diffForHumans() }}
                      </span>
                    </a>
                  @endforeach
                </div>

                <div class="mt-3">
                  {{ $notifications->links() }}
                </div>
              @endif
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

@endsection


