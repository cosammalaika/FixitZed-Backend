<div class="container mt-4">

    <form wire:submit.prevent="submit">
        {{-- Recipient Type --}}
        <div class="mb-3">
            <select wire:model="recipient_type" class="form-control" required>
                <option value="">-- Select Recipient Type --</option>
                <option value="Customer">All Customers</option>
                <option value="Fixer">All Fixers</option>
                <option value="Admin">All Admins</option>
                <option value="Support">All Support</option>
                <option value="Individual">Individual User</option>
            </select>

            @error('recipient_type')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        {{-- User Selection (only if recipient_type is Individual) --}}
        @if ($recipient_type === 'Individual')
            <div class="mb-3">
                <label for="userId" class="form-label">Select User</label>
                <select wire:model="user_id" id="userId" class="form-control">
                    <option value="">-- Select User --</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </select>
                @error('user_id')
                    <span class="text-danger">{{ $message }}</span>
                @enderror
            </div>
        @endif

        {{-- Title --}}
        <div class="mb-3">
            <label for="title" class="form-label">Title</label>
            <input type="text" wire:model="title" id="title" class="form-control"
                placeholder="Enter notification title">
            @error('title')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        {{-- Message --}}
        <div class="mb-3">
            <label for="message" class="form-label">Message</label>
            <textarea wire:model="message" id="message" class="form-control" rows="4"
                placeholder="Enter notification message"></textarea>
            @error('message')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>

        {{-- Submit Button --}}
        <button type="submit" class="btn btn-primary">Send Notification</button>
    </form>
</div>
