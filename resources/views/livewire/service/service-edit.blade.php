<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="update">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="name">Service Name</label>
                        <input id="name" class="form-control" type="text" wire:model="name"
                            placeholder="Service Name" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="price">Base Price (ZMW)</label>
                        <input id="price" class="form-control" type="number" wire:model="price"
                            placeholder="e.g., 150" required>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label" for="duration_minutes">Duration (Minutes)</label>
                        <input id="duration_minutes" class="form-control" type="number" wire:model="duration_minutes"
                            placeholder="e.g., 20" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="subcategory_id">Subcategory</label>
                        <select id="subcategory_id" wire:model="subcategory_id" class="form-control" required>
                            <option value="">-- Select Subcategory --</option>
                            @foreach ($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                            @endforeach
                        </select>
                        @error('subcategory_id')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" wire:model="description" class="form-control" rows="4"
                            placeholder="Description text ..."></textarea>
                        @error('description')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6 d-flex align-items-center">
                        <div class="form-check form-switch mt-4">
                            <input type="checkbox" class="form-check-input" id="is_active" wire:model="is_active">
                            <label class="form-check-label" for="is_active">Active</label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Service</button>
            </form>
        </div>
    </div>
</div>
