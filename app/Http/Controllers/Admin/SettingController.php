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
                'header_search_placeholder' => Setting::get('header_search_placeholder', 'Search seeds, plants & more…'),
                'header_show_home' => Setting::enabled('header_show_home'),
                'header_home_label' => Setting::get('header_home_label', 'Home'),
                'header_guide_label' => Setting::get('header_guide_label', 'Growing guide'),
                'header_guide_url' => Setting::get('header_guide_url', '#grower-guide'),
                'header_show_contact' => Setting::enabled('header_show_contact'),
                'header_contact_label' => Setting::get('header_contact_label', 'Contact Us'),
                'header_show_account' => Setting::enabled('header_show_account'),
                'top_categories_eyebrow' => Setting::get('top_categories_eyebrow', 'Most Popular'),
                'top_categories_title' => Setting::get('top_categories_title', 'Top Categories'),
                'top_categories_intro' => Setting::get('top_categories_intro', 'Thoughtfully chosen seeds for kitchen gardens, flowering balconies, and productive fields—packed in practical quantities and ready to grow.'),
                'catalog_eyebrow' => Setting::get('catalog_eyebrow', 'Fresh picks'),
                'catalog_title' => Setting::get('catalog_title', 'Seeds worth growing'),
                'catalog_intro' => Setting::get('catalog_intro', 'Browse by category, search a variety, or open any pack for more detail.'),
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
            'header_search_placeholder' => ['nullable', 'string', 'max:120'],
            'header_show_home' => ['sometimes', 'boolean'],
            'header_home_label' => ['nullable', 'string', 'max:40'],
            'header_guide_label' => ['nullable', 'string', 'max:40'],
            'header_guide_url' => ['nullable', 'string', 'max:255'],
            'header_show_contact' => ['sometimes', 'boolean'],
            'header_contact_label' => ['nullable', 'string', 'max:40'],
            'header_show_account' => ['sometimes', 'boolean'],
            'top_categories_eyebrow' => ['nullable', 'string', 'max:80'],
            'top_categories_title' => ['nullable', 'string', 'max:80'],
            'top_categories_intro' => ['nullable', 'string', 'max:400'],
            'catalog_eyebrow' => ['nullable', 'string', 'max:80'],
            'catalog_title' => ['nullable', 'string', 'max:80'],
            'catalog_intro' => ['nullable', 'string', 'max:400'],
        ]);

        foreach (['header_show_home', 'header_show_contact', 'header_show_account'] as $flag) {
            if ($request->exists($flag) || $request->exists('_storefront')) {
                $validated[$flag] = $request->boolean($flag) ? '1' : '0';
            }
        }

        foreach ($validated as $key => $value) {
            if ($value === null) {
                Setting::set($key, '');
                continue;
            }

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
