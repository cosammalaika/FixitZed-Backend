<div class="dropdown d-inline-block">
    <button type="button" class="btn header-item noti-icon position-relative"
        id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
        aria-expanded="false">
        <i class="mdi mdi-bell-outline"></i>
        @if ($unreadCount > 0)
            <span class="badge bg-danger rounded-pill">{{ $unreadCount }}</span>
        @endif
    </button>

    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end p-0"
        aria-labelledby="page-header-notifications-dropdown">
        <div class="p-3">
            <div class="row align-items-center">
                <div class="col">
                    <h6 class="m-0"> Notifications </h6>
                </div>
                <div class="col-auto">
                    <a href="#" class="small text-reset text-decoration-underline">Unread ({{ $unreadCount }})</a>
                </div>
            </div>
        </div>

        <div data-simplebar style="max-height: 230px;">
            @forelse ($notifications as $notification)
                <a href="#" class="text-reset notification-item">
                    <div class="d-flex">
                        <div class="avatar-sm me-3">
                            <span class="avatar-title bg-primary rounded-circle font-size-16">
                                <i class="mdi mdi-bell-outline"></i>
                            </span>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $notification->title }}</h6>
                            <div class="font-size-13 text-muted">
                                <p class="mb-1">{{ Str::limit($notification->message, 50) }}</p>
                                <p class="mb-0"><i class="mdi mdi-clock-outline"></i> <span>{{ $notification->created_at->diffForHumans() }}</span></p>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <p class="text-center text-muted p-2">No notifications found.</p>
            @endforelse
        </div>

        <div class="p-2 border-top d-grid">
            <a class="btn btn-sm btn-link font-size-14 text-center" href="{{ route('notification.index') }}">
                <i class="mdi mdi-arrow-right-circle me-1"></i> <span>View More..</span>
            </a>
        </div>
    </div>
</div>
