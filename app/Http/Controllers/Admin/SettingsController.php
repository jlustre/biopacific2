<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class SettingsController extends Controller
{
    public function index()
    {
        return view('admin.settings.index');
    }

    public function update(Request $request)
    {
        $settings = [
            'site_name' => $request->input('site_name'),
            'site_email' => $request->input('site_email'),
            'theme' => $request->input('theme'),
            'enable_mfa' => $request->has('enable_mfa') ? '1' : '0',
        ];
        foreach ($settings as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }
        return redirect()->route('admin.settings.index')->with('status', 'Settings updated!');
    }
}
