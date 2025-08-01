<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="card-body">
            @if (session()->has('success'))
                <div class="bg-green-100 text-green-800 p-2 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

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
                        <select wire:model="service_id" class="form-control">
                            <option value="">-- Select Service --</option>
                            @foreach ($services as $service)
                                <option value="{{ $service->id }}">{{ $service->name }}</option>
                            @endforeach
                        </select>
                    </div>

                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label class="form-label" for="fixer_id">Fixer</label>
                        <select wire:model="fixer_id" class="form-control" wire:key="fixer-select-{{ $service_id }}">
                            <option value="">-- Select Fixer --</option>
                            @foreach ($filteredFixers as $fixer)
                                <option value="{{ $fixer->id }}">
                                    {{ $fixer->user->first_name }} {{ $fixer->user->last_name }}
                                </option>
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
                        <label class="form-label" for="service_id">Status</label>
                        <select wire:model="status" class="form-control">
                            @foreach (['pending', 'accepted', 'completed', 'cancelled'] as $stat)
                                <option value="{{ $stat }}">{{ ucfirst($stat) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Location</label>
                        <input wire:model="location" class="form-control"></input>
                    </div>
                </div><br>

                <button type="submit" class="btn btn-primary waves-effect waves-light">Create</button>
            </form>
        </div>
    </div>
</div>
