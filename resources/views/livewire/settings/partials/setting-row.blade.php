@php
    $key = $field['key'];
    $inputType = $field['type'] ?? 'text';
@endphp

<div class="settings-row">
    <div class="settings-meta">
        <h6>{{ $field['label'] }}</h6>
        @if(!empty($field['help']))
            <small class="text-muted">{{ $field['help'] }}</small>
        @endif
    </div>
    <div>
        <input
            type="{{ $inputType }}"
            class="form-control"
            wire:model.defer="values.{{ $key }}"
            @if(isset($field['min'])) min="{{ $field['min'] }}" @endif
            @if(isset($field['max'])) max="{{ $field['max'] }}" @endif
            @if(isset($field['step'])) step="{{ $field['step'] }}" @endif
            @if(!empty($field['placeholder'])) placeholder="{{ $field['placeholder'] }}" @endif
        >
        @error('values.' . $key) <small class="text-danger">{{ $message }}</small> @enderror
    </div>
    <div class="settings-actions">
        <button
            type="button"
            class="btn btn-sm btn-primary"
            wire:click="save('{{ $key }}')"
            wire:loading.attr="disabled"
            wire:target="save('{{ $key }}')"
        >
            <span wire:loading.remove wire:target="save('{{ $key }}')">Save</span>
            <span wire:loading wire:target="save('{{ $key }}')">Saving...</span>
        </button>
        @if(!empty($saved[$key]))
            <span class="saved-indicator">Saved</span>
        @endif
    </div>
</div>
