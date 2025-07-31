<div class="container mt-4">
    <h4>Notification Details</h4>
    <hr>
    <div class="mb-3">
        <strong>Title:</strong>
        <p>{{ $notification->title }}</p>
    </div>

    <div class="mb-3">
        <strong>Message:</strong>
        <p>{{ $notification->message }}</p>
    </div>

    <div class="mb-3">
        <strong>Status:</strong>
        <p>{{ $notification->read ? 'Read' : 'Unread' }}</p>
    </div>

    <div class="mb-3">
        <strong>Created At:</strong>
        <p>{{ $notification->created_at->format('d M Y, h:i A') }}</p>
    </div>

</div>
