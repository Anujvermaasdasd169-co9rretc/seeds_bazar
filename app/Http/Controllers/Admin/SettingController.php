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
        ]);
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
