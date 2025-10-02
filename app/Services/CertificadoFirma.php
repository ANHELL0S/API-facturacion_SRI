<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class CertificadoFirma
{
    public function handleCertificate($certificateFile, $certificateKey, $expectedRuc, $existingCertificatePath = null)
    {
        Log::info("Servicio CertificadoFirma: Iniciando manejo de certificado.");

        try {
            // 1. Validar que el archivo existe y es legible
            if (!$certificateFile || !$certificateFile->isValid()) {
                throw new Exception('Archivo de certificado no válido o no encontrado.');
            }

            // 2. Validar el tipo de archivo
            $mimeType = $certificateFile->getMimeType();
            $extension = strtolower($certificateFile->getClientOriginalExtension());
            
            Log::info("Tipo MIME del archivo: " . $mimeType);
            Log::info("Extensión del archivo: " . $extension);
            
            $validExtensions = ['p12', 'pfx'];
            if (!in_array($extension, $validExtensions)) {
                throw new Exception("Extensión de archivo no válida. Se esperaba: " . implode(', ', $validExtensions));
            }

            // 3. Validar y leer el contenido del certificado
            Log::info("Paso 1: Leyendo contenido del certificado.");
            $certificateContent = file_get_contents($certificateFile->getRealPath());
            
            if ($certificateContent === false) {
                throw new Exception('No se pudo leer el contenido del archivo de certificado.');
            }
            
            Log::info("Tamaño del archivo: " . strlen($certificateContent) . " bytes");

            // 4. Validar que la clave no esté vacía
            if (empty($certificateKey)) {
                throw new Exception('La clave del certificado no puede estar vacía.');
            }

            // 5. Obtener información del entorno OpenSSL
            Log::info("Información del entorno OpenSSL:");
            Log::info("Versión OpenSSL: " . OPENSSL_VERSION_TEXT);
            Log::info("Variable OPENSSL_CONF: " . (getenv('OPENSSL_CONF') ?: 'No configurada'));
            
            // Verificar si el certificado es válido usando CLI antes de procesarlo con PHP
            $this->validateCertificateWithCLI($certificateContent, $certificateKey);

            $certData = [];

            Log::info("Intentando leer el archivo PKCS12 con openssl_pkcs12_read.");
            
            // Habilitar proveedores legacy para algoritmos antiguos
            $this->enableLegacyProvider();
            
            // Intentar con diferentes encodings de la clave
            $passwords = [
                $certificateKey,
                trim($certificateKey),
                utf8_encode($certificateKey),
                utf8_decode($certificateKey)
            ];

            $success = false;
            foreach ($passwords as $index => $password) {
                Log::info("Intentando con variación de clave #" . ($index + 1));
                
                if (openssl_pkcs12_read($certificateContent, $certData, $password)) {
                    Log::info("openssl_pkcs12_read exitoso con variación de clave #" . ($index + 1));
                    $success = true;
                    break;
                }
            }

            if (!$success) {
                // Intentar método alternativo con soporte legacy
                Log::info("Intentando método alternativo con soporte legacy.");
                $certData = $this->readPkcs12WithLegacySupport($certificateContent, $passwords);
                
                if ($certData !== false) {
                    $success = true;
                    Log::info("Método alternativo exitoso.");
                }
            }

            if (!$success) {
                // Obtener el último error de OpenSSL
                $sslError = '';
                while (($msg = openssl_error_string()) !== false) {
                    $sslError .= $msg . '; ';
                }
                
                Log::error("Fallo en openssl_pkcs12_read. Error SSL: " . $sslError);
                Log::error("La clave o el archivo son inválidos.");
                
                // Sugerir soluciones específicas basadas en el error
                if (strpos($sslError, 'unsupported') !== false) {
                    throw new Exception('El certificado usa algoritmos criptográficos legacy no soportados por esta versión de OpenSSL. Intente convertir el certificado a un formato más moderno o contacte al administrador del sistema.');
                } else {
                    throw new Exception('El archivo del certificado o la clave son inválidos. Verifique que el archivo sea un certificado PKCS#12 válido y que la clave sea correcta.');
                }
            }

            // 5. Validar que se obtuvieron los datos necesarios
            if (!isset($certData['cert']) || !isset($certData['pkey'])) {
                throw new Exception('El certificado no contiene los datos necesarios (cert/pkey).');
            }

            // 6. Validar la fecha de expiración
            Log::info("Paso 2: Validando fecha de expiración.");
            $x509Cert = $certData['cert'];
            $parsedCert = openssl_x509_parse($x509Cert);
            if (!$parsedCert) {
                Log::error("Fallo en openssl_x509_parse. No se pudo analizar el certificado.");
                throw new Exception('No se pudo analizar el certificado.');
            }
            Log::info("openssl_x509_parse exitoso.");

            $validTo = $parsedCert['validTo_time_t'];
            $validFrom = $parsedCert['validFrom_time_t'];
            $currentTime = time();
            
            Log::info("Fecha actual (timestamp): " . $currentTime . " (" . date('Y-m-d H:i:s', $currentTime) . ")");
            Log::info("Fecha de inicio (timestamp): " . $validFrom . " (" . date('Y-m-d H:i:s', $validFrom) . ")");
            Log::info("Fecha de expiración (timestamp): " . $validTo . " (" . date('Y-m-d H:i:s', $validTo) . ")");
            
            if ($currentTime < $validFrom) {
                Log::error("El certificado aún no es válido. Fecha de inicio: " . date('Y-m-d H:i:s', $validFrom));
                throw new Exception('El certificado aún no es válido.');
            }
            
            if ($currentTime > $validTo) {
                Log::error("El certificado ha caducado. Fecha de expiración: " . date('Y-m-d H:i:s', $validTo));
                throw new Exception('El certificado ha caducado.');
            }
            Log::info("Validación de fecha de expiración exitosa.");

            // 7. Validar que el RUC del certificado coincida con el del usuario
            Log::info("Paso 3: Validando RUC.");
            
            // Mostrar toda la información del subject para debugging
            Log::info("Subject completo del certificado: " . json_encode($parsedCert['subject']));
            
            $certRuc = $parsedCert['subject']['serialNumber'] ?? null;

            if (!$certRuc) {
                Log::error("No se pudo encontrar el serialNumber en el certificado.");
                Log::info("Campos disponibles en subject: " . implode(', ', array_keys($parsedCert['subject'])));
                throw new Exception('No se pudo obtener el RUC del certificado.');
            }
            Log::info("serialNumber encontrado en el certificado: " . $certRuc);

            // Extraer solo la parte inicial del serialNumber, que corresponde a la Cédula/RUC.
            // Formato común: 1234567890-xxxxxxxxxx
            if (preg_match('/^(\d+)/', $certRuc, $matches)) {
                $certRuc = $matches[1];
            } else {
                // Fallback por si el formato no es el esperado
                $certRuc = preg_replace('/[^0-9]/', '', $certRuc);
            }
            Log::info("RUC/Cédula limpiado del certificado: " . $certRuc . ". RUC esperado del usuario: " . $expectedRuc);

            $rucMatch = false;
            $tipoContribuyente = $this->getTipoContribuyente($expectedRuc);
            Log::info("Tipo de contribuyente detectado para RUC $expectedRuc: " . $tipoContribuyente);

            if ($tipoContribuyente === 'persona_natural') {
                // Para personas naturales, el RUC del usuario (13 dígitos) debe comenzar con la Cédula del certificado (10 dígitos).
                if (strlen($certRuc) == 10 && str_starts_with($expectedRuc, $certRuc)) {
                    $rucMatch = true;
                    Log::info("Validación exitosa para Persona Natural (Cédula en certificado vs RUC de usuario).");
                }
            } elseif ($tipoContribuyente === 'persona_juridica_privada' || $tipoContribuyente === 'persona_juridica_publica') {
                // Para personas jurídicas, se omite la validación estricta del RUC del certificado,
                // ya que el certificado pertenece al Representante Legal (Cédula) y no a la empresa (RUC).
                // Se asume que si el cliente sube la firma, es la correcta.
                $rucMatch = true;
                Log::info("Se omite la validación de RUC para Persona Jurídica. Se confía en el certificado proporcionado por el usuario.");
            }
            
            // Como fallback y para otros casos (ej. RUC de empresa en certificado), se realiza una comprobación de coincidencia exacta.
            if (!$rucMatch && $expectedRuc === $certRuc) {
                $rucMatch = true;
                Log::info("Validación exitosa por coincidencia exacta de RUC.");
            }

            if (!$rucMatch) {
                Log::error("El RUC no coincide. Certificado: " . $certRuc . " | Usuario: " . $expectedRuc);
                throw new Exception("El RUC del certificado ($certRuc) no coincide con el RUC del usuario ($expectedRuc).");
            }
            Log::info("Validación de RUC exitosa.");

            // 8. Reemplazar el certificado anterior si existe
            if ($existingCertificatePath && Storage::exists($existingCertificatePath)) {
                Log::info("Paso 4: Eliminando certificado anterior: " . $existingCertificatePath);
                Storage::delete($existingCertificatePath);
            }

            // 9. Guardar el archivo en el almacenamiento
            $fileName = 'signature_' . uniqid() . '.p12';
            Log::info("Paso 5: Guardando nuevo archivo de firma como: " . $fileName);
            $filePath = $certificateFile->storeAs('signatures', $fileName);
            Log::info("Archivo guardado exitosamente en: " . $filePath);

            return [
                'signature_path' => $filePath,
                'signature_key' => encrypt($certificateKey),
                'expires_at' => date('Y-m-d H:i:s', $validTo),
                'valid_from' => date('Y-m-d H:i:s', $validFrom),
                'certificate_info' => [
                    'subject' => $parsedCert['subject'],
                    'issuer' => $parsedCert['issuer'],
                    'serial_number' => $parsedCert['serialNumber'] ?? 'N/A',
                ]
            ];
        } catch (Exception $e) {
            Log::error("Excepción capturada en handleCertificate: " . $e->getMessage());
            // Re-lanzamos la excepción para que el controlador la maneje
            throw $e;
        }
    }

    /**
     * Determina el tipo de contribuyente basado en el RUC.
     * @param string $ruc
     * @return string
     */
    private function getTipoContribuyente(string $ruc): string
    {
        if (strlen($ruc) !== 13) {
            return 'desconocido';
        }

        $tercerDigito = substr($ruc, 2, 1);

        if ($tercerDigito >= '0' && $tercerDigito < '6') {
            return 'persona_natural';
        } elseif ($tercerDigito == '6') {
            return 'persona_juridica_publica';
        } elseif ($tercerDigito == '9') {
            return 'persona_juridica_privada';
        }

        return 'desconocido';
    }

    /**
     * Habilita el proveedor legacy de OpenSSL para algoritmos antiguos
     */
    private function enableLegacyProvider()
    {
        try {
            Log::info("Habilitando proveedor legacy de OpenSSL.");
            
            // Buscar archivos de configuración OpenSSL existentes
            $possibleConfigs = [
                '/etc/ssl/openssl.cnf',
                '/usr/local/ssl/openssl.cnf',
                '/opt/ssl/openssl.cnf',
                '/etc/pki/tls/openssl.cnf'
            ];
            
            $currentConfig = null;
            foreach ($possibleConfigs as $config) {
                if (file_exists($config)) {
                    $currentConfig = $config;
                    Log::info("Archivo OpenSSL encontrado: " . $config);
                    break;
                }
            }
            
            // Si encontramos un archivo de configuración existente, usarlo
            if ($currentConfig) {
                putenv("OPENSSL_CONF=" . $currentConfig);
                Log::info("Usando configuración OpenSSL existente: " . $currentConfig);
            } else {
                // Crear configuración temporal con proveedores legacy
                $tempConfig = tmpfile();
                $tempConfigPath = stream_get_meta_data($tempConfig)['uri'];
                
                fwrite($tempConfig, "
openssl_conf = openssl_init

[openssl_init]
providers = provider_sect

[provider_sect]
default = default_sect
legacy = legacy_sect

[default_sect]
activate = 1

[legacy_sect]
activate = 1
");
                
                putenv("OPENSSL_CONF=" . $tempConfigPath);
                Log::info("Configuración legacy temporal creada: " . $tempConfigPath);
            }
            
            // Forzar la recarga de la configuración
            if (function_exists('openssl_config')) {
                openssl_config();
            }
            
            Log::info("Configuración legacy habilitada.");
            
        } catch (Exception $e) {
            Log::warning("No se pudo habilitar el proveedor legacy: " . $e->getMessage());
        }
    }

    /**
     * Método alternativo para leer PKCS12 con soporte legacy usando línea de comandos
     */
    private function readPkcs12WithLegacySupport($certificateContent, $passwords)
    {
        foreach ($passwords as $index => $password) {
            Log::info("Método alternativo CLI: Intentando con variación de clave #" . ($index + 1));
            
            // Crear archivo temporal para el certificado
            $tempCertFile = tempnam(sys_get_temp_dir(), 'cert_') . '.p12';
            file_put_contents($tempCertFile, $certificateContent);
            
            try {
                // Método 1: Intentar con proveedores legacy explícitos
                $certPem = $this->extractCertificateWithLegacy($tempCertFile, $password);
                $keyPem = $this->extractPrivateKeyWithLegacy($tempCertFile, $password);
                
                if ($certPem && $keyPem) {
                    Log::info("Extracción CLI exitosa con proveedores legacy");
                    
                    // Crear estructura de datos compatible
                    $certData = [
                        'cert' => $certPem,
                        'pkey' => $keyPem
                    ];
                    
                    unlink($tempCertFile);
                    return $certData;
                }
                
                // Método 2: Convertir el certificado a formato más moderno
                $modernCertPath = $this->convertToModernPkcs12($tempCertFile, $password);
                if ($modernCertPath) {
                    Log::info("Conversión a formato moderno exitosa, reintentando");
                    
                    $modernContent = file_get_contents($modernCertPath);
                    $certData = [];
                    
                    if (openssl_pkcs12_read($modernContent, $certData, $password)) {
                        unlink($tempCertFile);
                        unlink($modernCertPath);
                        return $certData;
                    }
                    
                    unlink($modernCertPath);
                }
                
            } catch (Exception $e) {
                Log::warning("Error en método CLI: " . $e->getMessage());
            } finally {
                if (file_exists($tempCertFile)) {
                    unlink($tempCertFile);
                }
            }
        }
        
        return false;
    }

    /**
     * Extrae el certificado usando proveedores legacy
     */
    private function extractCertificateWithLegacy($certFile, $password)
    {
        $commands = [
            // Comando con proveedores legacy explícitos
            sprintf('openssl pkcs12 -in %s -clcerts -nokeys -passin pass:%s -provider legacy -provider default 2>/dev/null',
                escapeshellarg($certFile), escapeshellarg($password)),
            
            // Comando tradicional
            sprintf('openssl pkcs12 -in %s -clcerts -nokeys -passin pass:%s 2>/dev/null',
                escapeshellarg($certFile), escapeshellarg($password)),
                
            // Comando con configuración legacy temporal
            sprintf('OPENSSL_CONF="" openssl pkcs12 -in %s -clcerts -nokeys -passin pass:%s -legacy 2>/dev/null',
                escapeshellarg($certFile), escapeshellarg($password))
        ];
        
        foreach ($commands as $command) {
            $output = shell_exec($command);
            if ($output && strpos($output, 'BEGIN CERTIFICATE') !== false) {
                Log::info("Certificado extraído exitosamente");
                return $output;
            }
        }
        
        return false;
    }

    /**
     * Extrae la clave privada usando proveedores legacy
     */
    private function extractPrivateKeyWithLegacy($certFile, $password)
    {
        $commands = [
            // Comando con proveedores legacy explícitos
            sprintf('openssl pkcs12 -in %s -nocerts -nodes -passin pass:%s -provider legacy -provider default 2>/dev/null',
                escapeshellarg($certFile), escapeshellarg($password)),
            
            // Comando tradicional
            sprintf('openssl pkcs12 -in %s -nocerts -nodes -passin pass:%s 2>/dev/null',
                escapeshellarg($certFile), escapeshellarg($password)),
                
            // Comando con configuración legacy temporal
            sprintf('OPENSSL_CONF="" openssl pkcs12 -in %s -nocerts -nodes -passin pass:%s -legacy 2>/dev/null',
                escapeshellarg($certFile), escapeshellarg($password))
        ];
        
        foreach ($commands as $command) {
            $output = shell_exec($command);
            if ($output && (strpos($output, 'BEGIN PRIVATE KEY') !== false || 
                           strpos($output, 'BEGIN RSA PRIVATE KEY') !== false)) {
                Log::info("Clave privada extraída exitosamente");
                return $output;
            }
        }
        
        return false;
    }

    /**
     * Convierte el certificado PKCS12 a un formato más moderno
     */
    private function convertToModernPkcs12($oldCertFile, $password)
    {
        try {
            $tempPemFile = tempnam(sys_get_temp_dir(), 'cert_pem_') . '.pem';
            $modernCertFile = tempnam(sys_get_temp_dir(), 'cert_modern_') . '.p12';
            
            // Paso 1: Convertir a PEM con proveedores legacy
            $convertToPemCommand = sprintf(
                'openssl pkcs12 -in %s -out %s -nodes -passin pass:%s -provider legacy -provider default 2>/dev/null',
                escapeshellarg($oldCertFile),
                escapeshellarg($tempPemFile),
                escapeshellarg($password)
            );
            
            $result1 = shell_exec($convertToPemCommand);
            
            if (file_exists($tempPemFile) && filesize($tempPemFile) > 0) {
                // Paso 2: Convertir de vuelta a PKCS12 con algoritmos modernos
                $convertToPkcs12Command = sprintf(
                    'openssl pkcs12 -export -in %s -out %s -passout pass:%s -keypbe AES-256-CBC -certpbe AES-256-CBC 2>/dev/null',
                    escapeshellarg($tempPemFile),
                    escapeshellarg($modernCertFile),
                    escapeshellarg($password)
                );
                
                $result2 = shell_exec($convertToPkcs12Command);
                
                if (file_exists($modernCertFile) && filesize($modernCertFile) > 0) {
                    Log::info("Conversión a formato moderno exitosa");
                    unlink($tempPemFile);
                    return $modernCertFile;
                }
            }
            
            // Limpiar archivos temporales si algo falló
            if (file_exists($tempPemFile)) unlink($tempPemFile);
            if (file_exists($modernCertFile)) unlink($modernCertFile);
            
        } catch (Exception $e) {
            Log::warning("Error en conversión a formato moderno: " . $e->getMessage());
        }
        
        return false;
    }

    /**
     * Valida el certificado usando la línea de comandos OpenSSL
     */
    private function validateCertificateWithCLI($certificateContent, $password)
    {
        try {
            Log::info("Validando certificado con CLI de OpenSSL");
            
            // Crear archivo temporal
            $tempFile = tempnam(sys_get_temp_dir(), 'cert_validation_') . '.p12';
            file_put_contents($tempFile, $certificateContent);
            
            // Comando para obtener información del certificado
            $infoCommand = sprintf(
                'openssl pkcs12 -info -in %s -passin pass:%s -noout 2>&1',
                escapeshellarg($tempFile),
                escapeshellarg($password)
            );
            
            $output = shell_exec($infoCommand);
            
            Log::info("Salida del comando CLI: " . substr($output, 0, 500));
            
            // Verificar si hay errores específicos
            if (strpos($output, 'MAC verify failure') !== false) {
                Log::error("CLI: Contraseña incorrecta (MAC verify failure)");
            } elseif (strpos($output, 'unable to load') !== false) {
                Log::error("CLI: No se puede cargar el certificado");
            } elseif (strpos($output, 'unsupported') !== false) {
                Log::warning("CLI: Algoritmos no soportados detectados");
            } else {
                Log::info("CLI: Validación inicial exitosa");
            }
            
            unlink($tempFile);
            
        } catch (Exception $e) {
            Log::warning("Error en validación CLI: " . $e->getMessage());
        }
    }
}