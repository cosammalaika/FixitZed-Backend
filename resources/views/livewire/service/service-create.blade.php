<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="submit">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Service Name</label>
                        <input class="form-control" type="text" wire:model="name" placeholder="Service Name"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Base Price (ZMW)</label>
                        <input class="form-control" type="text" wire:model="price" placeholder="e.g, ZMK" required>
                    </div>
                </div>
                <div class="row mt-6">
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Duration (Minutes)</label>
                        <input class="form-control" type="text" wire:model="duration_minutes" placeholder="e.g, 20"
                            required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="default-input">Subcategory</label>
                        <select wire:model="subcategory_id" class="form-control" required>
                            <option value="">-- Select Subcategory --</option>
                            @foreach ($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}">{{ $subcategory->name }}</option>
                            @endforeach
                        </select>
                        @error('subcategory_id')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>

                </div>
                <div class="row mt-4">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" wire:model="description" class="form-control" placeholder="Description text ..."
                            rows="4"></textarea>
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

                <button type="submit" class="btn btn-primary waves-effect waves-light">Save Service</button>

            </form>
        </div>
    </div>

</div>
