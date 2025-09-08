<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Comprobante Electrónico</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap');
        body {
            font-family: 'Roboto', Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #eef2f5;
            color: #3d4852;
        }
        .email-wrapper {
            padding: 20px 0;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            overflow: hidden;
        }
        .email-header {
            padding: 30px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .email-header img {
            max-width: 120px;
        }
        .email-body {
            padding: 30px 40px;
        }
        .email-body h1 {
            color: #2d3748;
            font-size: 24px;
            font-weight: 700;
            margin-top: 0;
            text-align: center;
        }
        .email-body p {
            font-size: 16px;
            line-height: 1.6;
            margin: 0 0 1em;
        }
        .invoice-details {
            margin: 25px 0;
            padding: 20px;
            background-color: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
        }
        .invoice-details p {
            margin: 0.5em 0;
            font-size: 15px;
        }
        .invoice-details strong {
            color: #2d3748;
        }
        .button-container {
            text-align: center;
            padding: 20px 0;
        }
        .button {
            background-color: #38a89d;
            color: #ffffff !important; /* Important to override default link colors */
            padding: 14px 28px;
            text-decoration: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 700;
            display: inline-block;
        }
        .email-footer {
            text-align: center;
            padding: 25px;
            font-size: 12px;
            color: #718096;
            background-color: #f7fafc;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                @if(isset($logoUrl))
                    <img src="{{ $logoUrl }}" alt="Logo de la Empresa">
                @endif
            </div>
            <div class="email-body">
                <h1>Ha Recibido un Nuevo Comprobante</h1>
                <p>Estimado cliente,</p>
                <p>Adjunto a este correo encontrará los archivos de su comprobante electrónico. A continuación, un resumen de la operación:</p>

                <div class="invoice-details">
                    <p><strong>Clave de Acceso:</strong><br>{{ $claveAcceso }}</p>
                    <p><strong>Importe Total:</strong><br>${{ number_format($total, 2) }}</p>
                </div>

                <p>Si desea visualizar el comprobante en formato PDF, puede hacerlo a través del siguiente botón:</p>

                <div class="button-container">
                    <a href="{{ $pdfUrl }}" class="button" target="_blank">Previsualizar PDF</a>
                </div>
            </div>
            <div class="email-footer">
                Este es un correo electrónico generado automáticamente. Por favor, no responda a este mensaje.
            </div>
        </div>
    </div>
</body>
</html>
