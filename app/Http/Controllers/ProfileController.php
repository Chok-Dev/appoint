<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        // Return our custom backend view instead of the default Laravel Breeze view
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }
    // Add these methods to your ProfileController.php file

    /**
     * Update the user's Telegram chat ID.
     */
    public function updateTelegram(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'telegram_chat_id' => ['required', 'string'],
        ]);

        $request->user()->update([
            'telegram_chat_id' => $validated['telegram_chat_id'],
        ]);

        return Redirect::route('profile.edit')->with('status', 'telegram-updated');
    }

    /**
     * Disable Telegram notifications for the user.
     */
    public function disableTelegram(Request $request): RedirectResponse
    {
        $request->user()->update([
            'telegram_chat_id' => null,
        ]);

        return Redirect::route('profile.edit')->with('status', 'telegram-disabled');
    }
    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
