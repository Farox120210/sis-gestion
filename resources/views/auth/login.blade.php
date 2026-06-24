<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar Sesión | ESAT-UNASAM</title>

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    
    <style>
        :root {
            --unasam-navy: #003366; /* Ajusta este color al de tu sistema */
            --esat-blue: #007bff;
        }
        body { background-color: #f4f6f9; }
        .login-card { border-radius: 15px !important; }
        .btn-navy { background-color: var(--unasam-navy); color: white; }
        .btn-navy:hover { background-color: #002244; color: white; }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box">
        <div class="card card-outline card-primary login-card shadow-lg border-0">
           <div class="card-header text-center border-0 pt-4">
    <a href="{{ url('/') }}" class="d-flex align-items-center justify-content-center text-decoration-none">
        <img src="{{ asset('dist/img/LOGO_UNASAM-removebg-preview.png') }}" 
             alt="Logo UNASAM" 
             style="height: 60px; width: auto; margin-right: 15px;">
        
        <span class="h2 font-weight-bold m-0" style="color: var(--unasam-navy);">
            ESAT<span style="color: var(--esat-blue);">-</span>UNASAM
        </span>
    </a>
</div>
            <div class="card-body">
                <p class="login-box-msg mb-4 text-muted">Ingresa tus credenciales para acceder</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="form-group mb-3">
                        <label for="email" class="font-weight-bold text-dark">Correo Electrónico</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" 
                               name="email" value="{{ old('email') }}" required autocomplete="email" autofocus 
                               placeholder="usuario@unasam.edu.pe" style="border-radius: 8px;">
                        @error('email')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="form-group mb-3">
                        <label for="password" class="font-weight-bold text-dark">Contraseña</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                               name="password" required autocomplete="current-password" 
                               placeholder="••••••••" style="border-radius: 8px;">
                        @error('password')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="row mb-3">
                        <div class="col-8">
                            <div class="icheck-primary">
                                <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label for="remember">Recordarme</label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-navy btn-block font-weight-bold py-2 mb-3" style="border-radius: 8px;">
                        {{ __('Ingresar al sistema') }}
                    </button>
                </form>

                <div class="text-center mt-3">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none d-block mb-2">¿Olvidaste tu contraseña?</a>
                    @endif
                    <a href="#" class="text-center text-decoration-none text-muted">Registrar nuevo miembro</a>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
</body>
</html>