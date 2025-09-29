<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Models\SchoolSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class SchoolSettingsController extends Controller
{
    /**
     * Show the school settings page.
     */
    public function edit(Request $request): Response
    {
        $settings = SchoolSettings::first() ?? new SchoolSettings();
        
        return Inertia::render('settings/SchoolSettings', [
            'settings' => $settings,
        ]);
    }

    /**
     * Update the school settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'nama_sekolah' => 'required|string|max:255',
            'alamat' => 'required|string',
            'telepon' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|string|max:255',
            'kepala_sekolah' => 'nullable|string|max:255',
            'npsn' => 'nullable|string|max:20',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $settings = SchoolSettings::first() ?? new SchoolSettings();
        
        $settings->nama_sekolah = $request->nama_sekolah;
        $settings->alamat = $request->alamat;
        $settings->telepon = $request->telepon;
        $settings->email = $request->email;
        $settings->website = $request->website;
        $settings->kepala_sekolah = $request->kepala_sekolah;
        $settings->npsn = $request->npsn;

        if ($request->hasFile('logo')) {
            // Hapus logo lama jika ada
            if ($settings->logo_url) {
                Storage::disk('public')->delete($settings->logo_url);
            }
            
            // Simpan logo baru
            $path = $request->file('logo')->store('logos', 'public');
            $settings->logo_url = $path;
        }

        $settings->save();

        return redirect()->route('school-settings.edit')->with('success', 'Pengaturan sekolah berhasil diperbarui.');
    }
}