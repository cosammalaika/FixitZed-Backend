<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">

            <form wire:submit.prevent="save">
                <div class="row">
                    <div class="col-md-6"><label>Title</label>
                        <input type="text" wire:model="title" class="form-control" placeholder="Today's Special Offer">
                        @error('title')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6"><label>Coupon Code</label>
                        <input type="text" wire:model="code" class="form-control">
                        @error('code')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6"> <label>Discount (%)</label>
                        <input type="number" wire:model="discount_percent" class="form-control">
                        @error('discount_percent')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6"> <label>Discount Amount</label>
                        <input type="number" wire:model="discount_amount" class="form-control" placeholder="e.g. 5000">
                        @error('discount_amount')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-12"> <label>Description</label>
                        <textarea wire:model="description" class="form-control" rows="3" placeholder="Get discount for every order, only valid for today"></textarea>
                        @error('description')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6"> <label>Valid From</label>
                        <input type="date" wire:model="valid_from" class="form-control">
                        @error('valid_from')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-6"> <label>Valid To</label>
                        <input type="date" wire:model="valid_to" class="form-control">
                        @error('valid_to')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6"><label>Usage Limit</label>
                        <input type="number" wire:model="usage_limit" class="form-control">
                        @error('usage_limit')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>
                </div><br>
                <button class="btn btn-primary">Create</button>
            </form>
        </div>
    </div>
</div>
