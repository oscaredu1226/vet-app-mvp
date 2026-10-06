@extends('layouts.app')

@section('content')
<div class="container-fluid px-4">
    {{-- Encabezado --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">
                <i class="fas fa-file-medical text-primary me-2"></i>
                Historia Clínica - {{ $mascota->nombre }}
            </h2>
            <p class="text-muted mb-0">
                <i class="fas fa-user me-2"></i>
                <strong>Propietario:</strong> {{ strtoupper($mascota->cliente->nombre ?? 'N/A') }} {{ strtoupper($mascota->cliente->apellido ?? '') }}
            </p>
        </div>
        <a href="{{ route('mascotas.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>

    {{-- Layout principal con sidebar y contenido --}}
    <div class="row">
        {{-- Sidebar izquierdo con información de la mascota --}}
        <div class="col-lg-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    {{-- Ícono de mascota --}}
                    <div class="mb-3">
                        <i class="fas fa-paw fa-4x text-primary"></i>
                    </div>
                    
                    {{-- Nombre --}}
                    <h4 class="mb-2">{{ $mascota->nombre }}</h4>
                    
                    {{-- Badge de especie --}}
                    <span class="badge bg-success mb-3">{{ $mascota->especie }}</span>
                    
                    {{-- Información detallada --}}
                    <div class="text-start small">
                        <p class="mb-1"><strong>Raza:</strong> {{ $mascota->raza }}</p>
                        <p class="mb-1"><strong>Sexo:</strong> {{ $mascota->genero }}</p>
                        <p class="mb-1"><strong>¿Esterilizado?:</strong> {{ $mascota->esterilizado ?? 'No' }}</p>
                        <p class="mb-1"><strong>Fecha de nacimiento:</strong> {{ \Carbon\Carbon::parse($mascota->fecha_nacimiento)->format('d-m-Y') }}</p>
                        <p class="mb-1">
                            <strong>Edad:</strong> {{ $mascota->edad_completa }}
                        </p>
                        <hr>
                        <p class="mb-1">
                            <i class="fab fa-whatsapp text-success me-1"></i>
                            <strong>Propietario:</strong>
                        </p>
                        <p class="text-primary mb-0 small">{{ strtoupper($mascota->cliente->nombre ?? 'N/A') }} {{ strtoupper($mascota->cliente->apellido ?? '') }}</p>
                    </div>
                    
                    {{-- Botones de acción --}}
                    <div class="mt-3">
                        <button type="button" class="btn btn-primary btn-sm w-100 mb-2" data-bs-toggle="modal" data-bs-target="#nuevoEventoModal">
                            <i class="fas fa-calendar-plus me-1"></i> Nuevo Evento
                        </button>

                        <div class="dropdown mb-2">
                            <button class="btn btn-success btn-sm w-100 dropdown-toggle" type="button" id="dropdownNuevoRegistro" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-plus-circle me-1"></i> NUEVO REGISTRO
                            </button>
                            <ul class="dropdown-menu w-100" aria-labelledby="dropdownNuevoRegistro">
                                <li>
                                    <a class="dropdown-item" href="{{ route('consultas.create', $mascota->id_mascota) }}">
                                        <i class="fas fa-stethoscope text-primary me-2"></i> Consulta
                                    </a>
                                </li>
                                {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<li>
                                    <a class="dropdown-item" href="{{ route('vacunas.create', $mascota->id_mascota) }}">
                                        <i class="fas fa-syringe text-success me-2"></i> Vacuna
                                    </a>
                                </li>
MVP_POSTERIOR: END Atencion especializada --}}
                                {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<li>
                                    <a class="dropdown-item" href="{{ route('desparasitaciones.create', $mascota->id_mascota) }}">
                                        <i class="fas fa-pills text-warning me-2"></i> Desparasitación
                                    </a>
                                </li>
MVP_POSTERIOR: END Atencion especializada --}}
                                {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<li>
                                    <a class="dropdown-item" href="{{ route('antipulgas.create', $mascota->id_mascota) }}">
                                        <i class="fas fa-bug text-info me-2"></i> Antipulgas
                                    </a>
                                </li>
MVP_POSTERIOR: END Atencion especializada --}}
                                {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<li>
                                    <a class="dropdown-item" href="{{ route('cirugias.create', $mascota->id_mascota) }}">
                                        <i class="fas fa-scalpel text-danger me-2"></i> Cirugía
                                    </a>
                                </li>
MVP_POSTERIOR: END Atencion especializada --}}
                            </ul>
                        </div>
                        
                        {{-- Botón Terminar Atención (solo si está en cola médica) --}}
                        {{-- MVP_POSTERIOR: BEGIN Terminar atencion de cola
<button type="button" class="btn btn-danger btn-sm w-100" id="btnTerminarAtencion" style="display: none;" onclick="terminarAtencion({{ $mascota->id_mascota }})">
                            <i class="fas fa-stop-circle me-1"></i> TERMINAR ATENCIÓN
                        </button>
MVP_POSTERIOR: END Terminar atencion de cola --}}
                    </div>
                </div>
            </div>
        </div>

        {{-- Contenido principal --}}
        <div class="col-lg-9">
            {{-- Tarjetas de estadísticas --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-sm-4 col-lg">
                    <a href="{{ route('consultas.create', $mascota->id_mascota) }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm text-white bg-primary h-100">
                            <div class="card-body text-center py-3 py-lg-4">
                                <i class="fas fa-stethoscope fa-2x mb-2"></i>
                                <h6 class="mb-1 text-truncate">Consultas</h6>
                                <h2 class="mb-0">{{ $mascota->consultas->count() }}</h2>
                            </div>
                        </div>
                    </a>
                </div>
                {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<div class="col-6 col-sm-4 col-lg">
                    <a href="{{ route('vacunas.create', $mascota->id_mascota) }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm text-white bg-success h-100">
                            <div class="card-body text-center py-3 py-lg-4">
                                <i class="fas fa-syringe fa-2x mb-2"></i>
                                <h6 class="mb-1 text-truncate">Vacunas</h6>
                                <h2 class="mb-0">{{ $mascota->vacunas->count() }}</h2>
                            </div>
                        </div>
                    </a>
                </div>
MVP_POSTERIOR: END Atencion especializada --}}
                {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<div class="col-6 col-sm-4 col-lg">
                    <a href="{{ route('desparasitaciones.create', $mascota->id_mascota) }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm text-white bg-warning h-100">
                            <div class="card-body text-center py-3 py-lg-4">
                                <i class="fas fa-pills fa-2x mb-2"></i>
                                <h6 class="mb-1 text-truncate">Desparasit.</h6>
                                <h2 class="mb-0">{{ $mascota->desparasitaciones->count() }}</h2>
                            </div>
                        </div>
                    </a>
                </div>
MVP_POSTERIOR: END Atencion especializada --}}
                {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<div class="col-6 col-sm-4 col-lg">
                    <a href="{{ route('antipulgas.create', $mascota->id_mascota) }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm text-white bg-info h-100">
                            <div class="card-body text-center py-3 py-lg-4">
                                <i class="fas fa-bug fa-2x mb-2"></i>
                                <h6 class="mb-1 text-truncate">Antipulgas</h6>
                                <h2 class="mb-0">{{ $mascota->antipulgas->count() }}</h2>
                            </div>
                        </div>
                    </a>
                </div>
MVP_POSTERIOR: END Atencion especializada --}}
                {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<div class="col-6 col-sm-4 col-lg">
                    <a href="{{ route('cirugias.create', $mascota->id_mascota) }}" class="text-decoration-none">
                        <div class="card border-0 shadow-sm text-white bg-danger h-100">
                            <div class="card-body text-center py-3 py-lg-4">
                                <i class="fas fa-scalpel fa-2x mb-2"></i>
                                <h6 class="mb-1 text-truncate">Cirugías</h6>
                                <h2 class="mb-0">{{ $mascota->cirugias->count() }}</h2>
                            </div>
                        </div>
                    </a>
                </div>
MVP_POSTERIOR: END Atencion especializada --}}
            </div>

            {{-- Historial Médico --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>
                        Historial Médico
                    </h5>
                    <p class="mb-0 small text-muted"><strong>Especie:</strong> {{ strtoupper($mascota->especie) }}</p>
                </div>
                <div class="card-body">
                    @if($timeline->isEmpty())
                        <div class="text-center py-5">
                            <i class="fas fa-clipboard-list fa-4x text-muted mb-3"></i>
                            <h4>No hay registros médicos</h4>
                            <p class="text-muted">{{-- MVP_POSTERIOR: BEGIN Texto de la version completa
Comienza agregando una consulta, vacuna o tratamiento.
MVP_POSTERIOR: END Texto de la version completa --}}Comienza agregando una consulta.</p>
                        </div>
                    @else
                        <div class="timeline">
                            @foreach($timeline as $registro)
                                <div class="timeline-item mb-4">
                                    <div class="row">
                                        <div class="col-md-2 text-muted">
                                            <small>
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ \Carbon\Carbon::parse($registro['fecha'])->format('d/m/Y') }}
                                            </small>
                                        </div>
                                        <div class="col-md-10">
                                            <div class="card border-start border-4 border-{{ 
                                                $registro['tipo'] == 'Consulta' ? 'primary' : 
                                                ($registro['tipo'] == 'Vacuna' ? 'success' : 
                                                ($registro['tipo'] == 'Desparasitación' ? 'warning' : 
                                                ($registro['tipo'] == 'Cirugía' ? 'danger' : 'info'))) 
                                            }}">
                                                <div class="card-body">
                                                    <h6 class="card-subtitle mb-2">
                                                        <span class="badge bg-{{ 
                                                            $registro['tipo'] == 'Consulta' ? 'primary' : 
                                                            ($registro['tipo'] == 'Vacuna' ? 'success' : 
                                                            ($registro['tipo'] == 'Desparasitación' ? 'warning' : 
                                                            ($registro['tipo'] == 'Cirugía' ? 'danger' : 'info'))) 
                                                        }}">
                                                            @if($registro['tipo'] == 'Cirugía')
                                                                <i class="fas fa-scalpel me-1"></i>
                                                            @endif
                                                            {{ $registro['tipo'] }}
                                                        </span>
                                                        @if(isset($registro['datos']->medico) && $registro['datos']->medico)
                                                            <span class="badge bg-dark ms-2">{{ $registro['datos']->medico }}</span>
                                                        @endif
                                                    </h6>
                                                    
                                                    @if($registro['tipo'] == 'Consulta')
                                                        <p class="mb-2"><strong>Motivo:</strong> {{ $registro['datos']->motivo ?? 'Consulta' }}</p>
                                                        @if($registro['datos']->anamnesis)
                                                            <p class="mb-2"><strong>Anamnesis:</strong> {{ $registro['datos']->anamnesis }}</p>
                                                        @endif
                                                        
                                                        @php
                                                            $tieneConstantes = $registro['datos']->peso || $registro['datos']->temperatura || 
                                                                             $registro['datos']->frecuencia_cardiaca || $registro['datos']->frecuencia_respiratoria || 
                                                                             $registro['datos']->tlc || $registro['datos']->hidratacion;
                                                        @endphp
                                                        
                                                        @if($tieneConstantes)
                                                            <div class="mb-3">
                                                                <strong class="d-block mb-2">Constantes fisiológicas</strong>
                                                                <div class="row g-2">
                                                                    @if($registro['datos']->temperatura)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Temperatura (°C)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->temperatura }} °C</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->peso)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Peso (kg)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->peso }} kg</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->frecuencia_cardiaca)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Frecuencia Cardiaca</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->frecuencia_cardiaca }} bpm</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->frecuencia_respiratoria)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Frecuencia Respiratoria</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->frecuencia_respiratoria }} rpm</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->hidratacion)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Hidratación</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->hidratacion }}</div>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if($registro['datos']->examen_fisico)
                                                            <p class="mb-1"><strong>Examen Físico:</strong> {{ $registro['datos']->examen_fisico }}</p>
                                                        @endif
                                                        @if($registro['datos']->diagnostico)
                                                            <p class="mb-1"><strong>Diagnóstico:</strong> {{ $registro['datos']->diagnostico }}</p>
                                                        @endif
                                                        @if($registro['datos']->examenes_auxiliares)
                                                            <p class="mb-1"><strong>Exámenes Auxiliares:</strong> {{ $registro['datos']->examenes_auxiliares }}</p>
                                                        @endif
                                                        @if($registro['datos']->plan_tratamiento)
                                                            <p class="mb-1"><strong>Plan de Tratamiento:</strong> {{ $registro['datos']->plan_tratamiento }}</p>
                                                        @endif
                                                        @if($registro['datos']->receta)
                                                            <p class="mb-1"><strong>Receta:</strong></p>
                                                            <div class="small ps-3">
                                                                @php
                                                                    // Los medicamentos están separados por "; "
                                                                    $lineas = explode(";", $registro['datos']->receta);
                                                                @endphp
                                                                @foreach($lineas as $linea)
                                                                    @if(trim($linea))
                                                                        @php
                                                                            // Formato esperado: "nombre - frecuencia (cantidad)"
                                                                            // Convertir a: "nombre - frecuencia - cantidad"
                                                                            $lineaLimpia = trim($linea);
                                                                            if (preg_match('/^(.+?)\s*-\s*(.+?)\s*\((\d+)\)$/', $lineaLimpia, $matches)) {
                                                                                $medicamento = trim($matches[1]);
                                                                                $frecuencia = trim($matches[2]);
                                                                                $cantidad = trim($matches[3]);
                                                                                echo "{$medicamento} - {$frecuencia} - {$cantidad}<br>";
                                                                            } else {
                                                                                // Si no coincide con el formato, mostrar tal cual
                                                                                echo $lineaLimpia . '<br>';
                                                                            }
                                                                        @endphp
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        @if($registro['datos']->proxima_cita)
                                                            <p class="mb-1"><strong>Próxima Cita:</strong> {{ \Carbon\Carbon::parse($registro['datos']->proxima_cita)->format('d/m/Y H:i') }}</p>
                                                        @endif

                                                    {{-- MVP_POSTERIOR: BEGIN Detalles de atenciones especializadas
@elseif($registro['tipo'] == 'Vacuna')
                                                        <p class="mb-2"><strong>Vacuna:</strong> {{ $registro['datos']->vacuna_aplicada ?? 'No especificada' }}</p>
                                                        @if($registro['datos']->anamnesis)
                                                            <p class="mb-2"><strong>Anamnesis:</strong> {{ $registro['datos']->anamnesis }}</p>
                                                        @endif
                                                        
                                                        @php
                                                            $tieneConstantesVacuna = $registro['datos']->peso || $registro['datos']->temperatura || 
                                                                                    $registro['datos']->hidratacion || $registro['datos']->tlc_tiempo_llenado;
                                                        @endphp
                                                        
                                                        @if($tieneConstantesVacuna)
                                                            <div class="mb-3">
                                                                <strong class="d-block mb-2">Constantes fisiológicas</strong>
                                                                <div class="row g-2">
                                                                    @if($registro['datos']->temperatura)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Temperatura (°C)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->temperatura }} °C</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->peso)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Peso (kg)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->peso }} kg</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->hidratacion)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Hidratación</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->hidratacion }}</div>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if($registro['datos']->laboratorio)
                                                            <p class="mb-1"><strong>Laboratorio:</strong> {{ $registro['datos']->laboratorio }}</p>
                                                        @endif
                                                        @if($registro['datos']->lote)
                                                            <p class="mb-1"><strong>Lote:</strong> {{ $registro['datos']->lote }}</p>
                                                        @endif
                                                        @if($registro['datos']->vencimiento)
                                                            <p class="mb-1"><strong>Vencimiento:</strong> {{ \Carbon\Carbon::parse($registro['datos']->vencimiento)->format('d/m/Y') }}</p>
                                                        @endif
                                                        @if($registro['datos']->via_administracion)
                                                            <p class="mb-1"><strong>Vía:</strong> {{ $registro['datos']->via_administracion }}</p>
                                                        @endif
                                                        @if($registro['datos']->dosis)
                                                            <p class="mb-1"><strong>Dosis:</strong> {{ $registro['datos']->dosis }}</p>
                                                        @endif
                                                        @if($registro['datos']->proxima_dosis)
                                                            <p class="mb-1"><strong>Próxima dosis:</strong> {{ \Carbon\Carbon::parse($registro['datos']->proxima_dosis)->format('d/m/Y') }}</p>
                                                        @endif
                                                        @if($registro['datos']->tipo_proxima_vacuna)
                                                            <p class="mb-1"><strong>Tipo próxima vacuna:</strong> {{ $registro['datos']->tipo_proxima_vacuna }}</p>
                                                        @endif
                                                    @elseif($registro['tipo'] == 'Desparasitación')
                                                        <p class="mb-2"><strong>Producto:</strong> {{ $registro['datos']->producto ?? 'No especificado' }}</p>
                                                        
                                                        @php
                                                            $tieneConstantesDesp = $registro['datos']->peso || $registro['datos']->temperatura || 
                                                                                  $registro['datos']->frecuencia_cardiaca || $registro['datos']->frecuencia_respiratoria || 
                                                                                  $registro['datos']->hidratacion;
                                                        @endphp
                                                        
                                                        @if($tieneConstantesDesp)
                                                            <div class="mb-3">
                                                                <strong class="d-block mb-2">Constantes fisiológicas</strong>
                                                                <div class="row g-2">
                                                                    @if($registro['datos']->temperatura)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Temperatura (°C)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->temperatura }} °C</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->peso)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Peso (kg)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->peso }} kg</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->frecuencia_cardiaca)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Frecuencia Cardiaca</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->frecuencia_cardiaca }} bpm</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->frecuencia_respiratoria)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Frecuencia Respiratoria</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->frecuencia_respiratoria }} rpm</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->hidratacion)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Hidratación</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->hidratacion }}</div>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if($registro['datos']->laboratorio)
                                                            <p class="mb-1"><strong>Laboratorio:</strong> {{ $registro['datos']->laboratorio }}</p>
                                                        @endif
                                                        @if($registro['datos']->dosis)
                                                            <p class="mb-1"><strong>Dosis:</strong> {{ $registro['datos']->dosis }}</p>
                                                        @endif
                                                        @if($registro['datos']->via_administracion)
                                                            <p class="mb-1"><strong>Vía:</strong> {{ $registro['datos']->via_administracion }}</p>
                                                        @endif
                                                        @if($registro['datos']->tipo_parasito)
                                                            <p class="mb-1"><strong>Tipo de parásito:</strong> {{ $registro['datos']->tipo_parasito }}</p>
                                                        @endif
                                                        @if($registro['datos']->proxima_dosis)
                                                            <p class="mb-1"><strong>Próxima dosis:</strong> {{ \Carbon\Carbon::parse($registro['datos']->proxima_dosis)->format('d/m/Y') }}</p>
                                                        @endif
                                                    @elseif($registro['tipo'] == 'Cirugía')
                                                        
MVP_POSTERIOR: END Detalles de atenciones especializadas --}}{{-- BLOQUE PARA MOSTRAR CIRUGÍAS (Formato mejorado) --}}{{-- MVP_POSTERIOR: BEGIN Detalles de atenciones especializadas

                                                        <p class="mb-2"><strong>Tipo de Cirugía:</strong> {{ $registro['datos']->tipo_cirugia }}</p>
                                                        
                                                        @if($registro['datos']->diagnostico)
                                                            <p class="mb-2"><strong>Diagnóstico:</strong> {{ $registro['datos']->diagnostico }}</p>
                                                        @endif

                                                        
MVP_POSTERIOR: END Detalles de atenciones especializadas --}}{{-- Constantes Pre-Quirúrgicas --}}{{-- MVP_POSTERIOR: BEGIN Detalles de atenciones especializadas

                                                        @php
                                                            $tieneConstantesPre = $registro['datos']->peso_pre || $registro['datos']->temperatura_pre || 
                                                                                 $registro['datos']->fc_pre || $registro['datos']->fr_pre;
                                                        @endphp
                                                        
                                                        @if($tieneConstantesPre)
                                                            <div class="mb-3">
                                                                <strong class="d-block mb-2">Constantes Pre-Quirúrgicas</strong>
                                                                <div class="row g-2">
                                                                    @if($registro['datos']->temperatura_pre)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Temperatura (°C)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->temperatura_pre }} °C</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->peso_pre)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Peso (kg)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->peso_pre }} kg</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->fc_pre)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Frecuencia Cardiaca</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->fc_pre }} bpm</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->fr_pre)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Frecuencia Respiratoria</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->fr_pre }} rpm</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->hidratacion_pre)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Hidratación</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->hidratacion_pre }}</div>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif

                                                        
MVP_POSTERIOR: END Detalles de atenciones especializadas --}}{{-- Médicos --}}{{-- MVP_POSTERIOR: BEGIN Detalles de atenciones especializadas

                                                        @php 
                                                            $cirujanos = is_string($registro['datos']->cirujanos) ? json_decode($registro['datos']->cirujanos, true) : $registro['datos']->cirujanos;
                                                            $anestesistas = is_string($registro['datos']->anestesistas) ? json_decode($registro['datos']->anestesistas, true) : $registro['datos']->anestesistas;
                                                        @endphp

                                                        @if(!empty($cirujanos))
                                                            <p class="mb-1"><strong>Cirujano(s):</strong> 
                                                                @foreach($cirujanos as $medico)
                                                                    {{ $medico['nombre'] ?? '' }}@if(!$loop->last), @endif
                                                                @endforeach
                                                            </p>
                                                        @endif

                                                        @if(!empty($anestesistas))
                                                            <p class="mb-1"><strong>Anestesista(s):</strong> 
                                                                @foreach($anestesistas as $medico)
                                                                    {{ $medico['nombre'] ?? '' }}@if(!$loop->last), @endif
                                                                @endforeach
                                                            </p>
                                                        @endif
                                                        
                                                        @if($registro['datos']->tratamiento_aplicado)
                                                            <p class="mb-1"><strong>Tratamiento Intraoperatorio:</strong></p>
                                                            <div class="small mb-2">
                                                                @php
                                                                    $lineas = explode("\n", $registro['datos']->tratamiento_aplicado);
                                                                @endphp
                                                                @foreach($lineas as $linea)
                                                                    @if(trim($linea))
                                                                        @php
                                                                            // Reemplazar los separadores por saltos de línea
                                                                            $lineaFormateada = str_replace(', ', '<br>', $linea);
                                                                            // Poner en negrita el nombre del medicamento
                                                                            $lineaFormateada = preg_replace('/Medicamento: ([^<]+)/', '<strong>Medicamento:</strong> $1', $lineaFormateada);
                                                                        @endphp
                                                                        <div class="mb-2 ps-3">
                                                                            {!! $lineaFormateada !!}
                                                                        </div>
                                                                    @endif
                                                                @endforeach
                                                            </div>
                                                        @endif

                                                        
MVP_POSTERIOR: END Detalles de atenciones especializadas --}}{{-- Constantes Post-Quirúrgicas --}}{{-- MVP_POSTERIOR: BEGIN Detalles de atenciones especializadas

                                                        @php
                                                            $tieneConstantesPost = $registro['datos']->peso_post || $registro['datos']->temperatura_post || 
                                                                                  $registro['datos']->fc_post || $registro['datos']->fr_post;
                                                        @endphp
                                                        
                                                        @if($tieneConstantesPost)
                                                            <div class="mb-2">
                                                                <strong class="d-block mb-2">Constantes Post-Quirúrgicas</strong>
                                                                <div class="row g-2">
                                                                    @if($registro['datos']->temperatura_post)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Temperatura (°C)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->temperatura_post }} °C</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->peso_post)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Peso (kg)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->peso_post }} kg</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->fc_post)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Frecuencia Cardiaca</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->fc_post }} bpm</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->fr_post)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Frecuencia Respiratoria</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->fr_post }} rpm</div>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif

                                                    @else
                                                        <p class="mb-2"><strong>Producto:</strong> {{ $registro['datos']->producto ?? 'No especificado' }}</p>
                                                        
                                                        @php
                                                            $tieneConstantesAnti = $registro['datos']->peso || $registro['datos']->temperatura || 
                                                                                  $registro['datos']->frecuencia_cardiaca || $registro['datos']->frecuencia_respiratoria || 
                                                                                  $registro['datos']->tlc || $registro['datos']->hidratacion;
                                                        @endphp
                                                        
                                                        @if($tieneConstantesAnti)
                                                            <div class="mb-3">
                                                                <strong class="d-block mb-2">Constantes fisiológicas</strong>
                                                                <div class="row g-2">
                                                                    @if($registro['datos']->temperatura)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Temperatura (°C)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->temperatura }} °C</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->peso)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Peso (kg)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->peso }} kg</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->frecuencia_cardiaca)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Frecuencia Cardiaca</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->frecuencia_cardiaca }} bpm</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->frecuencia_respiratoria)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Frecuencia Respiratoria</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->frecuencia_respiratoria }} rpm</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->tlc)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">TLC (seg)</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->tlc }} seg</div>
                                                                    </div>
                                                                    @endif
                                                                    @if($registro['datos']->hidratacion)
                                                                    <div class="col-md-4">
                                                                        <small class="text-muted d-block">Hidratación</small>
                                                                        <div class="fw-semibold">{{ $registro['datos']->hidratacion }}</div>
                                                                    </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                        @if($registro['datos']->laboratorio)
                                                            <p class="mb-1"><strong>Laboratorio:</strong> {{ $registro['datos']->laboratorio }}</p>
                                                        @endif
                                                        @if($registro['datos']->dosis)
                                                            <p class="mb-1"><strong>Dosis:</strong> {{ $registro['datos']->dosis }}</p>
                                                        @endif
                                                        @if($registro['datos']->via_administracion)
                                                            <p class="mb-1"><strong>Vía:</strong> {{ $registro['datos']->via_administracion }}</p>
                                                        @endif
                                                        @if($registro['datos']->proxima_aplicacion)
                                                            <p class="mb-1"><strong>Próxima aplicación:</strong> {{ \Carbon\Carbon::parse($registro['datos']->proxima_aplicacion)->format('d/m/Y') }}</p>
                                                        @endif
                                                    
MVP_POSTERIOR: END Detalles de atenciones especializadas --}}@endif
                                                    
                                                    {{-- Archivos adjuntos --}}
                                                    @if($registro['datos']->archivos && $registro['datos']->archivos->count() > 0)
                                                        <div class="mt-3">
                                                            <h6 class="text-muted mb-2"><i class="fas fa-paperclip me-1"></i> Archivos Adjuntos ({{ $registro['datos']->archivos->count() }})</h6>
                                                            <div class="row g-2">
                                                                @foreach($registro['datos']->archivos as $archivo)
                                                                    <div class="col-md-6">
                                                                        <div class="border rounded p-2 d-flex align-items-center">
                                                                            <div class="me-2">
                                                                                @if($archivo->tipo_archivo === 'imagen')
                                                                                    <i class="fas fa-image text-primary"></i>
                                                                                @elseif($archivo->tipo_archivo === 'documento')
                                                                                    <i class="fas fa-file-pdf text-danger"></i>
                                                                                @elseif($archivo->tipo_archivo === 'video')
                                                                                    <i class="fas fa-video text-success"></i>
                                                                                @else
                                                                                    <i class="fas fa-file text-secondary"></i>
                                                                                @endif
                                                                            </div>
                                                                            <div class="flex-grow-1">
                                                                                <div class="fw-semibold" style="font-size: 0.85rem;">{{ Str::limit($archivo->nombre_original, 30) }}</div>
                                                                                <small class="text-muted">{{ $archivo->tamaño_formateado }}</small>
                                                                            </div>
                                                                            <div>
                                                                                <a href="{{ $archivo->url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                                                                    <i class="fas fa-external-link-alt"></i>
                                                                                </a>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                    
                                                    @if($registro['datos']->observaciones ?? false)
                                                        <p class="mb-0 mt-2 text-muted"><small><strong>Observaciones:</strong> {{ $registro['datos']->observaciones }}</small></p>
                                                    @endif

                                                    {{-- Botones de Editar y Eliminar --}}
                                                    <div class="mt-3 d-flex gap-2">
                                                        @php
                                                            $tipoLower = strtolower($registro['tipo']);
                                                            
                                                            // Mapear nombres para rutas (plural)
                                                            $rutaPlural = $tipoLower;
                                                            if ($tipoLower === 'consulta') $rutaPlural = 'consultas';
                                                            elseif ($tipoLower === 'vacuna') $rutaPlural = 'vacunas';
                                                            elseif ($tipoLower === 'desparasitación') {
                                                                $tipoLower = 'desparasitacion';
                                                                $rutaPlural = 'desparasitaciones';
                                                            }
                                                            elseif ($tipoLower === 'antipulgas') {
                                                                $tipoLower = 'antipulga';
                                                                $rutaPlural = 'antipulgas';
                                                            }
                                                            elseif ($tipoLower === 'cirugía') {
                                                                $tipoLower = 'cirugia';
                                                                $rutaPlural = 'cirugias';
                                                            }
                                                            
                                                            $id = $registro['datos']->id_consulta ?? 
                                                                  $registro['datos']->id_vacuna ?? 
                                                                  $registro['datos']->id_desparasitacion ?? 
                                                                  $registro['datos']->id_antipulgas ?? 
                                                                  $registro['datos']->id_cirugia ?? null;
                                                            
                                                            $nombre = $registro['datos']->motivo ?? 
                                                                     $registro['datos']->vacuna_aplicada ?? 
                                                                     $registro['datos']->producto ?? 
                                                                     $registro['datos']->tipo_cirugia ??
                                                                     $registro['tipo'];
                                                        @endphp
                                                        
                                                        @if($id)
                                                        <a href="{{ route($rutaPlural . '.edit', ['mascota' => $mascota->id_mascota, 'id' => $id]) }}" 
                                                           class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-edit me-1"></i> Editar
                                                        </a>
                                                        {{-- MVP_POSTERIOR: BEGIN Descargas PDF
@if($tipoLower === 'consulta')
                                                        <a href="{{ route('consultas.descargarPDF', ['mascota' => $mascota->id_mascota, 'id' => $id]) }}" 
                                                           class="btn btn-sm btn-outline-success" target="_blank">
                                                            <i class="fas fa-download me-1"></i> Descargar PDF
                                                        </a>
                                                        @elseif($tipoLower === 'vacuna')
                                                        <a href="{{ route('vacunas.descargarPDF', ['mascota' => $mascota->id_mascota, 'id' => $id]) }}" 
                                                           class="btn btn-sm btn-outline-success" target="_blank">
                                                            <i class="fas fa-download me-1"></i> Descargar PDF
                                                        </a>
                                                        @elseif($tipoLower === 'desparasitacion')
                                                        <a href="{{ route('desparasitaciones.descargarPDF', ['mascota' => $mascota->id_mascota, 'id' => $id]) }}" 
                                                           class="btn btn-sm btn-outline-success" target="_blank">
                                                            <i class="fas fa-download me-1"></i> Descargar PDF
                                                        </a>
                                                        @elseif($tipoLower === 'antipulga')
                                                        <a href="{{ route('antipulgas.descargarPDF', ['mascota' => $mascota->id_mascota, 'id' => $id]) }}" 
                                                           class="btn btn-sm btn-outline-success" target="_blank">
                                                            <i class="fas fa-download me-1"></i> Descargar PDF
                                                        </a>
                                                        @elseif($tipoLower === 'cirugia')
                                                        <a href="{{ route('cirugias.descargarPDF', ['mascota' => $mascota->id_mascota, 'id' => $id]) }}" 
                                                           class="btn btn-sm btn-outline-success" target="_blank">
                                                            <i class="fas fa-download me-1"></i> Descargar PDF
                                                        </a>
                                                        @endif
MVP_POSTERIOR: END Descargas PDF --}}
                                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                                onclick="eliminarRegistro('{{ $tipoLower }}', {{ $id }}, '{{ addslashes($nombre) }}')">
                                                            <i class="fas fa-trash me-1"></i> Eliminar
                                                        </button>
                                                        @endif
                                                    </div>

                                                    {{-- Sección de Archivos Adjuntos --}}
                                                    {{-- MVP_POSTERIOR: BEGIN Archivos del historial
@if(in_array($tipoLower, ['consulta', 'cirugia']))
                                                        <div class="mt-3 pt-3 border-top">
                                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                                <h6 class="mb-0 text-muted">
                                                                    <i class="fas fa-paperclip me-1"></i>
                                                                    Archivos Adjuntos
                                                                </h6>
                                                                @include('components.archivo-upload', [
                                                                    'tipoHistoria' => $tipoLower,
                                                                    'historiaId' => $id
                                                                ])
                                                            </div>
                                                            
                                                            
MVP_POSTERIOR: END Archivos del historial --}}{{-- Lista de archivos existentes --}}{{-- MVP_POSTERIOR: BEGIN Archivos del historial

                                                            <div class="archivos-list-{{ $tipoLower }}-{{ $id }}" id="archivos-list-{{ $tipoLower }}-{{ $id }}">
                                                                
MVP_POSTERIOR: END Archivos del historial --}}{{-- Los archivos se cargarán aquí dinámicamente --}}{{-- MVP_POSTERIOR: BEGIN Archivos del historial

                                                            </div>
                                                        </div>
                                                    @endif
MVP_POSTERIOR: END Archivos del historial --}}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            
                            {{-- Paginación --}}
                            <div class="d-flex justify-content-center mt-4">
                                {{ $timeline->links('vendor.pagination.custom') }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal para Nuevo Registro --}}
<div class="modal fade" id="nuevoRegistroModal" tabindex="-1" aria-labelledby="nuevoRegistroModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="nuevoRegistroModalLabel">
                    <i class="fas fa-plus-circle me-2"></i>Nuevo Registro
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <div class="list-group list-group-flush">
                    <a href="{{ route('consultas.create', $mascota->id_mascota) }}" class="list-group-item list-group-item-action py-3">
                        <i class="fas fa-stethoscope me-3 text-primary"></i> Consulta
                    </a>
                    {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<a href="{{ route('vacunas.create', $mascota->id_mascota) }}" class="list-group-item list-group-item-action py-3">
                        <i class="fas fa-syringe me-3 text-success"></i> Vacuna
                    </a>
MVP_POSTERIOR: END Atencion especializada --}}
                    {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<a href="{{ route('desparasitaciones.create', $mascota->id_mascota) }}" class="list-group-item list-group-item-action py-3">
                        <i class="fas fa-pills me-3 text-warning"></i> Desparasitación
                    </a>
MVP_POSTERIOR: END Atencion especializada --}}
                    {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<a href="{{ route('antipulgas.create', $mascota->id_mascota) }}" class="list-group-item list-group-item-action py-3">
                        <i class="fas fa-bug me-3 text-info"></i> Antipulgas
                    </a>
MVP_POSTERIOR: END Atencion especializada --}}
                    {{-- MVP_POSTERIOR: BEGIN Atencion especializada
<a href="{{ route('cirugias.create', $mascota->id_mascota) }}" class="list-group-item list-group-item-action py-3">
                        <i class="fas fa-scalpel me-3 text-danger"></i> Cirugía
                    </a>
MVP_POSTERIOR: END Atencion especializada --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal para Nuevo Evento --}}
<div class="modal fade" id="nuevoEventoModal" tabindex="-1" aria-labelledby="nuevoEventoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="nuevoEventoModalLabel">
                    <i class="fas fa-calendar-plus me-2"></i>Nuevo Evento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formNuevoEvento">
                    @csrf
                    <input type="hidden" name="id_mascota" value="{{ $mascota->id_mascota }}">
                    <input type="hidden" name="tipo" value="Otro">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="mascota_evento" class="form-label">Mascota <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="mascota_evento" value="{{ $mascota->nombre }}" readonly>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="fecha_evento" class="form-label">Fecha <span class="text-danger">*</span></label>
                            {{-- MVP_POSTERIOR: Entrada de fecha con máscara
<input type="text" class="form-control fecha-input" id="fecha_evento" name="fecha" required
                                inputmode="numeric" pattern="\d{2}-\d{2}-\d{4}" placeholder="DD-MM-AAAA"
                                maxlength="10">
--}}
<input type="date" class="form-control" id="fecha_evento" name="fecha" required>
<div class="form-label mt-2">Horario (opcional)</div>
@include('eventos.partials.hora', ['inputId' => 'fecha_evento_hora'])
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="titulo_evento" class="form-label">Título <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="titulo_evento" name="titulo" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion_evento" class="form-label">Descripción</label>
                        <textarea class="form-control" id="descripcion_evento" name="descripcion" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="btnGuardarEvento">
                    Crear Evento
                </button>
            </div>
        </div>
    </div>
</div>


{{-- Modal Universal para Editar Registros --}}
<div class="modal fade" id="modalEditarRegistro" tabindex="-1" aria-labelledby="modalEditarRegistroLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modalEditarRegistroContent">
            {{-- El contenido se cargará dinámicamente vía AJAX --}}
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- MVP_POSTERIOR: BEGIN Script cola medica
<script>
    // Verificar si la mascota está en cola médica
    document.addEventListener('DOMContentLoaded', function() {
        const idMascota = {{ $mascota->id_mascota }};
        
        function verificarColaMedica() {
            fetch(`/cola-medica/verificar/${idMascota}`)
                .then(response => response.json())
                .then(data => {
                    const btnTerminar = document.getElementById('btnTerminarAtencion');
                    if (btnTerminar) {
                        btnTerminar.style.display = data.en_cola ? 'block' : 'none';
                    }
                })
                .catch(error => console.error('Error verificando cola médica:', error));
        }
        
        // Verificar al cargar
        verificarColaMedica();
        
        // Escuchar actualizaciones en tiempo real
        if (typeof Echo !== 'undefined') {
            Echo.channel('cola-medica')
                .listen('.cola.actualizada', () => {
                    verificarColaMedica();
                });
        }
    });
</script>
MVP_POSTERIOR: END Script cola medica --}}
@endpush

@push('styles')
<style>
    /* Tarjetas de estadísticas con hover */
    .bg-primary, .bg-success, .bg-warning, .bg-info {
        transition: all 0.3s ease;
    }
    
    .bg-primary:hover {
        background-color: #0056b3 !important;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 123, 255, 0.3) !important;
    }
    
    .bg-success:hover {
        background-color: #1e7e34 !important;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3) !important;
    }
    
    .bg-warning:hover {
        background-color: #e0a800 !important;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(255, 193, 7, 0.3) !important;
    }
    
    .bg-info:hover {
        background-color: #138496 !important;
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(23, 162, 184, 0.3) !important;
    }
    
    /* Tarjeta del sidebar */
    .card {
        transition: all 0.3s ease;
    }
    
    /* Timeline */
    .timeline {
        position: relative;
    }
    
    .timeline-item {
        position: relative;
    }
    
    /* Modal de nuevo registro */
    .list-group-item {
        transition: all 0.2s ease;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
        padding-left: 1.5rem !important;
    }
    
    /* Responsive */
    @media (max-width: 991px) {
        .col-lg-3 {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/historia.js') }}"></script>
{{-- MVP_POSTERIOR: BEGIN Scripts de calendario y adjuntos
<script>
$(document).ready(function() {
    // Cargar archivos para cada registro médico
    @foreach($timeline as $registro)
        @php
            $tipoLower = strtolower($registro['tipo']);
            if ($tipoLower === 'consulta') $rutaPlural = 'consultas';
            elseif ($tipoLower === 'desparasitación') $tipoLower = 'desparasitacion';
            elseif ($tipoLower === 'antipulgas') $tipoLower = 'antipulga';
            elseif ($tipoLower === 'cirugía') $tipoLower = 'cirugia';
            
            $id = $registro['datos']->id_consulta ?? 
                  $registro['datos']->id_cirugia ?? null;
        @endphp
        
        @if($id && in_array($tipoLower, ['consulta', 'cirugia']))
            cargarArchivosHistorial('{{ $tipoLower }}', {{ $id }});
        @endif
    @endforeach

    // Guardar nuevo evento
    $('#btnGuardarEvento').on('click', function() {
        const $btn = $(this);
        const $form = $('#formNuevoEvento');
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');
        
        $.ajax({
            url: "{{ route('eventos.store') }}",
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                const modalElement = document.getElementById('nuevoEventoModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
                
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Evento registrado correctamente',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error al guardar el evento';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('Crear Evento');
            }
        });
    });
    
    // Limpiar formulario al cerrar modal
    $('#nuevoEventoModal').on('hidden.bs.modal', function() {
        $('#formNuevoEvento')[0].reset();
    });
});

// Función para cargar archivos en el historial
function cargarArchivosHistorial(tipoHistoria, historiaId) {
    if (!historiaId) return;

    fetch(`/historias/archivos?historia_type=${encodeURIComponent(tipoHistoria)}&historia_id=${encodeURIComponent(historiaId)}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.archivos.length > 0) {
                mostrarArchivosHistorial(data.archivos, tipoHistoria, historiaId);
            }
        })
        .catch(error => {
            console.error('Error al cargar archivos:', error);
        });
}

// Función para mostrar archivos en el historial
function mostrarArchivosHistorial(archivos, tipoHistoria, historiaId) {
    const container = document.getElementById(`archivos-list-${tipoHistoria}-${historiaId}`);
    if (!container) return;

    let html = '<div class="row g-2">';
    
    archivos.forEach(archivo => {
        const iconClass = getArchivoIcon(archivo.tipo);
        const sizeFormatted = formatFileSize(archivo.tamaño);
        
        html += `
            <div class="col-6 col-md-4 col-lg-3">
                <div class="card border-0 bg-light h-100">
                    <div class="card-body p-2 text-center">
                        <i class="${iconClass} fa-2x mb-2 text-primary"></i>
                        <div class="small">
                            <div class="fw-semibold text-truncate" title="${archivo.nombre}">${archivo.nombre}</div>
                            <div class="text-muted">${sizeFormatted}</div>
                        </div>
                        <div class="mt-2">
                            <a href="${archivo.url}" target="_blank" class="btn btn-xs btn-outline-primary me-1" title="Ver archivo">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button type="button" class="btn btn-xs btn-outline-danger" 
                                    onclick="eliminarArchivoHistorial(${archivo.id}, '${archivo.nombre}', '${tipoHistoria}', ${historiaId})" 
                                    title="Eliminar archivo">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    });
    
    html += '</div>';
    container.innerHTML = html;
}

// Función para obtener el ícono según el tipo de archivo
function getArchivoIcon(tipo) {
    switch (tipo) {
        case 'imagen': return 'fas fa-image';
        case 'documento': return 'fas fa-file-pdf';
        case 'video': return 'fas fa-video';
        default: return 'fas fa-file';
    }
}

// Función para formatear el tamaño del archivo
function formatFileSize(bytes) {
    if (bytes >= 1048576) {
        return (bytes / 1048576).toFixed(2) + ' MB';
    } else if (bytes >= 1024) {
        return (bytes / 1024).toFixed(2) + ' KB';
    }
    return bytes + ' B';
}

// Función para eliminar archivo desde el historial
function eliminarArchivoHistorial(archivoId, nombreArchivo, tipoHistoria, historiaId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `¿Deseas eliminar el archivo "${nombreArchivo}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/historias/archivos/${archivoId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('¡Eliminado!', 'Archivo eliminado correctamente.', 'success');
                    cargarArchivosHistorial(tipoHistoria, historiaId);
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al eliminar el archivo', 'error');
            });
        }
    });
}
</script>
MVP_POSTERIOR: END Scripts de calendario y adjuntos --}}
@endpush
@push('scripts')
<script>
$(document).ready(function() {
    $('#btnGuardarEvento').on('click', function() {
        const $btn = $(this);
        if (!document.getElementById('formNuevoEvento').reportValidity()) return;
        const $form = $('#formNuevoEvento');
        
        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Guardando...');
        
        $.ajax({
            url: "{{ route('eventos.store') }}",
            method: 'POST',
            data: $form.serialize(),
            success: function(response) {
                const modalElement = document.getElementById('nuevoEventoModal');
                const modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) modal.hide();
                
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'Evento registrado correctamente',
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(() => {
                    location.reload();
                });
            },
            error: function(xhr) {
                const errorMsg = xhr.responseJSON?.message || 'Error al guardar el evento';
                Swal.fire('Error', errorMsg, 'error');
                $btn.prop('disabled', false).html('Crear Evento');
            }
        });
    });
    
    // Limpiar formulario al cerrar modal
    $('#nuevoEventoModal').on('hidden.bs.modal', function() {
        $('#formNuevoEvento')[0].reset();
    });
});
</script>
@endpush
