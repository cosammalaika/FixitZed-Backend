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
        $sections = collect($this->fields)
            ->groupBy('section')
            ->map(function ($group) {
                return $group->groupBy(function ($field) {
                    return $field['subsection'] ?? 'default';
                });
            })
            ->toArray();

        return view('livewire.settings.general-settings', [
            'sections' => $sections,
        ]);
    }

    private function fieldConfig(): array
    {
        return [
            [
                'key' => 'currency.code',
                'label' => 'Currency code',
                'help' => 'ISO currency code used across the platform.',
                'type' => 'text',
                'default' => 'ZMW',
                'section' => 'Currency',
            ],
            [
                'key' => 'currency.symbol',
                'label' => 'Currency symbol',
                'help' => 'Shown in invoices and receipts.',
                'type' => 'text',
                'default' => 'ZMW',
                'section' => 'Currency',
            ],
            [
                'key' => 'currency.name',
                'label' => 'Currency name',
                'help' => 'Displayed in admin reports and exports.',
                'type' => 'text',
                'default' => 'Zambian Kwacha',
                'section' => 'Currency',
            ],
            [
                'key' => 'loyalty.point_value',
                'label' => 'Point value (K per 1 point)',
                'help' => 'Example: 0.01 means 1pt = K0.01.',
                'type' => 'number',
                'step' => '0.0001',
                'min' => 0.0001,
                'default' => 0.01,
                'section' => 'Loyalty',
            ],
            [
                'key' => 'loyalty.redeem_threshold_value',
                'label' => 'Redeem threshold (K)',
                'help' => 'Minimum amount before points can be redeemed.',
                'type' => 'number',
                'step' => '0.01',
                'min' => 0,
                'default' => 50,
                'section' => 'Loyalty',
            ],
            [
                'key' => 'notifications.retention_days',
                'label' => 'Notification retention (days)',
                'help' => 'Notifications older than this are pruned daily.',
                'type' => 'number',
                'min' => 1,
                'max' => 365,
                'default' => 7,
                'section' => 'Notifications',
            ],
            [
                'key' => 'notifications.per_page_default',
                'label' => 'Notifications per page',
                'help' => 'Used for notification listing endpoints.',
                'type' => 'number',
                'min' => 1,
                'max' => 100,
                'default' => 20,
                'section' => 'Notifications',
            ],
            [
                'key' => 'requests.expiry_minutes',
                'label' => 'Request expiry (minutes)',
                'help' => 'Pending requests older than this are marked expired.',
                'type' => 'number',
                'min' => 1,
                'max' => 1440,
                'default' => 15,
                'section' => 'Request & Assignment Timing',
            ],
            [
                'key' => 'no_fixer_retry_delay_minutes',
                'label' => 'No fixer retry delay (minutes)',
                'help' => 'Delay before notifying customer when no fixer is found.',
                'type' => 'number',
                'min' => 1,
                'max' => 60,
                'default' => 5,
                'section' => 'Request & Assignment Timing',
            ],
            [
                'key' => 'admin.per_page',
                'label' => 'Admin default per page',
                'help' => 'Used across all admin tables.',
                'type' => 'number',
                'min' => 5,
                'max' => 200,
                'default' => 20,
                'section' => 'Pagination',
            ],
            [
                'key' => 'api.per_page_default',
                'label' => 'API default per page',
                'help' => 'Fallback page size for API lists.',
                'type' => 'number',
                'min' => 1,
                'max' => 100,
                'default' => 20,
                'section' => 'Pagination',
            ],
            [
                'key' => 'auth.password_reset_expiry_minutes',
                'label' => 'Password reset expiry (minutes)',
                'help' => 'How long reset codes remain valid.',
                'type' => 'number',
                'min' => 5,
                'max' => 120,
                'default' => 15,
                'section' => 'Auth & Security',
            ],
            [
                'key' => 'auth.mfa_challenge_expiry_minutes',
                'label' => 'MFA challenge expiry (minutes)',
                'help' => 'MFA challenge timeout window.',
                'type' => 'number',
                'min' => 1,
                'max' => 30,
                'default' => 5,
                'section' => 'Auth & Security',
            ],
            [
                'key' => 'priority.location_radius_km_default',
                'label' => 'Location radius default (km)',
                'help' => 'Fallback distance for matching if no override is set.',
                'type' => 'number',
                'min' => 1,
                'max' => 1000,
                'default' => 15,
                'section' => 'Advanced: Matching & Priority Tuning',
                'advanced' => true,
            ],
            [
                'key' => 'priority.location_radius_km',
                'label' => 'Location radius override (km)',
                'help' => 'Override matching distance when set.',
                'type' => 'number',
                'min' => 1,
                'max' => 1000,
                'default' => 15,
                'section' => 'Advanced: Matching & Priority Tuning',
                'advanced' => true,
            ],
            [
                'key' => 'priority.cap_default',
                'label' => 'Priority cap',
                'help' => 'Maximum priority score.',
                'type' => 'number',
                'default' => 200,
                'section' => 'Advanced: Matching & Priority Tuning',
                'advanced' => true,
            ],
            [
                'key' => 'priority.floor_default',
                'label' => 'Priority floor',
                'help' => 'Minimum priority score.',
                'type' => 'number',
                'default' => 0,
                'section' => 'Advanced: Matching & Priority Tuning',
                'advanced' => true,
            ],
            [
                'key' => 'priority.assignment_penalty_default',
                'label' => 'Assignment penalty',
                'help' => 'Penalty applied on assignment.',
                'type' => 'number',
                'default' => 0,
                'section' => 'Advanced: Matching & Priority Tuning',
                'advanced' => true,
            ],
            [
                'key' => 'priority.completion_bonus_default',
                'label' => 'Completion bonus',
                'help' => 'Bonus awarded on completion.',
                'type' => 'number',
                'default' => 10,
                'section' => 'Advanced: Matching & Priority Tuning',
                'advanced' => true,
            ],
            [
                'key' => 'priority.timeout_penalty_default',
                'label' => 'Timeout penalty',
                'help' => 'Penalty for timeouts.',
                'type' => 'number',
                'default' => -10,
                'section' => 'Advanced: Matching & Priority Tuning',
                'advanced' => true,
            ],
            [
                'key' => 'priority.weekly_recovery_default',
                'label' => 'Weekly recovery bonus',
                'help' => 'Weekly recovery bonus amount.',
                'type' => 'number',
                'default' => 5,
                'section' => 'Advanced: Matching & Priority Tuning',
                'advanced' => true,
            ],
            [
                'key' => 'priority.idle_bonus_default',
                'label' => 'Idle bonus',
                'help' => 'Bonus when idle.',
                'type' => 'number',
                'default' => 4,
                'section' => 'Advanced: Matching & Priority Tuning',
                'advanced' => true,
            ],
        ];
    }

    private function fieldRules(): array
    {
        return [
            'currency.code' => 'required|string|max:10',
            'currency.symbol' => 'required|string|max:10',
            'currency.name' => 'required|string|max:120',
            'loyalty.point_value' => 'required|numeric|min:0.0001',
            'loyalty.redeem_threshold_value' => 'required|numeric|min:0',
            'notifications.retention_days' => 'required|integer|min:1|max:365',
            'notifications.per_page_default' => 'required|integer|min:1|max:100',
            'requests.expiry_minutes' => 'required|integer|min:1|max:1440',
            'no_fixer_retry_delay_minutes' => 'required|integer|min:1|max:60',
            'admin.per_page' => 'required|integer|min:5|max:200',
            'api.per_page_default' => 'required|integer|min:1|max:100',
            'auth.password_reset_expiry_minutes' => 'required|integer|min:5|max:120',
            'auth.mfa_challenge_expiry_minutes' => 'required|integer|min:1|max:30',
            'priority.location_radius_km_default' => 'required|numeric|min:1|max:1000',
            'priority.location_radius_km' => 'required|numeric|min:1|max:1000',
            'priority.cap_default' => 'required|integer|min:1|max:10000',
            'priority.floor_default' => 'required|integer|min:0|max:10000',
            'priority.assignment_penalty_default' => 'required|integer|min:0|max:10000',
            'priority.completion_bonus_default' => 'required|integer|min:0|max:10000',
            'priority.timeout_penalty_default' => 'required|integer|min:-10000|max:0',
            'priority.weekly_recovery_default' => 'required|integer|min:0|max:10000',
            'priority.idle_bonus_default' => 'required|integer|min:0|max:10000',
        ];
    }

    private function parseEarningsPresets(string $raw): ?array
    {
        $parts = array_filter(array_map('trim', explode(',', $raw)));
        if (empty($parts) || count($parts) > 10) {
            return null;
        }

        $values = [];
        foreach ($parts as $part) {
            if (! ctype_digit($part)) {
                return null;
            }
            $value = (int) $part;
            if ($value < 1 || $value > 365) {
                return null;
            }
            $values[] = $value;
        }

        return array_values(array_unique($values));
        return view('livewire.settings.general-settings');
    }
}
