<div class="container mt-4">
    <div class="card p-4">
        <form wire:submit.prevent="update">
            <div class="mb-3">
                <label>Category</label>
                <select wire:model="category_id" class="form-control">
                    <option value="">Select Category</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label>Name</label>
                <input type="text" wire:model="name" class="form-control">
                @error('name') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <div class="mb-3">
                <label>Description</label>
                <textarea wire:model="description" class="form-control"></textarea>
                @error('description') <span class="text-danger">{{ $message }}</span> @enderror
            </div>

            <button class="btn btn-primary">Update</button>
        </form>
    </div>
</div>
