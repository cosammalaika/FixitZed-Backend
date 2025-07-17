<div class="max-w-lg mx-auto bg-white p-6 rounded shadow">
    @if (session()->has('success'))
        <div class="mb-4 text-green-600">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="update">
        <div class="mb-4">
            <label class="block mb-1">Name *</label>
            <input type="text" wire:model="name" class="w-full border rounded px-3 py-2">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="mb-4">
            <label class="block mb-1">Description</label>
            <textarea wire:model="description" class="w-full border rounded px-3 py-2"></textarea>
        </div>

        <div class="mb-4">
            <label class="block mb-1">Base Price (ZMW)</label>
            <input type="number" wire:model="base_price" step="0.01" class="w-full border rounded px-3 py-2">
        </div>
<div class="mb-4">
    <label class="flex items-center cursor-pointer">
        <!-- Switch -->
        <div class="relative">
            <input type="checkbox" wire:model="is_active" class="sr-only peer">
            <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-green-500 transition-colors duration-300"></div>
            <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full shadow-md transform peer-checked:translate-x-5 transition-transform duration-300"></div>
        </div>
        <!-- Label -->
        <span class="ml-3 text-gray-700 font-medium">
            {{ $is_active ? 'Active' : 'Inactive' }}
        </span>
    </label>
</div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Update Service</button>
    </form>
</div>
