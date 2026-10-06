{{--
Esta vista es para el detalle/ticket.
Puedes crear un layout de impresión 'layouts/print.blade.php' si quieres.
Por ahora, usará estilos en línea como tu original.
--}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalle de Venta #{{ $venta->id_venta }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 14px;
            margin: 0;
            padding: 0;
        }

        .content {
            margin: 0px auto;
            text-align: center;
            width: 300px;
        }

        .content img {
            vertical-align: middle;
        }

        .ticket {
            width: 300px;
            margin: 20px auto;
            padding: 15px;
            border: 2px dashed #000;
        }

        .ticket-header,
        .ticket-footer {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 2px dashed #000;
        }

        .ticket-footer {
            border-top: 2px dashed #000;
            border-bottom: none;
            padding-top: 12px;
            margin-top: 12px;
        }

        .cliente-info {
            margin-bottom: 12px;
            padding-bottom: 12px;
            border-bottom: 2px dashed #000;
        }

        table {
            width: 300px;
            margin: 0 auto;
            font-family: Arial, Helvetica, sans-serif;
            border-collapse: collapse;
        }

        table th {
            text-align: left;
            font-size: 14px;
            padding: 5px;
        }

        table td {
            text-align: left;
            font-size: 14px;
            padding: 5px;
        }

        .totales {
            margin-top: 12px;
            text-align: center;
        }

        .totales table {
            margin: 0 auto;
        }

        .totales td {
            font-size: 14px;
            padding: 3px 10px;
        }

        hr {
            border: 1px solid #000;
            margin: 5px 0;
        }

        @media print {
            .oculto-impresion,
            .oculto-impresion * {
                display: none !important;
            }

            @page {
                margin: 0;
            }

            body {
                margin: 0 !important;
                padding: 0 !important;
            }

            .ticket {
                border: none !important;
                box-shadow: none !important;
                width: 100% !important;
                max-width: 300px !important;
                margin: 0 !important;
                padding: 5px !important;
            }
        }

        p {
            font-family: Helvetica, Arial, sans-serif;
            margin: 5px 0;
        }
    </style>

    <script>
        function imprimir() {
            window.print();
            setTimeout(function() {
                window.location.href = '{{ route('ventas.index') }}';
            }, 1000);
        }
    </script>
</head>

<body>
    <div class="container mt-4 mb-5">
        <div class="row oculto-impresion">
            <div class="col-md-12 mb-3 d-flex justify-content-center gap-2">
                <a href="{{ route('ventas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Volver a Ventas
                </a>
                <button onclick="imprimir()" class="btn btn-primary">
                    <i class="fas fa-print me-2"></i>Imprimir
                </button>
            </div>
        </div>

        <div style="width:300px; margin: 0 auto;">
            <div class="content">
                <img src="{{ $logo_src }}" alt="Logo" width="300" height="100">
            </div>

            <div style="text-align:center; font-family: Helvetica, Arial, sans-serif;">
                <b style="font-size:16px">{{ strtoupper($nombre_negocio) }}</b><br>
                <b style="font-size:16px">RUC {{ $ruc }}</b><br>
                <b style="font-size:14px">{{ strtoupper($direccion) }}</b><br>
                <b style="font-size:16px">NOTA DE VENTA</b><br>
                <b style="font-size:14px">{{ str_pad($venta->id_venta, 6, "0", STR_PAD_LEFT) }}</b><br>
                <b style="font-size:14px">Fecha: {{ $venta->fecha_venta->format('d-m-Y') }} - {{ $venta->fecha_venta->format('H:i:s') }}</b><br>
            </div>

            <div style="margin: 10px 0;">
                <hr>
                <p style="font-size:14px; margin: 5px 0;"><b>CLIENTE:</b> {{ strtoupper($venta->mascota->cliente->nombre . ' ' . $venta->mascota->cliente->apellido) }}</p>
                <p style="font-size:14px; margin: 5px 0;"><b>DOCUMENTO:</b> {{ $venta->mascota->cliente->dni }}</p>
                <p style="font-size:14px; margin: 5px 0;"><b>MASCOTA:</b> {{ strtoupper($venta->mascota->nombre) }}</p>
                <hr>
            </div>

            <div style="margin: 10px auto;">
                <table width="300" align="center">
                    <tr style="font-size:14px;">
                        <th width="150" align="left">&nbsp;&nbsp;Producto</th>
                        <th width="20">Can</th>
                        <th width="40">P.U.</th>
                        <th width="50">Subtot</th>
                    </tr>
                    <tr>
                        <td colspan="4"><hr></td>
                    </tr>

                    @foreach ($items as $item)
                    <tr style="font-size:13px;">
                        <td>{{ $item['descripcion'] }}</td>
                        <td align="center">{{ $item['cantidad'] }}</td>
                        <td align="right">{{ number_format($item['precio'], 2) }}</td>
                        <td align="right">{{ number_format($item['total'], 2) }}</td>
                    </tr>
                    @endforeach

                    <tr>
                        <td colspan="4"><hr></td>
                    </tr>
                </table>

                <table align="center">
                    <tr style="font-size:14px">
                        <td><b>SUBTOTAL</b></td>
                        <td><b>S/</b></td>
                        <td align="right"><b>{{ number_format($subtotalTotal, 2) }}</b></td>
                    </tr>
                    <tr style="font-size:14px">
                        <td><b>DESCUENTO</b></td>
                        <td><b>S/</b></td>
                        <td align="right"><b>0.00</b></td>
                    </tr>
                    <tr style="font-size:14px">
                        <td><b>TOTAL</b></td>
                        <td><b>S/</b></td>
                        <td align="right"><b>{{ number_format($subtotalTotal, 2) }}</b></td>
                    </tr>
                    <tr>
                        <td colspan="3"><hr></td>
                    </tr>
                    <tr>
                        <td colspan="3"><label>Son: {{ $total_letras }}</label></td>
                    </tr>
                    <tr>
                        <td colspan="3"><hr></td>
                    </tr>
                </table>

                @php
                    $pagos = \App\Models\PagoVenta::where('id_venta', $venta->id_venta)->get();
                @endphp
                <table align="center">
                    <tr>
                        <td colspan="3"><b>MÉTODOS DE PAGO:</b></td>
                    </tr>
                    @if($pagos->count() > 1)
                        @foreach($pagos as $pago)
                        <tr style="font-size:14px">
                            <td colspan="3">{{ strtoupper($pago->medio_pago) }}: S/ {{ number_format($pago->monto, 2) }}</td>
                        </tr>
                        @endforeach
                    @elseif($pagos->count() == 1)
                        <tr style="font-size:14px">
                            <td colspan="3">{{ strtoupper($pagos->first()->medio_pago) }}: S/ {{ number_format($pagos->first()->monto, 2) }}</td>
                        </tr>
                    @else
                        <tr style="font-size:14px">
                            <td colspan="3">{{ strtoupper($venta->medio_pago) }}: S/ {{ number_format($subtotalTotal, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <div style="text-align:center;">
                <hr>
                <p style="font-size:14px">Gracias por su compra</p>
            </div>
        </div>
    </div>
</body>

</html>