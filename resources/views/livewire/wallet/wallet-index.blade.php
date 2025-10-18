@section('page-title', 'Wallets')

<div class="page-content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12">
        <div class="card">
          <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title mb-0">Fixer Wallets</h4>
          </div>
          <div class="card-body">
            @if (!empty($missing) && $missing)
              <div class="alert alert-warning">
                <strong>Wallets table not found.</strong>
                Run: <code>php artisan migrate</code>
              </div>
            @else
            <table class="table table-bordered dt-responsive nowrap w-100">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Fixer</th>
                  <th>Email</th>
                  <th>Coins</th>
                  <th>Subscription</th>
                  <th>Expires</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($wallets as $i => $w)
                  <tr>
                    <td>{{ $i + 1 }}</td>
                    <td>{{ optional($w->fixer->user)->first_name }} {{ optional($w->fixer->user)->last_name }}</td>
                    <td>{{ optional($w->fixer->user)->email }}</td>
                    <td>{{ $w->coin_balance }}</td>
                    <td>
                      <span class="badge rounded-pill {{ $w->subscription_status === 'approved' ? 'badge-soft-success' : 'badge-soft-warning' }}">{{ ucfirst($w->subscription_status) }}</span>
                    </td>
                    <td>{{ $w->last_subscription_expires_at ?? '—' }}</td>
                    <td>
                      <div class="dropdown">
                        <button class="btn btn-link font-size-16 shadow-none py-0 text-muted dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                          <i class="bx bx-dots-horizontal-rounded"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                          <li>
                            <a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#adjustWalletModal{{ $w->id }}">Adjust</a>
                          </li>
                        </ul>
                      </div>
                    </td>
                  </tr>

                  <div class="modal fade" id="adjustWalletModal{{ $w->id }}" tabindex="-1" aria-labelledby="adjustWalletModalLabel{{ $w->id }}" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title" id="adjustWalletModalLabel{{ $w->id }}">Adjust Wallet</h5>
                          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                          @livewire('wallet.wallet-adjust', ['fixerId' => $w->fixer_id], key('wallet-adjust-' . $w->id))
                        </div>
                      </div>
                    </div>
                  </div>
                @endforeach
              </tbody>
            </table>
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
