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

    private array $settingMap = [
        'currency_code' => ['key' => 'currency.code', 'type' => 'string'],
        'currency_symbol' => ['key' => 'currency.symbol', 'type' => 'string'],
        'currency_name' => ['key' => 'currency.name', 'type' => 'string'],

        'loyalty_point_value' => ['key' => 'loyalty.point_value', 'type' => 'float'],
        'loyalty_redeem_threshold_value' => ['key' => 'loyalty.redeem_threshold_value', 'type' => 'float'],

        'notifications_retention_days' => ['key' => 'notifications.retention_days', 'type' => 'int'],
        'notifications_per_page' => ['key' => 'notifications.per_page', 'type' => 'int'],

        'matching_default_radius_km' => ['key' => 'matching.default_radius_km', 'type' => 'float'],
        'matching_max_radius_km' => ['key' => 'matching.max_radius_km', 'type' => 'float'],
        'matching_radius_step_km' => ['key' => 'matching.radius_step_km', 'type' => 'float'],
        'matching_max_retries' => ['key' => 'matching.max_retries', 'type' => 'int'],

        'pagination_admin_default' => ['key' => 'pagination.admin_default', 'type' => 'int'],
        'pagination_api_default' => ['key' => 'pagination.api_default', 'type' => 'int'],

        'auth_password_reset_expiry' => ['key' => 'auth.password_reset_expiry', 'type' => 'int'],
        'auth_mfa_expiry' => ['key' => 'auth.mfa_expiry', 'type' => 'int'],
    ];

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

    public function saveField(string $property): void
    {
        if (! isset($this->settingMap[$property])) {
            return;
        }

        // Special validation dependency for matching radii
        if (in_array($property, ['matching_default_radius_km', 'matching_max_radius_km'], true)) {
            $this->validateOnly('matching_default_radius_km');
            $this->validateOnly('matching_max_radius_km');
            if ($this->matching_default_radius_km > $this->matching_max_radius_km) {
                $this->addError('matching_default_radius_km', 'Default radius cannot exceed max radius.');
                return;
            }
        }

        $this->validateOnly($property);

        $mapping = $this->settingMap[$property];
        $value = $this->{$property};

        switch ($mapping['type']) {
            case 'int':
                $value = (int) $value;
                break;
            case 'float':
                $value = (float) $value;
                break;
            default:
                $value = is_string($value) ? $value : (string) $value;
        }

        Setting::set($mapping['key'], $value);
        $this->toast('Setting saved.');
    }

    public function render()
    {
        return view('livewire.settings.general-settings');
    }
}
