<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use App\Actions\Fortify\PasswordValidationRules;

class UserController extends Controller
{

    use PasswordValidationRules;

    public function fetch(Request $request)
    {
        return ResponseFormatter::success(
            $request->user(), 'Data profile successfully taken'
        );
    }
    public function login(Request $request)
    {
        try {
            // Validasi input
            $request->validate([
                'email' => 'email|required',
                'password' => 'required'
            ]);

            // Cek credentials (login)
            // $credentials = $request->only('email', 'password');
            // if(!Auth::attempt($credentials)) {
            //     return ResponseFormatter::error([
            //         'message' => 'Unauthorized'
            //     ], 'Authentication Failed', 500);
            // }

            if (!Auth::attempt($request->only('email', 'password'))) {
                return ResponseFormatter::error([
                    'message' => 'Email atau password salah'
                ], 'Login Gagal', 401);
            }
            
            // Jika hash/password tidak sesuai
            $user = User::where('email', $request->email)->with('city')->first();

            // Cek apakah user sudah verifikasi email
            if (is_null($user->email_verified_at)) {
                return ResponseFormatter::error([
                    'message' => 'Email belum diverifikasi'
                ], 'Email Not Verified', 403);
            }

            // Jika data benar
            $tokenResult = $user->createToken('authToken')->plainTextToken;
            return ResponseFormatter::success([
                'access_token' => $tokenResult,
                'token_type' => 'Bearer',
                'user' => $user
            ], 'Authenticated');
        } catch(Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Ada sesuatau yang salah',
                'error' => $error
            ], 'Login Gagal', 500);
        }
    }

    public function register(Request $request)
    {
        try {
            $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
                'password' => $this->passwordRules()
            ]);

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'address' => $request->address,
                'houseNumber' => $request->houseNumber,
                'phoneNumber' => $request->phoneNumber,
                'city_id' => $request->city_id,
                'password' => Hash::make($request->password)
            ]);

            // Kirim email verifikasi
            event(new Registered($user));

            return ResponseFormatter::success([
                'message' => 'Pendaftaran berhasil. Silakan cek email Anda untuk verifikasi sebelum login.'
            ], 'Register Success, Verification Email Sent');

        } catch(Exception $error) {
            return ResponseFormatter::error([
                'message' => 'Something went wrong',
                'error' => $error->getMessage()
            ], 'Register Failed', 500);
        }
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken()->delete();

        return ResponseFormatter::success($token, 'Token Revoked');
    }

    public function updateProfile(Request $request)
    {
        $data = $request->all();

        $user = Auth::user();
        $user->update($data);

        return ResponseFormatter::success($user, 'Profile Updated');
    }

    public function updatePhoto(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|image|max:2048'
        ]);

        if($validator->fails())
        {
            return ResponseFormatter::error([
                'error' => $validator->errors()
            ], 'Update foto gagal', 401);
        }

        if($request->file('file'))
        {
            $file = $request->file->store('assets/user', 'public');

            // Simpan ke database
            $user = Auth::user();
            $user->picturePath = $file;
            $user->update();

            return ResponseFormatter::success([$file], 'Foto profil berhasil di upload');
        }
    }

    public function getShippingPrice(Request $request)
    {
        $user = $request->user();

        if (!$user->city || !$user->city->zone) {
            return response()->json(['message' => 'Zona tidak ditemukan'], 404);
        }

        return response()->json([
            'driver_price' => $user->city->zone->price,
        ]);
    }

}
