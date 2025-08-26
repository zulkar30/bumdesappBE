<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Routing\Exceptions\InvalidSignatureException;

class VerifyEmailApiController extends Controller
{
    public function verify(Request $request, $id, $hash)
    {
        // Validasi signature di middleware 'signed' sudah jalan.
        $user = User::findOrFail($id);

        // Cocokkan hash
        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw new InvalidSignatureException;
        }

        // Tandai terverifikasi (idempotent)
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // Ambil target deep link dari query ?redirect=
        $redirect = $request->query('redirect');

        // (Keamanan) whitelist skema yang diizinkan
        if ($redirect && preg_match('#^(bumdesapp)://#i', $redirect)) {
            // Contoh: bumdesapp://email/verified?status=success
            $sep = str_contains($redirect, '?') ? '&' : '?';
            return redirect($redirect.$sep.'verified=1');
        }

        // Fallback (kalau app tidak terpasang / redirect tidak dikirim)
        // Tampilkan halaman HTML sederhana
        return response()->view('auth.verify-success'); // buat blade sederhana
    }
}
