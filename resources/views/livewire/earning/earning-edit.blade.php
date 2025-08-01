<div>
    <h4 class="mb-4">Create Earning</h4>

    <form wire:submit.prevent="update">
        <div class="mb-3">
            <label for="fixer_id">Fixer</label>
            <select wire:model="fixer_id" class="form-control" id="fixer_id">
                <option value="">-- Select Fixer --</option>
                @foreach($fixers as $fixer)
                    <option value="{{ $fixer->id }}">{{ $fixer->user->first_name }} {{ $fixer->user->last_name }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="service_count">Service Request</label>
            <select wire:model="service_count" class="form-control" id="service_count">
                <option value="">-- Select Request --</option>
                @foreach($requests as $req)
                    <option value="{{ $req->id }}">#{{ $req->id }} - {{ $req->description }}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="amount">Amount</label>
            <input wire:model="amount" type="number" step="0.01" class="form-control" id="amount">
        </div>

        <button class="btn btn-primary">Update</button>
    </form>
</div>
