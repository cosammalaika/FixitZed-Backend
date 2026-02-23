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
                        @error('name')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="service_subcategory">Subcategory</label>
                        <select id="service_subcategory" class="form-control" wire:model="subcategory_id" required>
                            <option value="">Select subcategory</option>
                            @foreach (($subcategories ?? collect()) as $subcategory)
                                <option value="{{ $subcategory->id }}">
                                    {{ $subcategory->name }}
                                    @if (optional($subcategory->category)->name)
                                        ({{ $subcategory->category->name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                        @error('subcategory_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="row g-4 mt-1">
                    <div class="col-md-6">
                        <label class="form-label" for="service_category_display">Category Label (auto)</label>
                        <input id="service_category_display" class="form-control" type="text" wire:model.defer="category" placeholder="Select a subcategory" readonly>
                        @error('category')
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

                <div class="row g-4 mt-1">
                    <div class="col-md-12">
                        <label class="form-label" for="description">Description</label>
                        <textarea id="description" wire:model.defer="description" class="form-control" placeholder="Description text ..."
                            rows="4"></textarea>
                        @error('description')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary waves-effect waves-light">Save Service</button>
            </form>
        </div>
    </div>
</div>
