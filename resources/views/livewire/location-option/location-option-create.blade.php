<form wire:submit.prevent="submit">
    <div class="row">
        <div class="col-md-6">
            <label class="form-label">Name</label>
            <input type="text" class="form-control" wire:model="name" required>
            @error('name') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Latitude</label>
            <input type="number" step="any" class="form-control" wire:model="latitude">
            @error('latitude') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label">Longitude</label>
            <input type="number" step="any" class="form-control" wire:model="longitude">
            @error('longitude') <span class="text-danger small">{{ $message }}</span> @enderror
        </div>
    </div>
    <div class="form-check form-switch mt-3">
        <input class="form-check-input" type="checkbox" id="activeSwitch" wire:model="is_active">
        <label class="form-check-label" for="activeSwitch">Active</label>
    </div>
    <div class="mt-3">
        <button type="submit" class="btn btn-primary">Save</button>
    </div>
</form>

