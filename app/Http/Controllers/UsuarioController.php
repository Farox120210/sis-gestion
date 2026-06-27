<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserInvitationMail;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usuarios = User::all();
        return view('admin.usuarios.index',['usuarios'=>$usuarios]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.usuarios.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name'  => 'required|max:200',
            'email' => 'required|email|unique:users,email',
            'rol'   => 'required|in:super_admin,publicador,espectador',
        ], [
            'name.required'  => 'El nombre es obligatorio.',
            'name.max'       => 'El nombre no debe superar los 200 caracteres.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email'    => 'El formato del correo electrónico no es válido.',
            'email.unique'   => 'Este correo electrónico ya está registrado en el sistema.',
            'rol.required'   => 'El rol del usuario es obligatorio.',
            'rol.in'         => 'El rol seleccionado no es válido.',
        ]);
        
        $token = Str::random(60);

        $usuario = new User();
        $usuario->name     = $request->name;
        $usuario->email    = $request->email;
        $usuario->rol      = $request->rol;
        $usuario->status   = 'inactive';
        $usuario->activation_token = $token;
        $usuario->activation_token_expires_at = now()->addHours(24);
        $usuario->save();
        
        // Generamos la URL de activación y enviamos el correo electrónico
        $activationUrl = route('activation.form', ['token' => $token]);
        Mail::to($usuario->email)->send(new UserInvitationMail($usuario, $activationUrl));
        
        return redirect()->route('usuarios.index')
            ->with('ok', 'Usuario creado correctamente. Se ha enviado un correo electrónico de invitación para activar la cuenta.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $usuario = User::findOrFail($id);
        return view('admin.usuarios.show',['usuario'=>$usuario] );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $usuario= User::FindOrFail($id);
        return view('admin.usuarios.edit',['usuario'=>$usuario] );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $usuario = User::findOrFail($id);

        $request->validate([
            'name' => 'required|max:100',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($usuario->id), // <- evita conflicto con su propio correo
            ],
            'password' => [
                'nullable',   // puede venir vacío
                'confirmed',  // password_confirmation
            ],
            // 👉 NUEVO: Validamos el rol al actualizar
            'rol' => 'required|in:super_admin,publicador,espectador', 
        ]);

        $usuario->name  = $request->name;
        $usuario->email = $request->email;
        
        // 👉 NUEVO: Actualizamos el rol en la base de datos
        $usuario->rol   = $request->rol; 

        // Solo cambiar contraseña si el usuario escribió una nueva
        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();

        return redirect()->route('usuarios.index')
            ->with('ok', 'Usuario actualizado correctamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $usuario = User::findOrFail($id);
        $usuario->delete();

        return redirect()->route('usuarios.index');
    }

    /**
     * Reenvía el correo de invitación a un usuario inactivo.
     */
    public function resendInvitation($id)
    {
        $usuario = User::findOrFail($id);

        if ($usuario->status === 'active') {
            return redirect()->route('usuarios.index')
                ->with('error', 'El usuario ya se encuentra activo.');
        }

        // Regeneramos el token y expiración
        $token = Str::random(60);
        $usuario->activation_token = $token;
        $usuario->activation_token_expires_at = now()->addHours(24);
        $usuario->save();

        // Generamos la URL de activación y enviamos el correo
        $activationUrl = route('activation.form', ['token' => $token]);
        Mail::to($usuario->email)->send(new UserInvitationMail($usuario, $activationUrl));

        return redirect()->route('usuarios.index')
            ->with('ok', 'Invitación reenviada correctamente al correo ' . $usuario->email . '.');
    }
}