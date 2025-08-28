<?php

namespace App\Services;

class TenantConfigService
{
    protected $facility;

    public function __construct()
    {
        // Try to get current facility, but don't fail if it doesn't exist
        try {
            $this->facility = app()->bound('current_facility') ? app('current_facility') : null;
        } catch (\Exception $e) {
            $this->facility = null;
        }
    }

    public function get($key, $default = null)
    {
        if (!$this->facility) {
            return $default;
        }

        $settings = $this->facility->settings ?? [];
        return data_get($settings, $key, $default);
    }

    public function set($key, $value)
    {
        if (!$this->facility) {
            return false;
        }

        $settings = $this->facility->settings ?? [];
        data_set($settings, $key, $value);
        $this->facility->update(['settings' => $settings]);
        return true;
    }

    public function getThemeColors()
    {
        return [
            'primary' => $this->get('theme.primary_color', $this->facility->primary_color ?? '#047857'),
            'secondary' => $this->get('theme.secondary_color', $this->facility->secondary_color ?? '#1f2937'),
            'accent' => $this->get('theme.accent_color', $this->facility->accent_color ?? '#06b6d4'),
        ];
    }

    public function getLayoutConfig($section = null)
    {
        if (!$this->facility) {
            return [];
        }

        return $this->facility->getLayoutConfig($section);
    }

    public function setLayoutConfig($section, $config)
    {
        if (!$this->facility) {
            return false;
        }

        return $this->facility->setLayoutConfig($section, $config);
    }

    public function getLayoutTemplate()
    {
<<<<<<< HEAD
        return $this->facility ? $this->facility->layout_template : 'default-template';
=======
        return $this->facility ? $this->facility->layout_template : 'layout1';
>>>>>>> 5a7e1f9599c22a67bfe93c9cd3f696bb1a5ec0be
    }
}
