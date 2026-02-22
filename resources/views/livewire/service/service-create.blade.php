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

                <div class="row g-4">
                    <div class="col-md-6">
                        <label class="form-label" for="service_name">Service Name</label>
                        <input id="service_name" class="form-control" type="text" wire:model.defer="name" placeholder="Service Name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="service_category">Category</label>
                        <input id="service_category" class="form-control" type="text" wire:model.defer="category" placeholder="Enter a category label" required>
                        @error('category')
                            <span class="text-red-500 text-sm">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-md-6">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" wire:model.defer="description" class="form-control" placeholder="Description text ..."
                            rows="4"></textarea>
                        @error('description')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label" for="service_create_status">Status</label>
                        <select id="service_create_status" class="form-control" wire:model.defer="status" required>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary waves-effect waves-light">Save Service</button>
            </form>
        </div>
    </div>
</div>
