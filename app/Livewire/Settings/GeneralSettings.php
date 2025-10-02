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
    public $loyalty_point_value = 0.01; // K0.01 per point
    public $loyalty_redeem_threshold_value = 50; // K50 minimum to redeem

    protected $rules = [
        'currency_code' => 'required|string|max:10',
        'currency_symbol' => 'required|string|max:10',
        'currency_name' => 'required|string|max:120',
        'loyalty_point_value' => 'required|numeric|min:0.0001',
        'loyalty_redeem_threshold_value' => 'required|numeric|min:0',
    ];

    public function mount(): void
    {
        $this->currency_code = Setting::get('currency.code', $this->currency_code);
        $this->currency_symbol = Setting::get('currency.symbol', $this->currency_symbol);
        $this->currency_name = Setting::get('currency.name', $this->currency_name);
        $this->loyalty_point_value = (float) Setting::get('loyalty.point_value', $this->loyalty_point_value);
        $this->loyalty_redeem_threshold_value = (float) Setting::get('loyalty.redeem_threshold_value', $this->loyalty_redeem_threshold_value);
    }

    public function save(): void
    {
        $data = $this->validate();

        Setting::set('currency.code', strtoupper($data['currency_code']));
        Setting::set('currency.symbol', $data['currency_symbol']);
        Setting::set('currency.name', $data['currency_name']);
        Setting::set('loyalty.point_value', (float) $data['loyalty_point_value']);
        Setting::set('loyalty.redeem_threshold_value', (float) $data['loyalty_redeem_threshold_value']);

        $this->toast('Settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.settings.general-settings');
    }
}
