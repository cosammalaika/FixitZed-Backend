<div class="container mt-4">
    
    <form wire:submit.prevent="submit">
        <div class="mb-3">
            <label>Title</label>
            <input type="text" wire:model="title" class="form-control">
            @error('title') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="mb-3">
            <label>Message</label>
            <textarea wire:model="message" class="form-control" rows="4"></textarea>
            @error('message') <span class="text-danger">{{ $message }}</span> @enderror
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" wire:model="read" class="form-check-input" id="readStatus">
            <label class="form-check-label" for="readStatus">Mark as Read</label>
        </div>

        <button type="submit" class="btn btn-primary">Update</button>
        <a href="{{ route('notification.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </form>
</div>
