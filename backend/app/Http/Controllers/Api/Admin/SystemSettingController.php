<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SystemSettingController extends Controller
{
    /**
     * List all system settings
     */
    public function index()
    {
        $settings = SystemSetting::all()->map(function ($setting) {
            $setting->value = $setting->castValue($setting->value, $setting->type);
            return $setting;
        });

        return response()->json($settings);
    }

    /**
     * Update settings (Bulk or Single)
     * Expects payload: { settings: [{ key: 'allow_registration', value: false }, ... ] }
     */
    public function update(Request $request)
    {
        Log::info('Settings Update Payload:', $request->all());

        $request->validate([
            'settings' => 'present|array',
            'settings.*.key' => 'required|string|exists:system_settings,key',
            'settings.*.value' => 'nullable',
        ]);

        foreach ($request->settings as $item) {
            $setting = SystemSetting::where('key', $item['key'])->first();

            $valueToStore = $item['value'];
            if (is_bool($valueToStore)) {
                $valueToStore = $valueToStore ? 'true' : 'false';
            } elseif (is_array($valueToStore)) {
                $valueToStore = json_encode($valueToStore);
            }

            $setting->value = (string) $valueToStore;
            $setting->save();
        }

        return response()->json(['message' => 'Cập nhật cài đặt thành công']);
    }

    /**
     * Get public settings (allow_registration, maintenance_mode, etc.)
     */
    public function publicSettings()
    {
        $keys = ['allow_registration', 'maintenance_mode', 'enable_ai_chat'];
        $settings = SystemSetting::whereIn('key', $keys)->get()->mapWithKeys(function (SystemSetting $item) {
            return [$item->key => $item->castValue($item->value, $item->type)];
        });

        return response()->json($settings);
    }
}
