<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Publicaciones Científicas - UNASAM</title>
    <style>
        /* Configuración de la página en formato A4 Horizontal (Landscape) */
        @page {
            size: A4 landscape;
            margin: 1.2cm 1.5cm 1.5cm 1.5cm;
        }
        
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333333;
            font-size: 9.5px;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }

        /* Encabezado Institucional */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        .header-table td {
            border: none;
            padding: 0;
            vertical-align: middle;
        }
        
        .logo-col {
            width: 12%;
            text-align: left;
        }
        
        .logo-img {
            height: 65px;
            width: auto;
        }
        
        .title-col {
            width: 88%;
            text-align: left;
            padding-left: 15px;
        }
        
        .university-name {
            font-size: 14px;
            font-weight: bold;
            color: #071444; /* Azul marino institucional */
            text-transform: uppercase;
            margin: 0;
            letter-spacing: 0.5px;
        }
        
        .vice-name {
            font-size: 11px;
            font-weight: bold;
            color: #2A7BCA; /* Azul secundario */
            margin: 2px 0 0 0;
            letter-spacing: 0.3px;
        }
        
        .system-name {
            font-size: 9px;
            font-weight: bold;
            color: #666666;
            margin: 1px 0 0 0;
        }
        
        .report-title {
            font-size: 11px;
            font-weight: bold;
            color: #c69214; /* Dorado institucional */
            margin: 4px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Línea divisoria decorativa institucional */
        .divider {
            height: 3px;
            background-color: #071444;
            margin-bottom: 3px;
            border-radius: 2px;
        }
        
        .sub-divider {
            height: 1px;
            background-color: #c69214;
            margin-bottom: 15px;
            border-radius: 1px;
        }

        /* Metadatos del Reporte */
        .metadata-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 8.5px;
            color: #555555;
        }
        
        .metadata-table td {
            padding: 2px 0;
            border: none;
        }
        
        .metadata-label {
            font-weight: bold;
            color: #333333;
        }

        /* Tabla de Datos Principal */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        
        .data-table th {
            background-color: #071444;
            color: #ffffff;
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            padding: 8px 6px;
            border: 1px solid #071444;
            text-align: left;
        }
        
        .data-table td {
            padding: 7px 6px;
            border: 1px solid #dddddd;
            vertical-align: top;
            word-wrap: break-word;
        }
        
        /* Cebra para filas alternas */
        .data-table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        /* Bordes y clases utilitarias */
        .text-center {
            text-align: center !important;
        }
        
        .text-right {
            text-align: right !important;
        }
        
        .font-bold {
            font-weight: bold;
        }

        /* Estilos de las celdas de datos */
        .col-index { width: 3%; text-align: center; }
        .col-titulo { width: 20%; font-weight: bold; color: #071444; }
        .col-autores { width: 16%; }
        .col-revista { width: 12%; }
        .col-anio { width: 5%; text-align: center; }
        .col-area { width: 10%; }
        .col-grupo { width: 10%; }
        .col-tipo { width: 8%; text-align: center; }
        .col-keywords { width: 16%; }

        .badge {
            display: inline-block;
            padding: 2px 5px;
            font-size: 8px;
            font-weight: bold;
            color: #ffffff;
            background-color: #777777;
            border-radius: 3px;
            text-align: center;
        }
        
        .badge-tipo {
            background-color: #2a7bca;
        }

        /* Pie de Página (Fijo en todas las páginas) */
        footer {
            position: fixed;
            bottom: -0.8cm;
            left: 0px;
            right: 0px;
            height: 0.8cm;
            border-top: 1px solid #eeeeee;
            padding-top: 4px;
            font-size: 7.5px;
            color: #777777;
        }
        
        .footer-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .footer-table td {
            border: none;
            padding: 0;
        }
        
        .page-number:after {
            content: "Pág. " counter(page);
        }
    </style>
</head>
<body>

    @php
        // Convertimos el logotipo institucional a base64 para garantizar su correcta renderización local en DomPDF
        $logoPath = public_path('dist/img/LOGO_UNASAM-removebg-preview.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }
    @endphp

    <!-- Encabezado Institucional -->
    <table class="header-table">
        <tr>
            @if(!empty($logoBase64))
                <td class="logo-col">
                    <img src="{{ $logoBase64 }}" alt="Logo UNASAM" class="logo-img">
                </td>
            @endif
            <td class="title-col">
                <h1 class="university-name">Universidad Nacional Santiago Antúnez de Mayolo</h1>
                <h2 class="vice-name">Vicerrectorado de Investigación</h2>
                <div class="system-name">Sistema de Gestión de Investigaciones (SIS-GESTIÓN)</div>
                <h3 class="report-title">Reporte de Publicaciones Científicas Registradas</h3>
            </td>
        </tr>
    </table>

    <!-- Líneas divisoras decorativas -->
    <div class="divider"></div>
    <div class="sub-divider"></div>

    <!-- Metadatos de la descarga -->
    <table class="metadata-table">
        <tr>
            <td style="width: 50%;">
                <span class="metadata-label">Fecha de Generación:</span> {{ now()->format('d/m/Y h:i A') }}
            </td>
            <td style="width: 50%; text-align: right;">
                @if(!empty($filtros['tipo']) || !empty($filtros['q']))
                    <span class="metadata-label">Filtros Aplicados:</span>
                    @if(!empty($filtros['tipo']))
                        [Tipo: {{ $filtros['tipo'] }}]
                    @endif
                    @if(!empty($filtros['q']))
                        [Búsqueda: "{{ $filtros['q'] }}"]
                    @endif
                @else
                    <span class="metadata-label">Filtros Aplicados:</span> Ninguno (Reporte General)
                @endif
            </td>
        </tr>
        <tr>
            <td>
                <span class="metadata-label">Total de Publicaciones:</span> {{ count($publicaciones) }} registros
            </td>
            <td style="text-align: right;">
                <span class="metadata-label">Generado por:</span> {{ auth()->user()->name ?? 'Usuario Autenticado' }}
            </td>
        </tr>
    </table>

    <!-- Tabla Principal de Datos -->
    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 3%;">N°</th>
                <th style="width: 20%;">Título de la Publicación</th>
                <th style="width: 16%;">Autores</th>
                <th style="width: 12%;">Revista</th>
                <th class="text-center" style="width: 5%;">Año</th>
                <th style="width: 10%;">Área de Investigación</th>
                <th style="width: 10%;">Grupo de Investigación</th>
                <th class="text-center" style="width: 8%;">Tipo</th>
                <th style="width: 16%;">Palabras Clave</th>
            </tr>
        </thead>
        <tbody>
            @forelse($publicaciones as $index => $pub)
                <tr>
                    <td class="col-index">{{ $index + 1 }}</td>
                    <td class="col-titulo">{{ $pub->titulo }}</td>
                    <td class="col-autores">{{ $pub->autores ?? '—' }}</td>
                    <td class="col-revista">{{ $pub->revista ?? '—' }}</td>
                    <td class="col-anio">{{ $pub->anio_publicacion ?? '—' }}</td>
                    <td class="col-area">{{ $pub->area ?? '—' }}</td>
                    <td class="col-grupo">{{ $pub->grupo ?? '—' }}</td>
                    <td class="col-tipo">
                        <span class="badge badge-tipo">{{ $pub->tipo ?? 'Sin tipo' }}</span>
                    </td>
                    <td class="col-keywords" style="font-size: 8.5px;">{{ $pub->keywords ?? '—' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #777777;">
                        No se encontraron publicaciones científicas registradas que coincidan con los criterios de búsqueda.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Pie de Página Fijo -->
    <footer>
        <table class="footer-table">
            <tr>
                <td style="width: 40%; text-align: left;">
                    Universidad Nacional Santiago Antúnez de Mayolo - Ancash, Perú
                </td>
                <td style="width: 30%; text-align: center; font-weight: bold;">
                    CONFIDENCIAL - REPORTE INSTITUCIONAL
                </td>
                <td class="page-number" style="width: 30%; text-align: right;">
                    <!-- Rellenado automáticamente por DomPDF -->
                </td>
            </tr>
        </table>
    </footer>

</body>
</html>
