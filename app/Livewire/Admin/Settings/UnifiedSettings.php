<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Settings;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class UnifiedSettings extends Component
{
    public string $activeTab = 'general';

    public array $tabs = [
        'general' => 'General Settings',
        'branch' => 'Branch Settings',
        'currencies' => 'Currencies',
        'rates' => 'Exchange Rates',
        'translations' => 'Translations',
        'security' => 'Security',
        'backup' => 'Backup',
        'advanced' => 'Advanced',
    ];

    // General settings
    public string $company_name = '';
    public string $company_email = '';
    public string $company_phone = '';
    public string $timezone = 'UTC';
    public string $date_format = 'Y-m-d';
    public string $default_currency = 'USD';

    // Branch settings
    public bool $multi_branch = false;
    public bool $require_branch_selection = true;

    // Security settings
    public bool $require_2fa = false;
    public int $session_timeout = 120;
    public bool $enable_audit_log = true;

    // Advanced settings
    public bool $enable_api = true;
    public bool $enable_webhooks = false;
    public int $cache_ttl = 3600;

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user || ! $user->can('settings.view')) {
            abort(403);
        }

        // Get tab from query string
        $this->activeTab = request()->query('tab', 'general');
        
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        // Load general settings
        $this->company_name = $this->getSetting('company.name', config('app.name', 'HugouERP'));
        $this->company_email = $this->getSetting('company.email', '');
        $this->company_phone = $this->getSetting('company.phone', '');
        $this->timezone = $this->getSetting('app.timezone', config('app.timezone', 'UTC'));
        $this->date_format = $this->getSetting('app.date_format', 'Y-m-d');
        $this->default_currency = $this->getSetting('app.default_currency', 'USD');

        // Load other settings
        $this->multi_branch = (bool) $this->getSetting('system.multi_branch', false);
        $this->require_branch_selection = (bool) $this->getSetting('system.require_branch_selection', true);
        $this->require_2fa = (bool) $this->getSetting('security.require_2fa', false);
        $this->session_timeout = (int) $this->getSetting('security.session_timeout', 120);
        $this->enable_audit_log = (bool) $this->getSetting('security.enable_audit_log', true);
        $this->enable_api = (bool) $this->getSetting('advanced.enable_api', true);
        $this->enable_webhooks = (bool) $this->getSetting('advanced.enable_webhooks', false);
        $this->cache_ttl = (int) $this->getSetting('advanced.cache_ttl', 3600);
    }

    protected function getSetting(string $key, $default = null)
    {
        $setting = SystemSetting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    protected function setSetting(string $key, $value, string $group = 'general'): void
    {
        SystemSetting::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'group' => $group,
                'is_public' => false,
            ]
        );
    }

    public function switchTab(string $tab): void
    {
        if (array_key_exists($tab, $this->tabs)) {
            $this->activeTab = $tab;
        }
    }

    public function saveGeneral(): void
    {
        $this->validate([
            'company_name' => 'required|string|max:255',
            'company_email' => 'nullable|email|max:255',
            'company_phone' => 'nullable|string|max:50',
            'timezone' => 'required|string',
            'date_format' => 'required|string',
            'default_currency' => 'required|string|size:3',
        ]);

        $this->setSetting('company.name', $this->company_name, 'general');
        $this->setSetting('company.email', $this->company_email, 'general');
        $this->setSetting('company.phone', $this->company_phone, 'general');
        $this->setSetting('app.timezone', $this->timezone, 'general');
        $this->setSetting('app.date_format', $this->date_format, 'general');
        $this->setSetting('app.default_currency', $this->default_currency, 'general');

        Cache::forget('system_settings');
        session()->flash('success', __('General settings saved successfully'));
    }

    public function saveBranch(): void
    {
        $this->setSetting('system.multi_branch', $this->multi_branch, 'branch');
        $this->setSetting('system.require_branch_selection', $this->require_branch_selection, 'branch');

        Cache::forget('system_settings');
        session()->flash('success', __('Branch settings saved successfully'));
    }

    public function saveSecurity(): void
    {
        $this->validate([
            'session_timeout' => 'required|integer|min:5|max:1440',
        ]);

        $this->setSetting('security.require_2fa', $this->require_2fa, 'security');
        $this->setSetting('security.session_timeout', $this->session_timeout, 'security');
        $this->setSetting('security.enable_audit_log', $this->enable_audit_log, 'security');

        Cache::forget('system_settings');
        session()->flash('success', __('Security settings saved successfully'));
    }

    public function saveAdvanced(): void
    {
        $this->validate([
            'cache_ttl' => 'required|integer|min:60|max:86400',
        ]);

        $this->setSetting('advanced.enable_api', $this->enable_api, 'advanced');
        $this->setSetting('advanced.enable_webhooks', $this->enable_webhooks, 'advanced');
        $this->setSetting('advanced.cache_ttl', $this->cache_ttl, 'advanced');

        Cache::forget('system_settings');
        session()->flash('success', __('Advanced settings saved successfully'));
    }

    public function render()
    {
        return view('livewire.admin.settings.unified-settings');
    }
}
