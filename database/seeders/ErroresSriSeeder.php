<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ErroresSriSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $errorCodes = [
            // Códigos de error
            [
                'code' => '2',
                'description' => 'RUC del emisor se encuentra NO ACTIVO.',
                'reason' => 'Verificar que el número de RUC se encuentre en estado ACTIVO',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '10',
                'description' => 'Establecimiento del emisor se encuentra Clausurado.',
                'reason' => 'No se autorizará comprobantes si el establecimiento emisor ha sido clausurado, automáticamente se habilitará el servicio una vez concluido la clausura.',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '26',
                'description' => 'Tamaño máximo superado',
                'reason' => 'Tamaño del archivo supera lo establecido',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '27',
                'description' => 'Clase no permitido',
                'reason' => 'La clase del contribuyente no puede emitir comprobantes electrónicos.',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '28',
                'description' => 'Acuerdo de medios electrónicos no aceptado',
                'reason' => 'Siempre el contribuyente debe haber aceptado el acuerdo de medio electrónicos en el cual se establece que se acepta que lleguen las notificaciones al buzón del contribuyente.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '34',
                'description' => 'Comprobante no autorizado',
                'reason' => 'Cuando el comprobante no ha sido autorizado como parte de la solicitud de emisión del contribuyente.',
                'validation_type' => 'EMISOR',
                'is_warning' => false
            ],
            [
                'code' => '35',
                'description' => 'Documento inválido',
                'reason' => 'Cuando el XML no pasa validación de esquema.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '36',
                'description' => 'Versión esquema descontinuada',
                'reason' => 'Cuando la versión del esquema no es la correcta.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '37',
                'description' => 'RUC sin autorización de emisión',
                'reason' => 'Cuando el RUC del emisor no cuenta con una solicitud de emisión de comprobantes electrónicos.',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '39',
                'description' => 'Firma inválida',
                'reason' => 'Firma electrónica del emisor no es válida.',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '40',
                'description' => 'Error en el certificado',
                'reason' => 'No se encontró el certificado o no se puede convertir en certificad X509.',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '42',
                'description' => 'Certificado revocado',
                'reason' => 'Certificado que ha superado su fecha de caducidad, y no ha sido renovado.',
                'validation_type' => 'EMISOR',
                'is_warning' => false
            ],
            [
                'code' => '43',
                'description' => 'Clave acceso registrada',
                'reason' => 'Cuando la clave de acceso ya se encuentra registrada en la base de datos.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '45',
                'description' => 'Secuencial registrado',
                'reason' => 'Secuencial del comprobante ya se encuentra registrado en la base de datos',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '46',
                'description' => 'RUC no existe',
                'reason' => 'Cuando el RUC emisor no existe en el Registro Único de Contribuyentes.',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '47',
                'description' => 'Tipo de comprobante no existe',
                'reason' => 'Cuando envían en el tipo de comprobante uno que no exista en el catálogo de nuestros tipos de comprobantes.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '48',
                'description' => 'Esquema XSD no existe',
                'reason' => 'Cuando el esquema para el tipo de comprobante enviado no existe.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '49',
                'description' => 'Argumentos que envían al WS nulos',
                'reason' => 'Cuando se consume el WS con argumentos nulos.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '50',
                'description' => 'Error interno general',
                'reason' => 'Cuando ocurre un error inesperado en el servidor.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '52',
                'description' => 'Error en diferencias',
                'reason' => 'Cuando existe error en los cálculos del comprobante.',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '56',
                'description' => 'Establecimiento cerrado',
                'reason' => 'Cuando el establecimiento desde el cual se genera el comprobante se encuentra cerrado.',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '57',
                'description' => 'Autorización suspendida',
                'reason' => 'Cuando la autorización para emisión de comprobantes electrónicos para el emisor se encuentra suspendida por procesos de control de la Administración Tributaria o el contribuyente no tenía la autorización para emitir comprobantes electrónicos a la fecha de emisión del comprobante',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '58',
                'description' => 'Error en la estructura de clave acceso',
                'reason' => 'Cuando la clave de acceso tiene componentes diferentes a los del comprobante.',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '63',
                'description' => 'RUC clausurado',
                'reason' => 'Cuando el RUC del emisor se encuentra clausurado por procesos de control de la Administración Tributaria.',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '64',
                'description' => 'Código documento sustento',
                'reason' => 'Cuando el código del documento sustento no existe en el catálogo de documentos que se tiene en la Administración.',
                'validation_type' => 'EMISOR',
                'is_warning' => false
            ],
            [
                'code' => '65',
                'description' => 'Fecha de emisión extemporánea',
                'reason' => 'Cuando el comprobante emitido no fue enviado de acuerdo con el tiempo del tipo de emisión en el cual fue realizado.',
                'validation_type' => 'EMISOR/RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '67',
                'description' => 'Fecha inválida',
                'reason' => 'Cuando existe errores en el formato de la fecha.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '69',
                'description' => 'Identificación del receptor',
                'reason' => 'Cuando la identificación asociada al adquirente no existe. En general cuando el RUC del adquirente no existe en el Registro Único de Contribuyentes.',
                'validation_type' => 'EMISOR',
                'is_warning' => false
            ],
            [
                'code' => '70',
                'description' => 'Clave de acceso en procesamiento',
                'reason' => 'Cuando se desea enviar un comprobante que ha sido enviado anteriormente y el mismo no ha terminado su procesamiento.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '80',
                'description' => 'Error en la estructura de clave acceso',
                'reason' => 'Cuando se ejecuta la consulta de autorización por clave de acceso y el valor de este parámetro supera los 49 dígitos, tiene caracteres alfanuméricos o cuando el tag (claveAccesoComprobante) está vacío',
                'validation_type' => 'AUTORIZACIÓN',
                'is_warning' => false
            ],
            [
                'code' => '82',
                'description' => 'Error en la fecha de inicio de transporte',
                'reason' => 'Cuando la fecha de inicio de transporte es menor a la fecha de emisión de la guía de remisión.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            [
                'code' => '92',
                'description' => 'Error al validar monto de devolución del IVA.',
                'reason' => 'Cuando el valor registrado en el campo de devolución del IVA, en facturas y notas de débito, no corresponde al que fue autorizado por el servicio web DIG.',
                'validation_type' => 'RECEPCIÓN',
                'is_warning' => false
            ],
            
            // Códigos de advertencia
            [
                'code' => '59',
                'description' => 'Identificación no existe',
                'reason' => 'Cuando el número de la identificación del adquirente no existe.',
                'validation_type' => null,
                'is_warning' => true
            ],
            [
                'code' => '60',
                'description' => 'Ambiente ejecución.',
                'reason' => 'Siempre que el comprobante sea emitido en ambiente de certificación o pruebas se enviará como parte de la autorización esta advertencia.',
                'validation_type' => null,
                'is_warning' => true
            ],
            [
                'code' => '62',
                'description' => 'Identificación incorrecta',
                'reason' => 'Cuando el número de la identificación del adquirente del comprobante está incorrecto. Por ejemplo, cédulas no pasan el dígito verificador.',
                'validation_type' => null,
                'is_warning' => true
            ],
            [
                'code' => '68',
                'description' => 'Documento sustento',
                'reason' => 'Cuando el comprobante relacionado no existe como electrónico.',
                'validation_type' => null,
                'is_warning' => true
            ],
        ];

        DB::table('errores_sri')->insert($errorCodes);
    }
}
