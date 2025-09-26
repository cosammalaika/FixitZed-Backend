<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <h4>Coupon Details</h4>
            <hr>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-4">
                        <strong>Title</strong>
                        <p> {{ $coupon->title }}</p>
                    </div>
                    <div class="mb-4">
                        <strong>Code</strong>
                        <p> {{ $coupon->code }}</p>
                    </div>
                    <div class="mb-4">
                        <strong>Discount</strong>
                        <p>{{ $coupon->discount_percent }}%</p>
                    </div>
                    <div class="mb-4">
                        <strong>Discount Amount</strong>
                        <p>{{ $coupon->discount_amount }}</p>
                    </div>
                    <div class="mb-4">
                        <strong>Valid From</strong>
                        <p>{{ $coupon->valid_from }}</p>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-4">
                        <strong>Description</strong>
                        <p>{{ $coupon->description }}</p>
                    </div>

                    <div class="mb-4">
                        <strong>Usage Limit</strong>
                        <p>{{ $coupon->usage_limit }}</p>
                    </div>
                    <div class="mb-4">
                        <strong>Usage Count</strong>
                        <p>{{ $coupon->used_count }}</p>
                    </div>
                    <div class="mb-4">
                        <strong>Valid To</strong>
                        <p>{{ $coupon->valid_to }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
