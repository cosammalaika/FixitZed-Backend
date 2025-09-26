@section('page-title', 'Fixer Purchases')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Purchases</h4>
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
                  <th>Fixer</th>
                  <th>Plan</th>
                  <th>Coins</th>
                  <th>Status</th>
                  <th>Reference</th>
                  <th>Starts</th>
                  <th>Expires</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($purchases as $i => $s)
                  <tr>
                    <td>{{ $purchases->firstItem() + $i }}</td>
                    <td>{{ optional($s->fixer->user)->first_name }} {{ optional($s->fixer->user)->last_name }}</td>
                    <td>{{ optional($s->plan)->name }}</td>
                    <td>{{ $s->coins_awarded }}</td>
                    <td>
                      <span class="badge bg-{{ $s->status === 'approved' ? 'success' : ($s->status === 'pending' ? 'warning' : 'secondary') }}">{{ ucfirst($s->status) }}</span>
                    </td>
                    <td class="text-monospace">{{ $s->payment_reference }}</td>
                    <td>{{ $s->starts_at }}</td>
                    <td>{{ $s->expires_at ?? '—' }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
            @if (empty($missing) || !$missing)
              {{ $purchases->links() }}
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
