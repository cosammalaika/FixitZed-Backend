<div class="page-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-11">
                <div class="mb-4">
                    <h2 class="fw-semibold mb-1">General Settings</h2>
                    <p class="text-muted mb-0">Manage core platform defaults. Each section saves independently.</p>
                </div>

                {{-- Currency --}}
                <div class="card shadow-sm mb-4 settings-card">
                    <div class="card-header gradient-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 text-white">Currency</h5>
                            <small class="text-white-50">Defaults used across pricing and invoices.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveCurrency" class="row g-4 align-items-start">
                            <div class="col-md-4">
                                <label class="form-label">Currency code</label>
                                <input type="text" class="form-control" wire:model.defer="currency_code" placeholder="e.g., ZMW">
                                @error('currency_code') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Currency symbol</label>
                                <input type="text" class="form-control" wire:model.defer="currency_symbol" placeholder="e.g., K">
                                @error('currency_symbol') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Currency name</label>
                                <input type="text" class="form-control" wire:model.defer="currency_name" placeholder="e.g., Zambian Kwacha">
                                @error('currency_name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Loyalty --}}
                <div class="card shadow-sm mb-4 settings-card">
                    <div class="card-header gradient-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 text-white">Loyalty</h5>
                            <small class="text-white-50">Configure points value and redemption threshold.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveLoyalty" class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Point value (K per 1 point)</label>
                                <input type="number" step="0.0001" min="0.0001" class="form-control" wire:model.defer="loyalty_point_value" placeholder="e.g., 0.01">
                                @error('loyalty_point_value') <small class="text-danger">{{ $message }}</small> @enderror
                                <small class="text-muted">Example: 0.01 means 1pt = K0.01</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Redeem threshold (K)</label>
                                <input type="number" step="0.01" min="0" class="form-control" wire:model.defer="loyalty_redeem_threshold_value" placeholder="e.g., 50">
                                @error('loyalty_redeem_threshold_value') <small class="text-danger">{{ $message }}</small> @enderror
                                <small class="text-muted">Users can redeem points starting at this amount.</small>
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Notifications --}}
                <div class="card shadow-sm mb-4 settings-card">
                    <div class="card-header gradient-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 text-white">Notifications</h5>
                            <small class="text-white-50">Retention and list sizing.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveNotifications" class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Notification retention (days)</label>
                                <input type="number" min="1" max="365" class="form-control" wire:model.defer="notifications_retention_days" placeholder="e.g., 7">
                                @error('notifications_retention_days') <small class="text-danger">{{ $message }}</small> @enderror
                                <small class="text-muted">Older notifications are pruned daily.</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notifications per page</label>
                                <input type="number" min="1" max="100" class="form-control" wire:model.defer="notifications_per_page" placeholder="e.g., 20">
                                @error('notifications_per_page') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Search & Matching --}}
                <div class="card shadow-sm mb-4 settings-card">
                    <div class="card-header gradient-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 text-white">Search & Matching (Proximity)</h5>
                            <small class="text-white-50">Control discovery radius and retries.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveMatching" class="row g-4">
                            <div class="col-md-3">
                                <label class="form-label">Default search radius (km)</label>
                                <input type="number" min="1" max="1000" step="0.1" class="form-control" wire:model.defer="matching_default_radius_km" placeholder="e.g., 15">
                                @error('matching_default_radius_km') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Maximum search radius (km)</label>
                                <input type="number" min="1" max="1000" step="0.1" class="form-control" wire:model.defer="matching_max_radius_km" placeholder="e.g., 30">
                                @error('matching_max_radius_km') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Radius expansion step (km)</label>
                                <input type="number" min="0.1" max="1000" step="0.1" class="form-control" wire:model.defer="matching_radius_step_km" placeholder="e.g., 5">
                                @error('matching_radius_step_km') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Maximum search retries</label>
                                <input type="number" min="1" max="20" class="form-control" wire:model.defer="matching_max_retries" placeholder="e.g., 3">
                                @error('matching_max_retries') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Pagination --}}
                <div class="card shadow-sm mb-4 settings-card">
                    <div class="card-header gradient-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 text-white">Pagination</h5>
                            <small class="text-white-50">Defaults for admin and API lists.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="savePagination" class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Admin default per page</label>
                                <input type="number" min="1" max="500" class="form-control" wire:model.defer="pagination_admin_default" placeholder="e.g., 20">
                                @error('pagination_admin_default') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">API default per page</label>
                                <input type="number" min="1" max="500" class="form-control" wire:model.defer="pagination_api_default" placeholder="e.g., 20">
                                @error('pagination_api_default') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Save</button>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Auth & Security --}}
                <div class="card shadow-sm mb-5 settings-card">
                    <div class="card-header gradient-header d-flex align-items-center justify-content-between">
                        <div>
                            <h5 class="mb-0 text-white">Auth & Security</h5>
                            <small class="text-white-50">Expiry windows for password reset and MFA.</small>
                        </div>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="saveAuth" class="row g-4">
                            <div class="col-md-6">
                                <label class="form-label">Password reset expiry (minutes)</label>
                                <input type="number" min="1" max="1440" class="form-control" wire:model.defer="auth_password_reset_expiry" placeholder="e.g., 15">
                                @error('auth_password_reset_expiry') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">MFA challenge expiry (minutes)</label>
                                <input type="number" min="1" max="120" class="form-control" wire:model.defer="auth_mfa_expiry" placeholder="e.g., 5">
                                @error('auth_mfa_expiry') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary" wire:loading.attr="disabled">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .settings-card {
        border: none;
        border-radius: 16px;
    }
    .gradient-header {
        background: linear-gradient(135deg, #f46b45 0%, #ff8f70 100%);
        border-radius: 16px 16px 0 0;
        padding: 1rem 1.25rem;
    }
    .settings-card .card-body {
        padding: 1.5rem;
    }
    .form-label {
        font-weight: 600;
    }
    .form-control {
        height: 44px;
        border-radius: 10px;
    }
    .btn-primary {
        padding: 0.55rem 1.4rem;
        border-radius: 10px;
    }
    @media (max-width: 768px) {
        .settings-card .card-body {
            padding: 1.1rem;
        }
    }
</style>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('toast', ({type, message}) => {
            const toast = bootstrap.Toast.getOrCreateInstance(document.getElementById('app-toast'));
            const toastBody = document.querySelector('#app-toast .toast-body');
            const toastElement = document.getElementById('app-toast');
            if (toastBody && toastElement) {
                toastElement.classList.remove('text-bg-success', 'text-bg-danger', 'text-bg-info');
                toastElement.classList.add(type === 'success' ? 'text-bg-success' : 'text-bg-danger');
                toastBody.textContent = message;
                toast.show();
            } else {
                alert(message);
            }
        });
    });
</script>
