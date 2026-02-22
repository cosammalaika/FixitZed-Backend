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
                        <label class="form-label" for="category">Category</label>
                        <input id="category" class="form-control" type="text" wire:model.defer="category"
                            placeholder="Enter a category label" required>
                        @error('category')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" wire:model.defer="description" class="form-control" rows="4"
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
