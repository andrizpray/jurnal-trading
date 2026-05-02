<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function __construct() {}

    public function index()
    {
        $user = Auth::user();
        return view('settings.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $minCapital = match ($request->input('currency', $user->currency)) {
            'USD' => 50,
            'USC' => 5000,
            default => 100000,
        };

        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'required|email|unique:users,email,' . $user->id,
            'currency'        => 'required|in:IDR,USD,USC',
            'default_capital' => "required|numeric|min:{$minCapital}",
            'trader_type'     => 'required|in:conservative,moderate,aggressive',
            'timezone'        => 'required|string',
        ]);

        $user->update($validated);

        return back()->with('success', '⚙️ Pengaturan berhasil disimpan!');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password'         => 'required|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', '🔐 Password berhasil diperbarui!');
    }
}
