<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SettingsController extends Controller
{
    /**
     * Display the settings page.
     */
    public function index()
    {
        $setting = Setting::get();
        return view('admin.settings.index', compact('setting'));
    }

    /**
     * Update the settings.
     */
    public function update(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'timezone' => 'required|string|max:255',
            'withdrawal_limit_per_month' => 'required|integer|min:1|max:100',
        ]);

        $setting = Setting::get();
        
        // Debug: Log request data
        \Log::info('Settings update request', [
            'has_file' => $request->hasFile('logo'),
            'file_valid' => $request->hasFile('logo') ? $request->file('logo')->isValid() : false,
            'company_name' => $request->company_name
        ]);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            try {
                $logoDir = public_path('images/logo');
                
                \Log::info('Logo upload started', [
                    'logo_dir' => $logoDir,
                    'dir_exists' => File::exists($logoDir),
                    'is_writable' => is_writable(dirname($logoDir))
                ]);
                
                // Ensure the images directory exists first
                $imagesDir = public_path('images');
                if (!File::exists($imagesDir)) {
                    File::makeDirectory($imagesDir, 0755, true);
                }
                
                // Ensure the logo directory exists
                if (!File::exists($logoDir)) {
                    $created = File::makeDirectory($logoDir, 0755, true);
                    if (!$created) {
                        throw new \Exception('Failed to create logo directory: ' . $logoDir);
                    }
                }
                
                // Check if directory is writable
                if (!is_writable($logoDir)) {
                    throw new \Exception('Logo directory is not writable: ' . $logoDir);
                }
                
                // Delete old logo if exists
                if ($setting->logo_path) {
                    $oldLogoPath = public_path($setting->logo_path);
                    if (File::exists($oldLogoPath)) {
                        File::delete($oldLogoPath);
                        \Log::info('Old logo deleted', ['path' => $oldLogoPath]);
                    }
                }

                // Get file and generate unique filename
                $file = $request->file('logo');
                $extension = $file->getClientOriginalExtension();
                $filename = 'logo_' . time() . '.' . $extension;
                $fullPath = $logoDir . '/' . $filename;
                
                \Log::info('Moving file', [
                    'from' => $file->getPathname(),
                    'to' => $fullPath
                ]);
                
                // Move file to public/images/logo directory
                $moved = $file->move($logoDir, $filename);
                
                if (!$moved || !File::exists($fullPath)) {
                    throw new \Exception('Failed to move uploaded file to: ' . $fullPath);
                }
                
                // Verify file was saved
                if (!File::exists($fullPath)) {
                    throw new \Exception('File does not exist after move: ' . $fullPath);
                }
                
                // Save relative path (images/logo/filename)
                $setting->logo_path = 'images/logo/' . $filename;
                
                \Log::info('Logo uploaded successfully', [
                    'logo_path' => $setting->logo_path,
                    'full_path' => $fullPath,
                    'file_size' => File::size($fullPath)
                ]);
                
            } catch (\Exception $e) {
                \Log::error('Logo upload error', [
                    'message' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'logo_dir' => $logoDir ?? 'not set'
                ]);
                return redirect()->route('admin.settings.index')
                    ->with('error', 'Failed to upload logo: ' . $e->getMessage());
            }
        }

        // Update company name, timezone, and withdrawal limit
        $setting->company_name = $request->company_name;
        $setting->timezone = $request->timezone;
        $setting->withdrawal_limit_per_month = $request->withdrawal_limit_per_month;
        $setting->save();
        
        // Update application timezone
        config(['app.timezone' => $request->timezone]);
        date_default_timezone_set($request->timezone);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Settings updated successfully.');
    }
}
