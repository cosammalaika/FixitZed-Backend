<div class="container mx-auto p-4">
    <h2 class="text-xl font-bold mb-4">Create Fixer</h2>

    @if(session()->has('success'))
        <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-4">
        <div>
            <label class="block font-semibold mb-1">Select User</label>
            <select wire:model="user_id" class="form-select w-full">
                <option value="">-- Choose User --</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->first_name }} {{ $user->last_name }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
            @error('user_id') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-semibold mb-1">Bio</label>
            <textarea wire:model="bio" class="form-textarea w-full" rows="3"></textarea>
            @error('bio') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <div>
            <label class="block font-semibold mb-1">Status</label>
            <select wire:model="status" class="form-select w-full">
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
            </select>
            @error('status') <span class="text-red-500">{{ $message }}</span> @enderror
        </div>

        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
            Create Fixer
        </button>
    </form>
</div>
