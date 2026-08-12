<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ActivationController extends Controller
{
    /**
     * Muestra el formulario de activación y establecimiento de contraseña.
     */
    public function showForm($token)
    {
        // Buscamos al usuario que tenga el token de activación
        $user = User::where('activation_token', $token)->first();

        // Validamos si el usuario existe y si el token no ha expirado
        if (!$user || !$user->activation_token_expires_at || now()->greaterThan($user->activation_token_expires_at)) {
            return redirect()->route('login')->with('error', 'El enlace de activación es inválido o ha expirado. Por favor, contacte al administrador para recibir una nueva invitación.');
        }

        return view('auth.activate', ['token' => $token, 'user' => $user]);
    }

    /**
     * Procesa la contraseña y activa la cuenta del usuario.
     */
    public function activate(Request $request, $token)
    {
        $user = User::where('activation_token', $token)->first();

        if (!$user || !$user->activation_token_expires_at || now()->greaterThan($user->activation_token_expires_at)) {
            return redirect()->route('login')->with('error', 'El enlace de activación ha expirado o es inválido.');
        }

        // Validación de la nueva contraseña
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ], [
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas ingresadas no coinciden.',
        ]);

        // Actualizamos los datos del usuario
        $user->password = Hash::make($request->password);
        $user->status = 'active';
        $user->activation_token = null;
        $user->activation_token_expires_at = null;
        $user->save();

        // Iniciamos sesión automáticamente
        Auth::login($user);

        return redirect()->route('admin.index')->with('ok', '¡Tu cuenta ha sido activada correctamente! Bienvenido al sistema.');
    }
}
