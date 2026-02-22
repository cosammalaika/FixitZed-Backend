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
                        <input id="name" class="form-control" type="text" wire:model.defer="name"
                            placeholder="Service Name" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="subcategory_id">Subcategory</label>
                        <select id="subcategory_id" class="form-control" wire:model.defer="subcategory_id" required>
                            <option value="">Select subcategory</option>
                            @foreach ($subcategoryOptions as $option)
                                <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
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
                    <div class="col-md-6">
                        <label class="form-label" for="status">Status</label>
                        <select id="status" class="form-control" wire:model.defer="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Update Service</button>
            </form>
        </div>
    </div>
</div>
