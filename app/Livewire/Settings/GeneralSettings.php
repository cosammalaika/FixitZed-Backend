<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\InteractsWithToast;
use App\Models\Setting;
use Livewire\Component;

class GeneralSettings extends Component
{
    use InteractsWithToast;

    public $currency_code = 'ZMW';
    public $currency_symbol = 'ZMW';
    public $currency_name = 'Zambian Kwacha';

    protected $rules = [
        'currency_code' => 'required|string|max:10',
        'currency_symbol' => 'required|string|max:10',
        'currency_name' => 'required|string|max:120',
    ];

    public function mount(): void
    {
        $this->currency_code = Setting::get('currency.code', $this->currency_code);
        $this->currency_symbol = Setting::get('currency.symbol', $this->currency_symbol);
        $this->currency_name = Setting::get('currency.name', $this->currency_name);
    }

    public function save(): void
    {
        $data = $this->validate();

        Setting::set('currency.code', strtoupper($data['currency_code']));
        Setting::set('currency.symbol', $data['currency_symbol']);
        Setting::set('currency.name', $data['currency_name']);

        $this->toast('Currency settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.settings.general-settings');
    }
}
