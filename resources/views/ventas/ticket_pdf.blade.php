<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Ticket #{{ str_pad($venta->id_venta, 6, "0", STR_PAD_LEFT) }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Helvetica, Arial, sans-serif;
            font-size: 14px;
            line-height: 1.2;
            padding: 5px 10px;
        }
        .ticket {
            width: 100%;
            max-width: 80mm;
        }
        .center {
            text-align: center;
        }
        .bold {
            font-weight: bold;
        }
        .content {
            margin: 0px auto;
            text-align: center;
        }
        .content img {
            vertical-align: middle;
            max-width: 300px;
            max-height: 100px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-family: Arial, Helvetica, sans-serif;
        }
        table th {
            text-align: left;
            font-size: 14px;
            padding: 5px 2px;
        }
        table td {
            text-align: left;
            font-size: 13px;
            padding: 3px 2px;
        }
        hr {
            border: 0;
            border-top: 1px solid #000;
            margin: 5px 0;
        }
        .info-section {
            margin: 10px 0;
            font-size: 14px;
        }
        .totales-table {
            margin: 10px auto;
            text-align: right;
        }
        .totales-table td {
            padding: 3px 10px;
            font-size: 14px;
        }
        p {
            margin: 5px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="ticket">
        {{-- Logo y Encabezado --}}
        <div class="content">
            @if($logo_src)
                <img src="{{ $logo_src }}" alt="Logo">
            @endif
        </div>

        <div class="center">
            <p class="bold" style="font-size: 16px;">{{ strtoupper($nombre_negocio) }}</p>
            @if($ruc)
                <p class="bold" style="font-size: 16px;">RUC {{ $ruc }}</p>
            @endif
            @if($direccion)
                <p class="bold" style="font-size: 14px;">{{ strtoupper($direccion) }}</p>
            @endif
            <p class="bold" style="font-size: 16px;">NOTA DE VENTA</p>
            <p class="bold" style="font-size: 14px;">{{ str_pad($venta->id_venta, 6, "0", STR_PAD_LEFT) }}</p>
            <p class="bold" style="font-size: 14px;">Fecha: {{ $venta->fecha_venta->format('d-m-Y') }} - {{ $venta->fecha_venta->format('H:i:s') }}</p>
        </div>

        <div class="info-section">
            <hr>
            <p><span class="bold">CLIENTE:</span> {{ strtoupper($venta->mascota->cliente->nombre ?? '') }} {{ strtoupper($venta->mascota->cliente->apellido ?? '') }}</p>
            <p><span class="bold">DOCUMENTO:</span> {{ $venta->mascota->cliente->dni ?? 'N/A' }}</p>
            <p><span class="bold">MASCOTA:</span> {{ strtoupper($venta->mascota->nombre ?? 'N/A') }}</p>
            <hr>
        </div>

        {{-- Tabla de productos --}}
        <div>
            <table>
                <tr>
                    <th style="width: 50%;">Producto</th>
                    <th style="width: 15%; text-align: center;">Can</th>
                    <th style="width: 17%; text-align: right;">P.U.</th>
                    <th style="width: 18%; text-align: right;">Subtot</th>
                </tr>
                <tr>
                    <td colspan="4"><hr></td>
                </tr>
                @foreach($items as $item)
                <tr>
                    <td>{{ $item['descripcion'] }}</td>
                    <td style="text-align: center;">{{ $item['cantidad'] }}</td>
                    <td style="text-align: right;">{{ number_format($item['precio'], 2) }}</td>
                    <td style="text-align: right;">{{ number_format($item['total'], 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="4"><hr></td>
                </tr>
            </table>
        </div>

        {{-- Totales --}}
        <div class="center">
            <table class="totales-table">
                <tr>
                    <td class="bold">SUBTOTAL</td>
                    <td class="bold">S/</td>
                    <td class="bold" style="text-align: right;">{{ number_format($subtotalTotal, 2) }}</td>
                </tr>
                <tr>
                    <td class="bold">DESCUENTO</td>
                    <td class="bold">S/</td>
                    <td class="bold" style="text-align: right;">0.00</td>
                </tr>
                <tr>
                    <td class="bold">TOTAL</td>
                    <td class="bold">S/</td>
                    <td class="bold" style="text-align: right;">{{ number_format($subtotalTotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3"><hr></td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align: left;">Son: {{ $total_letras }}</td>
                </tr>
                <tr>
                    <td colspan="3"><hr></td>
                </tr>
            </table>
        </div>

        {{-- Medio de pago --}}
        <div class="center">
            <p class="bold">MÉTODOS DE PAGO:</p>
            @if($venta->medio_pago == 'Mixto' && $pagos->count() > 0)
                @foreach($pagos as $pago)
                    <p>{{ strtoupper($pago->medio_pago) }}: S/ {{ number_format($pago->monto, 2) }}</p>
                @endforeach
            @else
                <p>{{ strtoupper($venta->medio_pago) }}: S/ {{ number_format($subtotalTotal, 2) }}</p>
            @endif
        </div>

        <hr>

        {{-- Pie de página --}}
        <div class="center">
            <p style="font-size: 14px;">Gracias por su compra</p>
        </div>
    </div>
</body>
</html>
