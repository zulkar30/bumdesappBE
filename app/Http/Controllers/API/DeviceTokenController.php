<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

class DeviceTokenController extends Controller
{
    public function saveToken(Request $request)
    {
        // Validasi input token
        $request->validate([
            'device_token' => 'required|string',
        ]);

        Log::info('Data yang diterima di Backend:', $request->all());
        
        $user = auth()->user(); // Untuk login
        if (!$user) {
            $user = User::find($request->user_id); // Atau ambil berdasarkan user_id dari request saat registrasi
        }

        if ($user) {
            $user->device_token = $request->device_token;  // Simpan device_token
            $user->save();
    
            return ResponseFormatter::success($user, 'Device Token berhasil disimpan');
        }
        return ResponseFormatter::error('Error', 'Gagal menyimpan device token');
    }
}
