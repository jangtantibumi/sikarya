<?php

declare(strict_types=1);

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Inventory\InventorySetting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = InventorySetting::all();

        return view('inventory.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            InventorySetting::updateOrCreate(
                ['setting_key' => $key],
                ['setting_value' => $value]
            );
        }

        return redirect()->route('inventory.settings.index')->with('success', 'Pengaturan Inventory berhasil diperbarui.');
    }
}
