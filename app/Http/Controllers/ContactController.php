<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function show(): View
    {
		
        return view('contact');
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'mobile' => ['required', 'string', 'max:25'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:255'],
            'query' => ['nullable', 'string', 'max:2000'],
        ]);

        ContactMessage::create([
            ...$validated,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Thanks! We received your details. We will contact you soon.',
            ]);
        }

        return back()->with('success', 'Thanks! We received your details. We will contact you soon.');
    }
}
