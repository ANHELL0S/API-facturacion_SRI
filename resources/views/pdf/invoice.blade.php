<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura</title>
    <style>
        body { 
            font-family: sans-serif; 
            font-size: 12px; /* Aumentado de 10px a 12px */
            line-height: 1.4;
        }
        .container { width: 100%; margin: 0 auto; }
        .header-container { 
            display: table; 
            width: 100%; 
            border: 1px solid #ddd; 
            border-radius: 8px; /* Bordes redondeados */
        }
        .emitter-container { 
            display: table-cell; 
            width: 50%; 
            vertical-align: top; 
            padding: 15px; /* Aumentado padding */
        }
        .invoice-info-container { 
            display: table-cell; 
            width: 50%; 
            vertical-align: top; 
            border-left: 1px solid #ddd; 
            padding: 15px; /* Aumentado padding */
            border-top-right-radius: 8px;
            border-bottom-right-radius: 8px;
        }
        .client-info-container { 
            margin-top: 15px; 
            border: 1px solid #ddd; 
            border-radius: 8px; /* Bordes redondeados */
            padding: 15px; /* Aumentado padding */
            background-color: #fafafa;
            display: table;
            width: 100%;
        }
        .client-info-left { 
            display: table-cell; 
            width: 50%; 
            vertical-align: top; 
            padding-right: 15px;
        }
        .client-info-right { 
            display: table-cell; 
            width: 50%; 
            vertical-align: top; 
            padding-left: 15px;
        }
        .content { margin-top: 15px; }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            border-radius: 8px; /* Bordes redondeados */
            overflow: hidden; /* Para que los bordes redondeados funcionen con border-collapse */
        }
        th, td { 
            border: 1px solid #ddd; 
            padding: 8px; /* Aumentado de 5px a 8px */
        }
        th { 
            background-color: #f2f2f2; 
            font-weight: bold;
        }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .totals-container { 
            width: 100%; 
            margin-top: 15px;
        }
        .additional-info-container { 
            width: 48%;
            float: left;
            border: 1px solid #ddd; 
            border-radius: 8px;
            padding: 15px;
            background-color: #fafafa;
            box-sizing: border-box;
            margin-bottom: 15px; /* Por si se necesita espacio abajo */
        }
        .totals-table-container { 
            width: 44%;
            float: right;
            box-sizing: border-box;
        }
        /* Clearfix para contener los floats */
        .totals-container::after {
            content: "";
            display: table;
            clear: both;
        }
        .totals-table { 
            width: 100%; 
            border-collapse: collapse;
            border-radius: 8px;
            overflow: hidden;
            border: 1px solid #ddd; /* Agregamos borde a la tabla */
        }
        .totals-table td { 
            border: 1px solid #ddd; 
            padding: 8px; /* Aumentado padding */
        }
        p { 
            margin: 4px 0; /* Aumentado de 2px a 4px */
        }
        .barcode-container {
            text-align: center; 
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #eee;
            border-radius: 6px;
            background-color: #f9f9f9;
        }
        .access-key {
            font-size: 10px; 
            word-wrap: break-word;
            font-family: monospace;
        }
        /* Estilos para las primeras y últimas celdas de las tablas para bordes redondeados */
        table tr:first-child th:first-child { border-top-left-radius: 8px; }
        table tr:first-child th:last-child { border-top-right-radius: 8px; }
        table tr:last-child td:first-child { border-bottom-left-radius: 8px; }
        table tr:last-child td:last-child { border-bottom-right-radius: 8px; }

        /* Estilos específicos para hacer el contenedor más compacto cuando no hay información */
        .additional-info-container.minimal {
            padding: 10px 15px; /* Padding reducido para cuando no hay contenido adicional */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header-container">
            <div class="emitter-container">
                @if(isset($logo_path))
                    <img src="{{ public_path('storage/' . $logo_path) }}" alt="Logo" style="max-width: 200px; max-height: 100px; margin-bottom: 15px; border-radius: 4px;"/>
                @endif
                <p><span class="bold">Emisor:</span> {{ $infoTributaria->razonSocial }}</p>
                <p><span class="bold">RUC:</span> {{ $infoTributaria->ruc }}</p>
                <p><span class="bold">Matriz:</span> {{ $infoTributaria->dirMatriz }}</p>
                <p><span class="bold">Correo:</span> {{ $user->email }}</p>
                {{-- No hay campo de telefono en la tabla de usuarios --}}
                <p><span class="bold">Obligado a llevar contabilidad:</span> {{ $infoFactura->obligadoContabilidad }}</p>
                @if(isset($infoTributaria->regimenMicroempresas))
                <p><span class="bold">CONTRIBUYENTE RÉGIMEN RIMPE</span></p>
                @endif
            </div>
            <div class="invoice-info-container">
                <p><span class="bold">FACTURA No.</span> {{ $infoTributaria->estab }}-{{ $infoTributaria->ptoEmi }}-{{ $infoTributaria->secuencial }}</p>
                <p><span class="bold">Número de Autorización:</span></p>
                <p>{{ $numeroAutorizacion }}</p>
                <p><span class="bold">Fecha y hora de Autorización:</span> {{ $fechaAutorizacion }}</p>
                <p><span class="bold">Ambiente:</span> {{ $infoTributaria->ambiente == '1' ? 'PRUEBAS' : 'PRODUCCIÓN' }}</p>
                <p><span class="bold">Emisión:</span> {{ $infoTributaria->tipoEmision == '1' ? 'NORMAL' : 'CONTINGENCIA' }}</p>
                <p><span class="bold">Clave de Acceso:</span></p>
              
            
                    <img src="{{ $barcode_path }}" alt="Código de barras de la factura" style="max-width: 100%; height: 5%;" />
                      <p class="access-key">{{ $infoTributaria->claveAcceso }}</p>
          
                
            </div>
        </div>

        @php
            $clientEmail = '';
            $clientTelefono = '';
            $infoAdicionalCampos = [];
            if (isset($infoAdicional) && $infoAdicional->campoAdicional) {
                foreach ($infoAdicional->campoAdicional as $campo) {
                    $nombre = strtolower((string)$campo['nombre']);
                    $valor = (string)$campo;
                    if ($nombre === 'email' || $nombre === 'correo') {
                        $clientEmail = $valor;
                    } elseif ($nombre === 'telefono' || $nombre === 'teléfono') {
                        $clientTelefono = $valor;
                    } else {
                        $infoAdicionalCampos[] = ['nombre' => (string)$campo['nombre'], 'valor' => $valor];
                    }
                }
            }

            // Determinar si hay formas de pago
            $hayFormasPago = isset($infoFactura->pagos) && isset($infoFactura->pagos->pago);
            $hayInfoAdicional = count($infoAdicionalCampos) > 0;
            $esMinimal = !$hayFormasPago && !$hayInfoAdicional;
        @endphp

        <div class="client-info-container">
            <div class="client-info-left">
                <p><span class="bold">Razón Social:</span> {{ $infoFactura->razonSocialComprador }}</p>
                <p><span class="bold">RUC/CI:</span> {{ $infoFactura->identificacionComprador }}</p>
                <p><span class="bold">Dirección:</span> {{ $infoFactura->direccionComprador ?? 'N/A' }}</p>
            </div>
            <div class="client-info-right">
                <p><span class="bold">Teléfono:</span> {{ $clientTelefono }}</p>
                <p><span class="bold">Fecha Emisión:</span> {{ $infoFactura->fechaEmision }}</p>
                <p><span class="bold">Correo:</span> {{ $clientEmail }}</p>
            </div>
        </div>

        <div class="content">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Descripción</th>
                        <th>Cantidad</th>
                        <th>Precio Unit.</th>
                        <th>Descuento</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($detalles as $detalle)
                    <tr>
                        <td>{{ $detalle->codigoPrincipal }}</td>
                        <td>{{ $detalle->descripcion }}</td>
                        <td class="text-right">{{ $detalle->cantidad }}</td>
                        <td class="text-right">{{ number_format((float)$detalle->precioUnitario, 2) }}</td>
                        <td class="text-right">{{ number_format((float)$detalle->descuento, 2) }}</td>
                        <td class="text-right">{{ number_format((float)$detalle->precioTotalSinImpuesto, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="totals-container">
            <div class="additional-info-container {{ $esMinimal ? 'minimal' : '' }}">
                <p class="bold">Información Adicional</p>
                @if($hayInfoAdicional)
                    @foreach($infoAdicionalCampos as $campo)
                        <p><span class="bold">{{ $campo['nombre'] }}:</span> {{ $campo['valor'] }}</p>
                    @endforeach
                @else
                    <p>No hay información adicional.</p>
                @endif

                @php
                    $formasPago = [
                        '01' => 'Sin utilización del sistema financiero',
                        '15' => 'Compensación de deudas',
                        '16' => 'Tarjeta de débito',
                        '17' => 'Dinero electrónico',
                        '18' => 'Tarjeta prepago',
                        '19' => 'Tarjeta de crédito',
                        '20' => 'Otros con utilización del sistema financiero',
                        '21' => 'Endoso de títulos',
                    ];
                @endphp

                @if($hayFormasPago)
                    <p class="bold" style="margin-top: 15px;">Formas de Pago</p>
                    <table style="border: none; border-radius: 6px; background-color: white;">
                        <thead style="display: none;">
                            <tr>
                                <th>Descripción</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($infoFactura->pagos->pago as $pago)
                                <tr>
                                    <td style="border: none; padding: 4px 0;">{{ $formasPago[(string)$pago->formaPago] ?? (string)$pago->formaPago }}</td>
                                    <td class="text-right" style="border: none; padding: 4px 0;">{{ number_format((float)$pago->total, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endif
            </div>
            <div class="totals-table-container">
                @php
                    $ivaDetails = [];
                    $subtotal0 = 0;
                    $subtotalNoObjeto = 0;
                    $subtotalExento = 0;
                    $ice = 0;

                    $tarifaMap = [
                        '0' => '0',
                        '2' => '12',
                        '3' => '14',
                        '4' => '15',
                        '5' => '5',
                        '8' => '8',
                        '10' => '13',
                    ];

                    if (isset($infoFactura->totalConImpuestos)) {
                        foreach($infoFactura->totalConImpuestos->totalImpuesto as $impuesto) {
                            if ($impuesto->codigo == '2') { // IVA
                                $codigoPorcentaje = (string)$impuesto->codigoPorcentaje;
                                if ($codigoPorcentaje === '0') {
                                    $subtotal0 = (float)$impuesto->baseImponible;
                                } elseif ($codigoPorcentaje === '6') {
                                    $subtotalNoObjeto = (float)$impuesto->baseImponible;
                                } elseif ($codigoPorcentaje === '7') {
                                    $subtotalExento = (float)$impuesto->baseImponible;
                                } else {
                                    $tarifa = $tarifaMap[$codigoPorcentaje] ?? $codigoPorcentaje;
                                    $ivaDetails[$tarifa] = [
                                        'subtotal' => (float)$impuesto->baseImponible,
                                        'iva' => (float)$impuesto->valor,
                                    ];
                                }
                            } elseif ($impuesto->codigo == '3') { // ICE
                                $ice += (float)$impuesto->valor;
                            }
                        }
                    }
                @endphp
                <table class="totals-table">
                    <tr>
                        <td class="bold text-left">Subtotal Sin Impuestos:</td>
                        <td class="text-right">${{ number_format((float)$infoFactura->totalSinImpuestos, 2) }}</td>
                    </tr>
                    @foreach($ivaDetails as $tarifa => $details)
                        @if($details['subtotal'] > 0)
                        <tr>
                            <td class="bold text-left">Subtotal {{ $tarifa }}%:</td>
                            <td class="text-right">${{ number_format($details['subtotal'], 2) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr>
                        <td class="bold text-left">Subtotal 0%:</td>
                        <td class="text-right">${{ number_format($subtotal0, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="bold text-left">Subtotal No Objeto IVA:</td>
                        <td class="text-right">${{ number_format($subtotalNoObjeto, 2) }}</td>
                    </tr>
                    @if($subtotalExento > 0)
                    <tr>
                        <td class="bold text-left">Subtotal Exento de IVA:</td>
                        <td class="text-right">${{ number_format($subtotalExento, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td class="bold text-left">Descuentos:</td>
                        <td class="text-right">${{ number_format((float)$infoFactura->totalDescuento, 2) }}</td>
                    </tr>
                    <tr>
                        <td class="bold text-left">ICE:</td>
                        <td class="text-right">${{ number_format($ice, 2) }}</td>
                    </tr>
                    @foreach($ivaDetails as $tarifa => $details)
                        @if($details['iva'] > 0)
                        <tr>
                            <td class="bold text-left">IVA {{ $tarifa }}%:</td>
                            <td class="text-right">${{ number_format($details['iva'], 2) }}</td>
                        </tr>
                        @endif
                    @endforeach
                    <tr>
                        <td class="bold text-left">Servicio %:</td>
                        <td class="text-right">${{ number_format((float)$infoFactura->propina, 2) }}</td>
                    </tr>
                    <tr style="background-color: #f0f8ff;">
                        <td class="bold text-left" style="font-size: 14px;">Valor Total:</td>
                        <td class="text-right bold" style="font-size: 14px;">${{ number_format((float)$infoFactura->importeTotal, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</body>
</html>