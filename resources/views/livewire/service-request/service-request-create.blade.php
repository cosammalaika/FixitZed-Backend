<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            <form wire:submit.prevent="submit">
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="customer_id">Customer</label>
                        <select wire:model="customer_id" class="form-control" required>
                            <option value="">-- Choose User --</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->first_name }}
                                    {{ $customer->last_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="service_id">Service</label>
                        <select id="service_id" wire:model="service_id" class="form-control">
                            <option value="">-- Select Service --</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label class="form-label" for="location_option_id">Location</label>
                        <select wire:model="location_option_id" id="location_option_id" class="form-control" data-trigger>
                            <option value="">-- Select Location --</option>
                            @foreach ($locationOptions as $opt)
                                <option value="{{ $opt->id }}">{{ $opt->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">Selecting a location will autofill the text field to the right.</small>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Location (text)</label>
                        <input wire:model.lazy="location" class="form-control" placeholder="Optional custom location"></input>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="fixer_id">Fixer</label>
                        <select id="fixer_id" wire:model="fixer_id" class="form-control" wire:key="fixer-select-{{ $service_id }}" required
                                wire:loading.attr="disabled" wire:target="service_id">
                            <option value="">-- Select Fixer --</option>
                            @foreach ($filteredFixers as $fixer)
                                @php($status = strtolower($fixer->status))
                                <option value="{{ $fixer->id }}" @if($status !== 'approved') disabled @endif>
                                    {{ $fixer->user->first_name }} {{ $fixer->user->last_name }}
                                    ({{ ucfirst($status) }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text" wire:loading wire:target="service_id">Loading fixers…</div>
                        @if (!$service_id)
                            <small class="text-muted">Select a service to see available fixers.</small>
                        @elseif ($filteredFixers->isEmpty())
                            <small class="text-muted">No fixers linked to this service yet. Attach this service to a fixer.</small>
                        @else
                            <small class="text-muted">Only Approved fixers can be selected. Pending fixers appear disabled.</small>
                        @endif


                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Scheduled At</label>
                        <input type="datetime-local" wire:model="scheduled_at" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="service_id">Status</label>
                        <select wire:model="status" class="form-control">
                            @foreach (['pending', 'accepted', 'completed', 'cancelled'] as $stat)
                                <option value="{{ $stat }}">{{ ucfirst($stat) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div><br>

                <button type="submit" class="btn btn-primary waves-effect waves-light">Create</button>
            </form>
            <script>
                (function () {
                    function attachHandlers() {
                        const serviceSel = document.getElementById('service_id');
                        const fixerSel = document.getElementById('fixer_id');
                        if (!serviceSel || !fixerSel) return;

                        if (!serviceSel.dataset.boundFixerLoader) {
                            serviceSel.addEventListener('change', function (e) {
                                loadFixers(e.target.value);
                            });
                            serviceSel.dataset.boundFixerLoader = '1';
                        }

                        // If there is a selected service on mount, populate once
                        if (serviceSel.value && !fixerSel.dataset.initialized) {
                            loadFixers(serviceSel.value).then(() => fixerSel.dataset.initialized = '1');
                        }
                    }

                    async function loadFixers(serviceId) {
                        const fixerSel = document.getElementById('fixer_id');
                        if (!fixerSel) return;
                        fixerSel.innerHTML = '<option value="">-- Select Fixer --</option>';
                        if (!serviceId) return;
                        try {
                            const resp = await fetch('/api/fixers?service_id=' + encodeURIComponent(serviceId));
                            const json = await resp.json();
                            const list = json && json.data ? (json.data.data || json.data) : [];
                            const frags = document.createDocumentFragment();
                            for (var i = 0; i < list.length; i++) {
                                var fixer = list[i];
                                var status = String(fixer.status || '').toLowerCase();
                                var opt = document.createElement('option');
                                opt.value = fixer.id;
                                opt.disabled = status !== 'approved';
                                var first = fixer.user && fixer.user.first_name ? fixer.user.first_name : '';
                                var last = fixer.user && fixer.user.last_name ? fixer.user.last_name : '';
                                opt.textContent = (first + ' ' + last + ' (' + (status.charAt(0).toUpperCase() + status.slice(1)) + ')').trim();
                                frags.appendChild(opt);
                            }
                            fixerSel.appendChild(frags);
                            fixerSel.value = '';
                            fixerSel.dispatchEvent(new Event('change', { bubbles: true }));
                        } catch (e) {
                            console.error('Failed to load fixers:', e);
                        }
                    }

                    document.addEventListener('DOMContentLoaded', attachHandlers);
                    document.addEventListener('livewire:load', function () {
                        // Re-attach after Livewire DOM patches
                        if (window.Livewire && Livewire.hook) {
                            Livewire.hook('message.processed', attachHandlers);
                        }
                    });
                })();
            </script>
        </div>
    </div>
</div>
