<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.4;
            color: #333;
        }
        .header {
            border-bottom: 2px solid #000;
            margin-bottom: 15px;
            padding-bottom: 10px;
        }
        .header-left {
            float: left;
            width: 50%;
        }
        .header-right {
            float: right;
            width: 50%;
            text-align: right;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            font-weight: bold;
        }
        .header p {
            margin: 2px 0;
            font-size: 11px;
        }
        .header-right h2 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #333;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin: 15px 0 8px 0;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        .subsection-title {
            font-size: 12px;
            font-weight: bold;
            margin: 10px 0 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        table.info td {
            padding: 5px;
            border: none;
            font-size: 11px;
        }
        table.bordered {
            border: 1px solid #000;
        }
        table.bordered th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-weight: bold;
            font-size: 11px;
        }
        table.bordered td {
            border: 1px solid #000;
            padding: 8px;
            font-size: 11px;
            text-align: center;
        }
        .clearfix {
            clear: both;
        }
        p {
            margin: 5px 0;
            font-size: 11px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #ccc;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    {{-- Encabezado --}}
    <div class="header">
        <div class="header-left">
            @if($logo)
                <img src="{{ $logo }}" style="max-height: 80px; margin-bottom: 10px;">
            @endif
            <h1>CIMEVET</h1>
            <p><strong>{{ $nombre_negocio }}</strong></p>
            <p>{{ $direccion }}</p>
            <p>{{ $ruc }}</p>
            <p>Teléfono: {{ $telefono }}</p>
        </div>
        <div class="header-right">
            <h2>VACUNA</h2>
        </div>
        <div class="clearfix"></div>
    </div>

    {{-- Datos del Propietario --}}
    <div class="section-title">DATOS DEL PROPIETARIO</div>
    <table class="info">
        <tr>
            <td width="33%"><strong>Propietario:</strong> {{ $cliente->nombre }} {{ $cliente->apellido }}</td>
            <td width="33%"><strong>Teléfono:</strong> {{ $cliente->celular ?? '-' }}</td>
            <td width="34%"><strong>Dirección:</strong> {{ $cliente->direccion ?? '-' }}</td>
        </tr>
    </table>

    {{-- Datos de la Mascota --}}
    <div class="section-title">DATOS DE LA MASCOTA</div>
    <table class="info">
        <tr>
            <td width="25%"><strong>Paciente:</strong> {{ $mascota->nombre }}</td>
            <td width="25%"><strong>Raza:</strong> {{ $mascota->raza }}</td>
            <td width="25%"><strong>¿Esterilizado?:</strong> {{ $mascota->esterilizado == 1 || $mascota->esterilizado === true || $mascota->esterilizado === 'Si' ? 'Sí' : 'No' }}</td>
            <td width="25%"><strong>Edad:</strong> {{ $mascota->edad_completa }}</td>
        </tr>
        <tr>
            <td width="25%"><strong>Especie:</strong> {{ strtoupper($mascota->especie) }}</td>
            <td width="25%"><strong>Sexo:</strong> {{ $mascota->genero }}</td>
            <td colspan="2"><strong>Fecha de Nacimiento:</strong> {{ $mascota->fecha_nacimiento ? \Carbon\Carbon::parse($mascota->fecha_nacimiento)->format('d-m-Y') : 'N/A' }}</td>
        </tr>
    </table>

    {{-- Datos de la Vacuna --}}
    <div class="section-title">VACUNA</div>
    <table class="info">
        <tr>
            <td width="50%"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y H:i') }}</td>
            <td width="50%"><strong>Peso (kg):</strong> {{ $registro->peso ?? '-' }} kg</td>
        </tr>
    </table>

    {{-- Constantes Fisiológicas --}}
    <div class="subsection-title">Constantes Fisiológicas</div>
    <table class="bordered">
        <thead>
            <tr>
                <th width="25%">Temperatura (°C)</th>
                <th width="25%">Peso (kg)</th>
                <th width="25%">Frecuencia Cardiaca</th>
                <th width="25%">Hidratación</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $registro->temperatura ?? '-' }} °C</td>
                <td>{{ $registro->peso ?? '-' }} kg</td>
                <td>{{ $registro->frecuencia_cardiaca ?? '-' }} bpm</td>
                <td>{{ $registro->hidratacion ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Información de la Vacuna --}}
    @if($registro->vacuna_aplicada)
        <div class="subsection-title">Vacuna Aplicada</div>
        <p><strong>{{ $registro->vacuna_aplicada }}</strong></p>
    @endif

    @if($registro->anamnesis)
        <div class="subsection-title">Anamnesis</div>
        <p>{{ $registro->anamnesis }}</p>
    @endif

    @if($registro->laboratorio)
        <div class="subsection-title">Laboratorio</div>
        <p>{{ $registro->laboratorio }}</p>
    @endif

    @if($registro->lote)
        <p><strong>Lote:</strong> {{ $registro->lote }}</p>
    @endif

    @if($registro->vencimiento)
        <p><strong>Vencimiento:</strong> {{ \Carbon\Carbon::parse($registro->vencimiento)->format('d/m/Y') }}</p>
    @endif

    @if($registro->via_administracion)
        <p><strong>Vía de Administración:</strong> {{ $registro->via_administracion }}</p>
    @endif

    @if($registro->dosis)
        <p><strong>Dosis:</strong> {{ $registro->dosis }}</p>
    @endif

    @if($registro->proxima_dosis)
        <p><strong>Próxima Dosis:</strong> {{ \Carbon\Carbon::parse($registro->proxima_dosis)->format('d/m/Y') }}</p>
    @endif

    @if($registro->tipo_proxima_vacuna)
        <p><strong>Tipo Próxima Vacuna:</strong> {{ $registro->tipo_proxima_vacuna }}</p>
    @endif

    @if($registro->observaciones)
        <div class="subsection-title">Observaciones</div>
        <p>{{ $registro->observaciones }}</p>
    @endif

    {{-- Pie de página --}}
    <div class="footer">
        <p>Documento generado automáticamente por el sistema de gestión veterinaria VetApp</p>
        <p>Fecha de impresión: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
