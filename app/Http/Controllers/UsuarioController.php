<?php

namespace App\Http\Controllers;
use Illuminate\Validation\Rule;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

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
            'name'     => 'required|max:200',
            'email'    => 'required|unique:users',
            'password' => 'required|confirmed',
            'rol'      => 'required|in:super_admin,publicador,espectador',
        ]);
        
        $usuario = new User();
        $usuario->name     = $request->name;
        $usuario->email    = $request->email;
        
        // 👉 MEJORA DE SEGURIDAD: Encriptamos la contraseña al crear
        $usuario->password = Hash::make($request->password); 
        
        // 👉 NUEVO: Guardamos el rol en la base de datos
        $usuario->rol      = $request->rol; 

        $usuario->save();
        
        return redirect()->route('usuarios.index')
            ->with('ok', 'Usuario creado correctamente.');
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
}