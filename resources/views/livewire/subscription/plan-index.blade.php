@section('page-title', 'Subscription Plans')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Plans</h4>
          </div>
          <div class="card-body">
            @if (!empty($missing) && $missing)
              <div class="alert alert-warning">
                <strong>Subscriptions tables not found.</strong>
                Run migrations to create them:
                <code>php artisan migrate</code>
              </div>
            @endif
            <table class="table table-bordered dt-responsive nowrap w-100">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Name</th>
                  <th>Price (ZMW)</th>
                  <th>Coins</th>
                  <th>Valid Days</th>
                  <th>Active</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($plans as $i => $plan)
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ $plan->name }}</td>
                    <td>{{ number_format($plan->price_cents / 100, 2) }}</td>
                    <td>{{ $plan->coins }}</td>
                    <td>{{ $plan->valid_days ?? '—' }}</td>
                    <td>
                      <span class="badge bg-{{ $plan->is_active ? 'success' : 'secondary' }}">{{ $plan->is_active ? 'Active' : 'Inactive' }}</span>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
