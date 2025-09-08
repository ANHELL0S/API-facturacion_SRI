<?php

return [

    'jar' => base_path('app/firmador/DetzSign.jar'),
    'storage_disk' => 'public',
    'certificados' => app_path('certificado'),
    'comprobantes' => 'comprobantes/pendientes',
    'firmados' => '/comprobantes/firmados',
    'pdw' => 'Cxbs1987',
    'ruta_firmados_absoluta' => storage_path('app/public/comprobantes/firmados'),
    'recepcion' => [
        'prueba' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
        'produccion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
    ],

    'autorizacion' => [
        'prueba' => 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        'produccion' => 'https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
    ],

];
