<div class="max-w-6xl mx-auto p-6 bg-white shadow-md rounded">
    <div class="flex justify-between items-center mb-4">
    <h2 class="text-2xl font-bold">Services</h2>
    <a href="{{ route('services.create') }}" class="bg-blue-600 text-black px-4 py-2 rounded hover:bg-blue-700">
        + Add Service
    </a>
</div>

    <table class="min-w-full bg-white border">
        <thead>
            <tr class="bg-gray-100 text-left">
                <th class="py-2 px-4 border-b">#</th>
                <th class="py-2 px-4 border-b">Name</th>
                <th class="py-2 px-4 border-b">Base Price (ZMW)</th>
                <th class="py-2 px-4 border-b">Status</th>
                <th class="py-2 px-4 border-b">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($services as $index => $service)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $index + 1 }}</td>
                    <td class="py-2 px-4 border-b">{{ $service->name }}</td>
                    <td class="py-2 px-4 border-b">ZMW {{ number_format($service->base_price, 2) }}</td>
                    <td class="py-2 px-4 border-b">
                        @if($service->is_active)
                            <span class="text-green-600 font-semibold">Active</span>
                        @else
                            <span class="text-red-600 font-semibold">Inactive</span>
                        @endif
                    </td>
                    <td class="py-2 px-4 border-b">
                        <a href="{{ route('services.show', $service->id) }}" class="text-blue-600 hover:underline mr-2">View</a>
                        <a href="{{ route('services.edit', $service->id) }}" class="text-yellow-600 hover:underline mr-2">Edit</a>
                        {{-- Future: Add delete button here --}}
        @csrf<button wire:click="delete({{ $service->id }})"
                                        wire:confirm="Are you Sure you want to delete user" variant="primary">
                                        Delete
                                    </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center py-4">No services found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
