<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/home';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Intercepta el inicio de sesión para verificar que la cuenta esté activa.
     */
    protected function attemptLogin(\Illuminate\Http\Request $request)
    {
        $user = \App\Models\User::where('email', $request->email)->first();
        
        if ($user && $user->status === 'inactive') {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => ['Su cuenta está inactiva. Por favor, revise su correo institucional para activarla o contacte al administrador.'],
            ]);
        }

        return $this->guard()->attempt(
            $this->credentials($request), $request->filled('remember')
        );
    }
}
