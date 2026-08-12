<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Activar Cuenta | ESAT-UNASAM</title>
    <link rel="icon" href="{{ asset('dist/img/LOGO_UNASAM-removebg-preview.png') }}" type="image/png">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <link rel="stylesheet" href="{{ asset('plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('dist/css/adminlte.min.css') }}">
    
    <style>
        :root {
            --unasam-navy: #003366;
            --esat-blue: #007bff;
        }
        body { background-color: #f4f6f9; }
        .activation-card { border-radius: 15px !important; }
        .btn-navy { background-color: var(--unasam-navy); color: white; }
        .btn-navy:hover { background-color: #002244; color: white; }
        .user-avatar {
            width: 60px;
            height: 60px;
            background-color: #e8f4fd;
            color: var(--unasam-navy);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin: 0 auto 15px auto;
            font-weight: bold;
        }
    </style>
</head>

<body class="hold-transition login-page">
    <div class="login-box" style="width: 400px;">
        <div class="card card-outline card-primary activation-card shadow-lg border-0">
            <div class="card-header text-center border-0 pt-4 pb-0">
                <a href="{{ url('/') }}" class="d-flex align-items-center justify-content-center text-decoration-none">
                    <img src="{{ asset('dist/img/LOGO_UNASAM-removebg-preview.png') }}" 
                         alt="Logo UNASAM" 
                         style="height: 50px; width: auto; margin-right: 12px;">
                    <span class="h3 font-weight-bold m-0" style="color: var(--unasam-navy);">
                        ESAT<span style="color: var(--esat-blue);">-</span>UNASAM
                    </span>
                </a>
            </div>
            
            <div class="card-body p-4">
                <div class="text-center mb-4">
                    <div class="user-avatar">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <h5 class="font-weight-bold text-dark mb-1">¡Hola, {{ $user->name }}!</h5>
                    <p class="text-muted small">Establece tu contraseña para activar tu cuenta en la plataforma.</p>
                </div>

                {{-- Caja informativa del correo --}}
                <div class="alert alert-light border p-2 mb-4 text-center small text-muted" style="border-radius: 8px;">
                    <i class="fas fa-envelope mr-1 text-info"></i> <strong>Correo institucional:</strong><br>{{ $user->email }}
                </div>

                <form method="POST" action="{{ route('activation.process', $token) }}">
                    @csrf
                    
                    <div class="form-group mb-3">
                        <label for="password" class="font-weight-bold text-dark">Nueva Contraseña <span class="text-danger">*</span></label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" 
                               name="password" required autocomplete="new-password" autofocus
                               placeholder="Mínimo 8 caracteres" style="border-radius: 8px;">
                        @error('password')
                            <span class="invalid-feedback"><strong>{{ $message }}</strong></span>
                        @enderror
                    </div>

                    <div class="form-group mb-4">
                        <label for="password_confirmation" class="font-weight-bold text-dark">Confirmar Contraseña <span class="text-danger">*</span></label>
                        <input id="password_confirmation" type="password" class="form-control" 
                               name="password_confirmation" required autocomplete="new-password"
                               placeholder="Repita la contraseña" style="border-radius: 8px;">
                    </div>

                    <button type="submit" class="btn btn-navy btn-block font-weight-bold py-2" style="border-radius: 8px;">
                        <i class="fas fa-user-check mr-2"></i> Activar cuenta e ingresar
                    </button>
                </form>

                <div class="text-center mt-3">
                    <a href="{{ route('login') }}" class="text-decoration-none text-muted small">
                        <i class="fas fa-arrow-left mr-1"></i> Volver al inicio de sesión
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('dist/js/adminlte.min.js') }}"></script>
</body>
</html>
