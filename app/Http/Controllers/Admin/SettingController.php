<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.edit', [
            'logoUrl' => Setting::logoUrl(),
            'settings' => [
                'tagline' => Setting::get('tagline', config('seeds_bazar.tagline')),
                'whatsapp_number' => Setting::get('whatsapp_number', config('seeds_bazar.whatsapp_number')),
                'shipping_flat_rate' => Setting::get('shipping_flat_rate', (string) config('seeds_bazar.shipping.flat_rate')),
                'free_shipping_threshold' => Setting::get('free_shipping_threshold', (string) config('seeds_bazar.shipping.free_threshold')),
                'shipping_estimate' => Setting::get('shipping_estimate', config('seeds_bazar.shipping.estimate')),
                'shipping_method' => Setting::get('shipping_method', config('seeds_bazar.shipping.method')),
                'policy_shipping' => Setting::get('policy_shipping'),
                'policy_returns' => Setting::get('policy_returns'),
                'policy_privacy' => Setting::get('policy_privacy'),
                'policy_terms' => Setting::get('policy_terms'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tagline' => ['required', 'string', 'max:160'],
            'whatsapp_number' => ['required', 'string', 'max:30'],
            'shipping_flat_rate' => ['required', 'numeric', 'min:0', 'max:999999'],
            'free_shipping_threshold' => ['required', 'numeric', 'min:0', 'max:999999'],
            'shipping_estimate' => ['required', 'string', 'max:100'],
            'shipping_method' => ['required', 'string', 'max:50'],
            'policy_shipping' => ['nullable', 'string', 'max:10000'],
            'policy_returns' => ['nullable', 'string', 'max:10000'],
            'policy_privacy' => ['nullable', 'string', 'max:10000'],
            'policy_terms' => ['nullable', 'string', 'max:10000'],
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, (string) $value);
        }

        return back()->with('success', 'Storefront settings updated successfully.');
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => ['required', 'image', 'mimes:jpeg,png,jpg,webp,gif,svg'],
        ]);

        Setting::deleteLogoFile();
        $path = $request->file('logo')->store('settings', 'public');
        Setting::set('site_logo', $path);

        return back()->with('success', 'Logo updated successfully.');
    }

    public function removeLogo(): RedirectResponse
    {
        Setting::deleteLogoFile();
        Setting::set('site_logo', null);

        return back()->with('success', 'Logo removed. Default icon will show.');
    }
}
