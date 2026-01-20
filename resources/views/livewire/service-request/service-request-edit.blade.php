<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">

            <form wire:submit.prevent="update">
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
                        <label class="form-label" for="fixer_id">Fixer</label>
                        <select wire:model="fixer_id" class="form-control">
                            <option value="">-- Select Fixer --</option>
                            @foreach ($fixers as $fixer)
                                @php($fixerUser = optional($fixer->user))
                                <option value="{{ $fixer->id }}">{{ $fixerUser->first_name ?? 'Deleted user' }}
                                    {{ $fixerUser->last_name ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="service_id">Service</label>
                        <select wire:model="service_id" class="form-control">
                            <option value="">-- Select Service --</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Scheduled At</label>
                        <input type="datetime-local" wire:model="scheduled_at" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="status">Status</label>
                        <select wire:model="status" class="form-control">
                            @foreach (['pending', 'accepted', 'completed', 'cancelled'] as $stat)
                                <option value="{{ $stat }}" @if ($stat === 'completed' && !$hasValidPayment) disabled style="color: #999;" @endif>
                                    {{ ucfirst($stat) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
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
                </div>

                <div class="row mt-3">
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
                </div><br>

                <button type="submit" class="btn btn-primary waves-effect waves-light">Update Request</button>
            </form>
        </div>
    </div>
                        @once
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
