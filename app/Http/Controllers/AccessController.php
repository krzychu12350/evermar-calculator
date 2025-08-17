<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AccessController extends Controller
{
    // Handle password submission
    public function check(Request $request): \Illuminate\Http\RedirectResponse
    {
        $request->validate([
            'password' => 'required|string',
        ]);

        $key = Str::lower($request->ip()).'|password-attempt'; // unique key per IP

        // Check if the user exceeded 5 attempts per hour
//        if (RateLimiter::tooManyAttempts($key, 5)) {
//            $seconds = RateLimiter::availableIn($key);
//
//            $minutes = floor($seconds / 60);
//            $remainingSeconds = $seconds % 60;
//
//            $timeMessage = $minutes > 0
//                ? "{$minutes} minut" . ($remainingSeconds > 0 ? " i {$remainingSeconds} sekund" : "")
//                : "{$remainingSeconds} sekund";
//
//            return back()->withErrors([
//                'password' => "Za dużo prób. Spróbuj ponownie za {$timeMessage}."
//            ]);
//        }

        if ($request->password === 'Evermar12?3') {
            // Reset attempts on success
            RateLimiter::clear($key);

            $request->session()->put('access_granted', true);
            return redirect()->route('home'); // redirect to Home page
        }

        // Increment failed attempts
        RateLimiter::hit($key, 3600); // lockout lasts 1 hour

        return back()->withErrors([
            'password' => 'Niepoprawne hasło!'
        ]);
    }
}
