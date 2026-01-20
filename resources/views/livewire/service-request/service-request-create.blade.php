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
                        <label class="form-label" for="province">Province</label>
                        <select id="province" class="form-control" wire:model="province" required>
                            <option value="">-- Select Province --</option>
                            @foreach ($provinceOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        @error('province')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="district">District / Area</label>
                        <select id="district" class="form-control" wire:model="district" required>
                            <option value="">-- Select District --</option>
                            @foreach ($districtOptions as $option)
                                <option value="{{ $option }}">{{ $option }}</option>
                            @endforeach
                        </select>
                        @if (empty($districtOptions) && $province)
                            <small class="text-muted">No districts found for the selected province.</small>
                        @endif
                        @error('district')
                            <small class="text-danger d-block">{{ $message }}</small>
                        @enderror
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
                                    @php($fixerUser = optional($fixer->user))
                                    {{ $fixerUser->first_name ?? 'Deleted user' }} {{ $fixerUser->last_name ?? '' }}
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
            @once
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
                <script>
                    (function () {
                        document.addEventListener('livewire:load', () => {
                            const componentId = @json($this->id());
                            const provinceCache = @json($provinceMap);

                            const getDistricts = (province) => {
                                if (!province) return [];
                                const key = Object.keys(provinceCache).find(
                                    (k) => k.trim().toLowerCase() === province.trim().toLowerCase()
                                );
                                return key ? provinceCache[key] ?? [] : [];
                            };

                            const handleProvinceChange = () => {
                                const provinceSelect = document.getElementById('province');
                                const districtSelect = document.getElementById('district');
                                if (!provinceSelect || !districtSelect) return;

                                const province = provinceSelect.value;
                                const districts = getDistricts(province);
                                const component = Livewire.find(componentId);
                                if (!component) return;

                                component.set('districtOptions', districts);
                                if (!districts.includes(districtSelect.value)) {
                                    component.set('district', '');
                                }
                            };

                            const bindProvinceHandlers = () => {
                                const provinceSelect = document.getElementById('province');
                                if (!provinceSelect) return;
                                provinceSelect.removeEventListener('change', handleProvinceChange);
                                provinceSelect.addEventListener('change', handleProvinceChange);
                            };

                            if (window.Livewire && Livewire.hook) {
                                Livewire.hook('message.processed', (message, component) => {
                                    if (component.id === componentId) {
                                        bindProvinceHandlers();
                                    }
                                });
                            }

                            bindProvinceHandlers();
                            handleProvinceChange();
                        });
                    })();
            </script>
                <script>
                    (function () {
                        const provinceMap = @json($provinceMap);

                        const resolveDistricts = (province) => {
                            if (!province) {
                                return [];
                            }
                            const key = Object.keys(provinceMap).find(
                                (candidate) => candidate.trim().toLowerCase() === province.trim().toLowerCase()
                            );
                            return key ? provinceMap[key] ?? [] : [];
                        };

                        const renderDistricts = (provinceSelect, districtSelect) => {
                            const province = provinceSelect.value;
                            const districts = resolveDistricts(province);
                            const previous = districtSelect.value;

                            districtSelect.querySelectorAll('option[data-generated="true"]').forEach((option) => option.remove());

                            districts.forEach((name) => {
                                const option = document.createElement('option');
                                option.value = name;
                                option.textContent = name;
                                option.dataset.generated = 'true';
                                if (name === previous) {
                                    option.selected = true;
                                }
                                districtSelect.appendChild(option);
                            });

                            if (!districts.includes(previous)) {
                                districtSelect.value = '';
                                districtSelect.dispatchEvent(new Event('change'));
                            }
                        };

                        const enhance = () => {
                            const provinceSelect = document.getElementById('province');
                            const districtSelect = document.getElementById('district');
                            if (!provinceSelect || !districtSelect) return;

                            if (!districtSelect.dataset.enhanced) {
                                const placeholder = districtSelect.querySelector('option[value=\"\"]');
                                if (placeholder) {
                                    placeholder.dataset.generated = 'true';
                                }
                                districtSelect.dataset.enhanced = 'true';
                            }

                            renderDistricts(provinceSelect, districtSelect);

                            provinceSelect.removeEventListener('change', provinceSelect._districtHandler ?? (() => {}));
                            provinceSelect._districtHandler = () => renderDistricts(provinceSelect, districtSelect);
                            provinceSelect.addEventListener('change', provinceSelect._districtHandler);
                        };

                        document.addEventListener('DOMContentLoaded', enhance);
                        document.addEventListener('livewire:load', enhance);
                        document.addEventListener('livewire:update', enhance);
                    })();
                </script>
            @endonce
        </div>
    </div>
</div>
