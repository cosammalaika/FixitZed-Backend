<form wire:submit.prevent="save">
  <div class="mb-3">
    <label class="form-label">Delta (coins)</label>
    <input type="number" wire:model="delta" class="form-control" placeholder="e.g. 5 or -1">
    @error('delta') <div class="text-danger small">{{ $message }}</div> @enderror
  </div>
  <div class="mb-3">
    <label class="form-label">Reason</label>
    <select wire:model="reason" class="form-control">
      <option value="admin_adjustment">Admin Adjustment</option>
      <option value="manual_credit">Manual Credit</option>
      <option value="manual_debit">Manual Debit</option>
    </select>
    @error('reason') <div class="text-danger small">{{ $message }}</div> @enderror
  </div>
  <div class="mb-3">
    <label class="form-label">Note</label>
    <textarea wire:model="note" class="form-control" rows="3" placeholder="Optional note"></textarea>
  </div>
  <div class="text-end">
    <button type="submit" class="btn btn-primary">Save</button>
  </div>
</form>

