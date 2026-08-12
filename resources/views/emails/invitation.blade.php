<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitación a ESAT-UNASAM</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            color: #333333;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: none;
            -ms-text-size-adjust: none;
        }
        .wrapper {
            width: 100%;
            background-color: #f4f6f9;
            padding: 40px 0;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #e1e5eb;
        }
        .header {
            background-color: #003366;
            padding: 30px;
            text-align: center;
        }
        .logo-text {
            color: #ffffff;
            font-size: 24px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 1px;
        }
        .logo-text span {
            color: #007bff;
        }
        .content {
            padding: 40px 30px;
            line-height: 1.6;
        }
        .greeting {
            font-size: 20px;
            font-weight: 700;
            color: #003366;
            margin-top: 0;
            margin-bottom: 15px;
        }
        .text {
            font-size: 16px;
            color: #555555;
            margin-bottom: 25px;
        }
        .info-box {
            background-color: #f8f9fa;
            border-left: 4px solid #007bff;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 0 8px 8px 0;
        }
        .info-item {
            font-size: 15px;
            margin-bottom: 8px;
            color: #444444;
        }
        .info-item:last-child {
            margin-bottom: 0;
        }
        .info-label {
            font-weight: bold;
            color: #003366;
        }
        .cta-container {
            text-align: center;
            margin: 35px 0;
        }
        .btn {
            background-color: #003366;
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 30px;
            font-size: 16px;
            font-weight: bold;
            border-radius: 8px;
            display: inline-block;
            box-shadow: 0 4px 6px rgba(0, 51, 102, 0.2);
            transition: background-color 0.2s ease;
        }
        .btn:hover {
            background-color: #002244;
        }
        .notice {
            font-size: 13px;
            color: #777777;
            border-top: 1px solid #eeeeee;
            padding-top: 20px;
            margin-top: 30px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #888888;
            border-top: 1px solid #eeeeee;
        }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <div class="logo-text">ESAT<span>-</span>UNASAM</div>
            </div>
            <div class="content">
                <h2 class="greeting">¡Hola, {{ $user->name }}!</h2>
                <p class="text">
                    Has sido registrado por el administrador en la plataforma de gestión del repositorio de investigaciones **ESAT-UNASAM**.
                </p>
                
                <div class="info-box">
                    <div class="info-item">
                        <span class="info-label">Usuario / Correo:</span> {{ $user->email }}
                    </div>
                    <div class="info-item">
                        <span class="info-label">Rol asignado:</span> 
                        @if($user->rol === 'super_admin')
                            Súper Administrador
                        @elseif($user->rol === 'publicador')
                            Publicador (Crear y editar)
                        @else
                            Espectador (Solo lectura)
                        @endif
                    </div>
                </div>

                <p class="text">
                    Para comenzar a utilizar tu cuenta, es necesario que la actives y establezcas tu contraseña de seguridad haciendo clic en el siguiente botón:
                </p>

                <div class="cta-container">
                    <a href="{{ $activationUrl }}" class="btn">Activar mi cuenta</a>
                </div>

                <div class="notice">
                    <p><strong>Nota de seguridad:</strong> Este enlace de activación es de un solo uso y expirará en 24 horas por motivos de seguridad.</p>
                    <p>Si el botón no funciona, copia y pega la siguiente URL en tu navegador:</p>
                    <p style="word-break: break-all; color: #007bff;">{{ $activationUrl }}</p>
                </div>
            </div>
            <div class="footer">
                &copy; {{ date('Y') }} ESAT-UNASAM. Todos los derechos reservados.<br>
                Huaraz, Ancash, Perú.
            </div>
        </div>
    </div>
</body>
</html>
