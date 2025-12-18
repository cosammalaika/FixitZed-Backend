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

    public $loyalty_point_value = 0.01;
    public $loyalty_redeem_threshold_value = 50;

    public $notifications_retention_days = 7;
    public $notifications_per_page = 20;

    public $matching_default_radius_km = 15;
    public $matching_max_radius_km = 30;
    public $matching_radius_step_km = 5;
    public $matching_max_retries = 3;

    public $pagination_admin_default = 20;
    public $pagination_api_default = 20;

    public $auth_password_reset_expiry = 15;
    public $auth_mfa_expiry = 5;

    protected $rules = [
        'currency_code' => 'required|string|max:10',
        'currency_symbol' => 'required|string|max:10',
        'currency_name' => 'required|string|max:120',

        'loyalty_point_value' => 'required|numeric|min:0.0001',
        'loyalty_redeem_threshold_value' => 'required|numeric|min:0',

        'notifications_retention_days' => 'required|integer|min:1|max:365',
        'notifications_per_page' => 'required|integer|min:1|max:100',

        'matching_default_radius_km' => 'required|numeric|min:1|max:1000',
        'matching_max_radius_km' => 'required|numeric|min:1|max:1000',
        'matching_radius_step_km' => 'required|numeric|min:0.1|max:1000',
        'matching_max_retries' => 'required|integer|min:1|max:20',

        'pagination_admin_default' => 'required|integer|min:1|max:500',
        'pagination_api_default' => 'required|integer|min:1|max:500',

        'auth_password_reset_expiry' => 'required|integer|min:1|max:1440',
        'auth_mfa_expiry' => 'required|integer|min:1|max:120',
    ];

    public function mount(): void
    {
        $this->currency_code = Setting::get('currency.code', $this->currency_code);
        $this->currency_symbol = Setting::get('currency.symbol', $this->currency_symbol);
        $this->currency_name = Setting::get('currency.name', $this->currency_name);
        $this->loyalty_point_value = (float) Setting::get('loyalty.point_value', $this->loyalty_point_value);
        $this->loyalty_redeem_threshold_value = (float) Setting::get('loyalty.redeem_threshold_value', $this->loyalty_redeem_threshold_value);

        $this->notifications_retention_days = (int) Setting::get('notifications.retention_days', $this->notifications_retention_days);
        $this->notifications_per_page = (int) Setting::get('notifications.per_page', $this->notifications_per_page);

        $this->matching_default_radius_km = (float) Setting::get('matching.default_radius_km', $this->matching_default_radius_km);
        $this->matching_max_radius_km = (float) Setting::get('matching.max_radius_km', $this->matching_max_radius_km);
        $this->matching_radius_step_km = (float) Setting::get('matching.radius_step_km', $this->matching_radius_step_km);
        $this->matching_max_retries = (int) Setting::get('matching.max_retries', $this->matching_max_retries);

        $this->pagination_admin_default = (int) Setting::get('pagination.admin_default', $this->pagination_admin_default);
        $this->pagination_api_default = (int) Setting::get('pagination.api_default', $this->pagination_api_default);

        $this->auth_password_reset_expiry = (int) Setting::get('auth.password_reset_expiry', $this->auth_password_reset_expiry);
        $this->auth_mfa_expiry = (int) Setting::get('auth.mfa_expiry', $this->auth_mfa_expiry);
    }

    public function saveCurrency(): void
    {
        $data = $this->validateOnly([
            'currency_code', 'currency_symbol', 'currency_name',
        ]);

        Setting::set('currency.code', strtoupper($data['currency_code']));
        Setting::set('currency.symbol', $data['currency_symbol']);
        Setting::set('currency.name', $data['currency_name']);

        $this->toast('Currency settings updated.');
    }

    public function saveLoyalty(): void
    {
        $data = $this->validateOnly([
            'loyalty_point_value', 'loyalty_redeem_threshold_value',
        ]);

        Setting::set('loyalty.point_value', (float) $data['loyalty_point_value']);
        Setting::set('loyalty.redeem_threshold_value', (float) $data['loyalty_redeem_threshold_value']);

        $this->toast('Loyalty settings updated.');
    }

    public function saveNotifications(): void
    {
        $data = $this->validateOnly([
            'notifications_retention_days', 'notifications_per_page',
        ]);

        Setting::set('notifications.retention_days', (int) $data['notifications_retention_days']);
        Setting::set('notifications.per_page', (int) $data['notifications_per_page']);

        $this->toast('Notification settings updated.');
    }

    public function saveMatching(): void
    {
        $data = $this->validateOnly([
            'matching_default_radius_km',
            'matching_max_radius_km',
            'matching_radius_step_km',
            'matching_max_retries',
        ]);

        if ($data['matching_default_radius_km'] > $data['matching_max_radius_km']) {
            $this->addError('matching_default_radius_km', 'Default radius cannot exceed max radius.');
            return;
        }

        Setting::set('matching.default_radius_km', (float) $data['matching_default_radius_km']);
        Setting::set('matching.max_radius_km', (float) $data['matching_max_radius_km']);
        Setting::set('matching.radius_step_km', (float) $data['matching_radius_step_km']);
        Setting::set('matching.max_retries', (int) $data['matching_max_retries']);

        $this->toast('Search & Matching settings updated.');
    }

    public function savePagination(): void
    {
        $data = $this->validateOnly([
            'pagination_admin_default', 'pagination_api_default',
        ]);

        Setting::set('pagination.admin_default', (int) $data['pagination_admin_default']);
        Setting::set('pagination.api_default', (int) $data['pagination_api_default']);

        $this->toast('Pagination settings updated.');
    }

    public function saveAuth(): void
    {
        $data = $this->validateOnly([
            'auth_password_reset_expiry', 'auth_mfa_expiry',
        ]);

        Setting::set('auth.password_reset_expiry', (int) $data['auth_password_reset_expiry']);
        Setting::set('auth.mfa_expiry', (int) $data['auth_mfa_expiry']);

        $this->toast('Auth & Security settings updated.');
    }

    public function render()
    {
        return view('livewire.settings.general-settings');
    }
}
