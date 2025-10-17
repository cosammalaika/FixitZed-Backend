<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="update">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" id="title" wire:model="title" class="form-control" placeholder="Today's Special Offer">
                        @error('title')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="code" class="form-label">Coupon Code</label>
                        <input type="text" id="code" wire:model="code" class="form-control">
                        @error('code')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="discount_percent" class="form-label">Discount (%)</label>
                        <input type="number" id="discount_percent" wire:model="discount_percent" class="form-control">
                        @error('discount_percent')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="discount_amount" class="form-label">Discount Amount</label>
                        <input type="number" id="discount_amount" wire:model="discount_amount" class="form-control" placeholder="e.g. 5000">
                        @error('discount_amount')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea id="description" wire:model="description" class="form-control" rows="3" placeholder="Get discount for every order, only valid for today"></textarea>
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="valid_from" class="form-label">Valid From</label>
                        <input type="date" id="valid_from" wire:model="valid_from" class="form-control">
                        @error('valid_from')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="valid_to" class="form-label">Valid To</label>
                        <input type="date" id="valid_to" wire:model="valid_to" class="form-control">
                        @error('valid_to')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <label for="usage_limit" class="form-label">Usage Limit</label>
                        <input type="number" id="usage_limit" wire:model="usage_limit" class="form-control">
                        @error('usage_limit')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <button class="btn btn-primary">Update Coupon</button>
            </form>
        </div>
    </div>
</div>
