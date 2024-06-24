<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .container {
            width: 80%;
            margin: auto;
            border: 1px solid #ddd;
            padding: 20px;
        }
        .header, .footer {
            text-align: center;
        }
        .header img {
            width: 100px;
        }
        .details, .items, .total, .process {
            width: 100%;
            margin-bottom: 20px;
        }
        .details table, .items table, .total table {
            width: 100%;
            border-collapse: collapse;
        }
        .details th, .details td, .items th, .items td, .total th, .total td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .items th, .total th {
            background-color: #f5f5f5;
        }
        .total {
            text-align: right;
        }
        .total th, .total td {
            text-align: right;
        }
        .footer {
            margin-top: 20px;
            font-size: 12px;
        }
        .footer hr {
            margin: 20px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <img src="https://rulersoluciones.com/images/Logo_ruler_black.png" alt="Ruler S.A.S">
        <p>Calle 56 # 3 - 26</p>
        <p>3104736884</p>
        <p>Recibo De Caja: 00{{ $proceso->id }}</p>
    </div>

    <div class="details">
        <p><strong>Cliente:</strong> {{ $proceso->client->name }}</p>
        <p><strong>Cédula:</strong> {{ $proceso->client->dni }}</p>
        <p><strong>Estado:</strong> {{ $proceso->estado }}</p>
    </div>

    <div class="items">
        <table>
            <thead>
                <tr>
                    <th>Concepto</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @if (!is_null($proceso->registrarProcesos) && count($proceso->registrarProcesos) > 0)
                    @foreach ($proceso->registrarProcesos as $repeaterItem)
                        <tr>
                            <td>{{ $repeaterItem->processcategory->name }}</td>
                            <td>{{ number_format($repeaterItem->total_value_paymet, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="4">No hay datos de repeater disponibles.</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="total">
        <table>
            <tr>
                <th>Abono</th>
                <td>{{ number_format($total_pagado, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total</th>
                <td>{{ number_format($proceso->gran_total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Saldo</th>
                <td>{{ number_format($proceso->gran_total - $total_pagado, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <hr>
        <p>"LA ALTERACIÓN, FALSIFICACIÓN O COMERCIALIZACIÓN ILEGAL DE ESTE DOCUMENTO ESTÁ PENADO POR LA LEY"</p>
    </div>
</div>

</body>
</html>
