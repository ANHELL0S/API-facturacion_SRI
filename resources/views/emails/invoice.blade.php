<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html dir="ltr" lang="es">
  <head>
    <link rel="preload" as="image" href="{{ $logoUrl }}" />
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type" />
    <meta name="x-apple-disable-message-reformatting" />
    <!--$-->
  </head>
  <body style="background-color:#ffffff">
    <table
      border="0"
      width="100%"
      cellpadding="0"
      cellspacing="0"
      role="presentation"
      align="center">
      <tbody>
        <tr>
          <td style="background-color:#ffffff">
            <table
              align="center"
              width="100%"
              border="0"
              cellpadding="0"
              cellspacing="0"
              role="presentation"
              style="max-width:37.5em;padding-left:12px;padding-right:12px;margin:0 auto">
              <tbody>
                <tr style="width:100%">
                  <td>
                    <h1
                      style="color:#333;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;font-size:24px;font-weight:bold;margin:40px 0;padding:0">
                      Factura electrónica
                    </h1>
                    
                    <p
                      style="font-size:14px;line-height:24px;color:#333;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;margin:24px 0;margin-bottom:14px;margin-top:24px;margin-right:0;margin-left:0">
                      Estimado cliente,
                    </p>
                    
                    <p
                      style="font-size:14px;line-height:24px;color:#333;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;margin:24px 0;margin-bottom:14px;margin-top:24px;margin-right:0;margin-left:0">
                      Adjunto a este correo encontrará los archivos de su comprobante electrónico. 
                      A continuación, un resumen de la operación:
                    </p>

                    <p
                      style="font-size:14px;line-height:24px;color:#ababab;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;margin:24px 0;margin-top:14px;margin-bottom:16px;margin-right:0;margin-left:0">
                      Este es un correo electrónico generado automáticamente. Por favor, no responda a este mensaje.
                    </p>
                    
                    <p
                      style="font-size:14px;line-height:24px;color:#ababab;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;margin:24px 0;margin-top:12px;margin-bottom:38px;margin-right:0;margin-left:0">
                      Conserve este comprobante para sus registros y futuras referencias.
                    </p>

                    @if(isset($logoUrl))
                    <img
                      alt="Logo de la Empresa"
                      height="32"
                      src="{{ $logoUrl }}"
                      style="display:block;outline:none;border:none;text-decoration:none"
                      width="32" />
                    @endif
                    
                    <p
                      style="font-size:12px;line-height:22px;color:#898989;font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;margin-top:12px;margin-bottom:24px">
                      {{ $empresaNombre ?? 'Empresa' }}<br />
                      Comprobantes electrónicos autorizados
                    </p>
                  </td>
                </tr>
              </tbody>
            </table>
          </td>
        </tr>
      </tbody>
    </table>
    <!--/$-->
  </body>
</html>