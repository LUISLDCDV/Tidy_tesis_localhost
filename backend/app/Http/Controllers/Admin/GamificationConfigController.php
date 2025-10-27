<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GamificationConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GamificationConfigController extends Controller
{
    public function index()
    {
        $configs = GamificationConfig::orderBy('key')->get()->groupBy(function ($item) {
            return str_contains($item->key, 'crear') ? 'crear' : 'completar';
        });

        return view('admin.gamification.index', compact('configs'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'configs' => 'required|array',
            'configs.*.id' => 'required|exists:gamification_config,id',
            'configs.*.value' => 'required|integer|min:0|max:1000',
        ]);

        try {
            foreach ($request->configs as $configData) {
                $config = GamificationConfig::find($configData['id']);
                $config->value = $configData['value'];
                $config->save();
            }

            Log::info("✅ Configuración de gamificación actualizada", [
                'admin_user_id' => auth()->id(),
                'changes' => $request->configs
            ]);

            return back()->with('success', 'Configuración de XP actualizada correctamente');
        } catch (\Exception $e) {
            Log::error("❌ Error actualizando configuración de gamificación", [
                'error' => $e->getMessage(),
                'admin_user_id' => auth()->id()
            ]);

            return back()->with('error', 'Error al actualizar configuración: ' . $e->getMessage());
        }
    }
}
