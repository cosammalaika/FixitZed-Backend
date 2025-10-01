<div class="page-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-xl-6 col-lg-8">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h4 class="card-title mb-0">General Settings</h4>
                    </div>
                    <div class="card-body">
                        <form wire:submit.prevent="save" class="row g-4">
                            <div class="col-12">
                                <label class="form-label">Currency code</label>
                                <input type="text" class="form-control" wire:model.defer="currency_code"
                                    placeholder="e.g., ZMW">
                                @error('currency_code') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Currency symbol</label>
                                <input type="text" class="form-control" wire:model.defer="currency_symbol"
                                    placeholder="e.g., ZMW or K">
                                @error('currency_symbol') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Currency name</label>
                                <input type="text" class="form-control" wire:model.defer="currency_name"
                                    placeholder="e.g., Zambian Kwacha">
                                @error('currency_name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary">Save changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
</div>
