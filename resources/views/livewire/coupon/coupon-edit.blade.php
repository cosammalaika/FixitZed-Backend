<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            @if (session()->has('success'))
                <div class="alert alert-success mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form wire:submit.prevent="update">
                <div class="row">
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
                </div>

                <div class="row">
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
