<?php

namespace App\Http\Controllers;

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
        //$datoss=request()->all();
        //return response()->json($datoss);

        $request->validate([
            'name' =>'required|max:200',
            'email'=> 'required|unique:users',
            'password'=>'required|confirmed',
        ]);
        $usuario = new User();
        $usuario->name = $request->name;
        $usuario->email =$request->email;
        $usuario->password=$request->password;

        $usuario->save();
        return redirect()->route('usuarios.index');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
     $usuario = User::findOrFail    ($id);
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
        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|unique:users',
            'password'=>'confirmed',
        ]);
        $usuario = User::find($id);
        $usuario->name = $request->name;
        $usuario->email =$request->email;
        $usuario->password=Hash::make(($request['password']));
        $usuario->save();
        
        return redirect()->route('usuarios.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
