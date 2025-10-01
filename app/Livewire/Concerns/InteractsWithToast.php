<?php

namespace App\Livewire\Concerns;

trait InteractsWithToast
{
    protected function toast(string $message, string $type = 'success'): void
    {
        $this->dispatch('toast', type: $type, message: $message);
    }
}
