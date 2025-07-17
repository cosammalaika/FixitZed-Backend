<div class="max-w-xl mx-auto bg-white shadow-md rounded p-6">
    <h2 class="text-2xl font-bold mb-4">Service Details</h2>

    <div class="mb-4">
        <strong>Name:</strong>
        <p>{{ $service->name }}</p>
    </div>

    <div class="mb-4">
        <strong>Description:</strong>
        <p>{{ $service->description ?? 'No description' }}</p>
    </div>

    <div class="mb-4">
        <strong>Base Price:</strong>
        <p>ZMW {{ number_format($service->base_price, 2) }}</p>
    </div>

    <div class="mb-4">
        <strong>Status:</strong>
        <p>
            @if($service->is_active)
                <span class="text-green-600 font-semibold">Active</span>
            @else
                <span class="text-red-600 font-semibold">Inactive</span>
            @endif
        </p>
    </div>

    <a href="{{ route('services.edit', $service->id) }}" class="text-blue-500 underline">Edit Service</a>
</div>
