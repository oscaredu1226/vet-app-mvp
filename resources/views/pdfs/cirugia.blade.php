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
            <h2>CIRUGÍA</h2>
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

    {{-- Datos de la Cirugía --}}
    <div class="section-title">CIRUGÍA</div>
    <table class="info">
        <tr>
            <td width="50%"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($registro->fecha)->format('d/m/Y H:i') }}</td>
            <td width="50%"><strong>Tipo de Cirugía:</strong> {{ $registro->tipo_cirugia ?? '-' }}</td>
        </tr>
    </table>

    {{-- Diagnóstico --}}
    @if($registro->diagnostico)
        <div class="subsection-title">Diagnóstico Preoperatorio</div>
        <p>{{ $registro->diagnostico }}</p>
    @endif

    {{-- Médicos --}}
    <div class="subsection-title">Médicos</div>
    @php
        $cirujanos = [];
        $anestesistas = [];
        
        if ($registro->cirujanos) {
            $cirujanos = is_string($registro->cirujanos) ? json_decode($registro->cirujanos, true) : $registro->cirujanos;
            if (!is_array($cirujanos)) {
                $cirujanos = [];
            }
        }
        
        if ($registro->anestesistas) {
            $anestesistas = is_string($registro->anestesistas) ? json_decode($registro->anestesistas, true) : $registro->anestesistas;
            if (!is_array($anestesistas)) {
                $anestesistas = [];
            }
        }
    @endphp

    <table class="info">
        <tr>
            <td width="50%">
                <strong>Cirujano(s):</strong>
                <table class="bordered" style="margin-top: 5px;">
                    <thead>
                        <tr>
                            <th width="50%">Nombre</th>
                            <th width="25%">Cargo</th>
                            <th width="25%">CMV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($cirujanos))
                            @foreach($cirujanos as $cirujano)
                                <tr>
                                    <td>{{ is_array($cirujano) ? ($cirujano['nombre'] ?? '-') : $cirujano }}</td>
                                    <td>{{ is_array($cirujano) ? ($cirujano['cargo'] ?? '-') : '-' }}</td>
                                    <td>{{ is_array($cirujano) ? ($cirujano['cmv'] ?? '-') : '-' }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" style="text-align: center;">-</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </td>
            <td width="50%">
                <strong>Anestesista(s):</strong>
                <table class="bordered" style="margin-top: 5px;">
                    <thead>
                        <tr>
                            <th width="50%">Nombre</th>
                            <th width="25%">Cargo</th>
                            <th width="25%">CMV</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(!empty($anestesistas))
                            @foreach($anestesistas as $anestesista)
                                <tr>
                                    <td>{{ is_array($anestesista) ? ($anestesista['nombre'] ?? '-') : $anestesista }}</td>
                                    <td>{{ is_array($anestesista) ? ($anestesista['cargo'] ?? '-') : '-' }}</td>
                                    <td>{{ is_array($anestesista) ? ($anestesista['cmv'] ?? '-') : '-' }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" style="text-align: center;">-</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    {{-- Constantes Pre-Quirúrgicas --}}
    <div class="subsection-title">Constantes Pre-Quirúrgicas</div>
    <table class="bordered">
        <thead>
            <tr>
                <th width="20%">Temperatura (°C)</th>
                <th width="20%">Peso (kg)</th>
                <th width="20%">FC (bpm)</th>
                <th width="20%">FR (rpm)</th>
                <th width="20%">Hidratación</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $registro->temperatura_pre ?? '-' }} °C</td>
                <td>{{ $registro->peso_pre ?? '-' }} kg</td>
                <td>{{ $registro->fc_pre ?? '-' }} bpm</td>
                <td>{{ $registro->fr_pre ?? '-' }} rpm</td>
                <td>{{ $registro->hidratacion_pre ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Tratamiento Intraoperatorio --}}
    @if($registro->tratamiento_aplicado)
        <div class="subsection-title">Tratamiento Intraoperatorio</div>
        @php
            $tratamientos = [];
            $lineas = explode('\n', $registro->tratamiento_aplicado);
            
            foreach ($lineas as $linea) {
                $linea = trim($linea);
                if (!empty($linea)) {
                    // Formato: "Medicamento: XXX, Dosis: YYY, Vía: ZZZ, Volumen: WWW"
                    preg_match('/Medicamento:\s*(.+?),\s*Dosis:\s*(.+?)(?:,\s*Vía:\s*(.+?))?(?:,\s*Volumen:\s*(.+))?$/', $linea, $matches);
                    
                    if (!empty($matches)) {
                        $tratamientos[] = [
                            'medicamento' => trim($matches[1] ?? ''),
                            'dosis' => trim($matches[2] ?? ''),
                            'via' => trim($matches[3] ?? ''),
                            'volumen' => trim($matches[4] ?? '')
                        ];
                    }
                }
            }
        @endphp
        
        <table class="bordered">
            <thead>
                <tr>
                    <th width="30%">Medicamento</th>
                    <th width="23%">Dosis</th>
                    <th width="23%">Vía</th>
                    <th width="24%">Volumen</th>
                </tr>
            </thead>
            <tbody>
                @if(!empty($tratamientos))
                    @foreach($tratamientos as $tratamiento)
                        <tr>
                            <td>{{ $tratamiento['medicamento'] ?? '-' }}</td>
                            <td>{{ $tratamiento['dosis'] ?? '-' }}</td>
                            <td>{{ $tratamiento['via'] ?? '-' }}</td>
                            <td>{{ $tratamiento['volumen'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4" style="text-align: center;">-</td>
                    </tr>
                @endif
            </tbody>
        </table>
    @endif

    {{-- Constantes Post-Quirúrgicas --}}
    <div class="subsection-title">Constantes Post-Quirúrgicas</div>
    <table class="bordered">
        <thead>
            <tr>
                <th width="20%">Temperatura (°C)</th>
                <th width="20%">Peso (kg)</th>
                <th width="20%">FC (bpm)</th>
                <th width="20%">FR (rpm)</th>
                <th width="20%">Hidratación</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $registro->temperatura_post ?? '-' }} °C</td>
                <td>{{ $registro->peso_post ?? '-' }} kg</td>
                <td>{{ $registro->fc_post ?? '-' }} bpm</td>
                <td>{{ $registro->fr_post ?? '-' }} rpm</td>
                <td>{{ $registro->hidratacion_post ?? '-' }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Observaciones --}}
    @if($registro->observaciones)
        <div class="subsection-title">Observaciones</div>
        <p>{{ $registro->observaciones }}</p>
    @endif

    {{-- Anamnesis (Cuidados Post-quirúrgicos) --}}
    @if($registro->anamnesis)
        <div class="subsection-title">Cuidados Post-Operatorios</div>
        <p>{{ $registro->anamnesis }}</p>
    @endif

    {{-- Pie de página --}}
    <div class="footer">
        <p>Documento generado automáticamente por el sistema de gestión veterinaria VetApp</p>
        <p>Fecha de impresión: {{ now()->format('d/m/Y H:i') }}</p>
    </div>
</body>
</html>
