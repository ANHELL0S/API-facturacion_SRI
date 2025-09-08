# 📊 API de Facturación Electrónica

[![PHP Version](https://img.shields.io/badge/PHP-8.1+-4F5B93.svg?logo=php)](https://www.php.net/)
[![Laravel Version](https://img.shields.io/badge/Laravel-11-FF2D20.svg?logo=laravel)](https://laravel.com)
[![JWT Auth](https://img.shields.io/badge/JWT-Auth-orange.svg?logo=jsonwebtokens)](https://jwt.io/)
[![Swagger](https://img.shields.io/badge/swagger-io-green?logo=swagger)](https://swagger.io)
[![OpenAPI Generator](https://img.shields.io/badge/openapi-generator-green?logo=openapiinitiative)](https://openapi-generator.tech)

Sistema **stateless** completo para generación, firma y autorización de facturas electrónicas bajo estándares fiscales. Diseñado para integrarse fácilmente con diversas plataformas mediante su API RESTful con respuestas en formato JSON.

## 📋 Características Principales

- **Gestión de Usuarios:** Sistema completo de autenticación con JWT
- **Documentación Interactiva:** Interfaz Swagger/OpenAPI para explorar endpoints
- **Facturación Electrónica:** Generación, firma y autorización de comprobantes XML
- **Control de Acceso:** Sistema robusto de roles y permisos
- **Stateless:** Arquitectura sin estado para máxima escalabilidad
- **Clientes API:** Generación automática de librerías cliente en múltiples lenguajes

## 🔧 Requisitos Previos

- PHP 8.1 o superior
- Composer 2.x
- Node.js 18+ y NPM
- Laravel 11
- MySQL/MariaDB
- Java Runtime Environment (JRE) 8+ (para OpenAPI Generator y Firmador)

## ⚙️ Instalación Local

### 1. Clonar el repositorio

```bash
git clone https://github.com/softec-apps/Api-Facturacion.git
cd Api-Facturacion
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Configuración de entorno

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 4. Configurar base de datos

Edita el archivo `.env` con tus credenciales:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nombre_de_tu_base_de_datos
DB_USERNAME=usuario
DB_PASSWORD=contraseña
```

### 5. Migrar base de datos y poblar con datos iniciales

```bash
php artisan migrate --seed
```

### 6. Iniciar servidor de desarrollo

```bash
php artisan serve
```

### 7. Iniciar worker para procesar jobs

```bash
php artisan queue:work
```

## 📚 Documentación API

La API utiliza **l5-swagger** para generar documentación interactiva OpenAPI/Swagger.

### Acceso a la documentación

- **API Cliente:** `/api/documentation`
- **API Admin:** `/admin-api/documentation`

> **Nota:** La documentación del administrador requiere autenticación mediante el formulario en: `/swagger-login`

### Actualizar la documentación

```bash
# Generar documentación para API cliente
php artisan l5-swagger:generate

# Generar documentación para API administrador
php artisan l5-swagger:generate admin

# Generar documentación para todos los recursos
php artisan l5-swagger:generate --all
```

## 🔌 Generación de Clientes API

El sistema permite generar librerías cliente para consumir la API desde diferentes lenguajes usando [OpenAPI Generator](https://openapi-generator.tech).

> **IMPORTANTE**
Esta opción se encuentra disponible pero **no es recomendable** generar el cliente en el directorio del API.
Lo mejor es permitir a la aplicación cliente generarlo mediante el acceso al recurso .json.

### Instalación de OpenAPI Generator

```bash
# Usando NPM
npm install @openapitools/openapi-generator-cli -g

# O descargando el JAR directamente
wget https://repo1.maven.org/maven2/org/openapitools/openapi-generator-cli/6.5.0/openapi-generator-cli-6.5.0.jar -O openapi-generator-cli.jar
```

### Generar cliente para un lenguaje específico

```bash
# Usando NPM
npx openapi-generator-cli generate -i storage/api-docs/api-docs.json -g php -o clients/php

# Usando JAR descargado
java -jar openapi-generator-cli.jar generate -i storage/api-docs/api-docs.json -g php -o clients/php
```

> Reemplaza `php` con el lenguaje destino deseado (java, typescript, csharp, python, etc.)

## 🔐 Autenticación

La autenticación se realiza mediante tokens JWT (JSON Web Tokens).

### Obtener token

Utiliza el endpoint `/api/login` con tus credenciales para obtener un token válido.

```json
{
  "email": "usuario@ejemplo.com",
  "password": "contraseña"
}
```

### Usar token en peticiones

```bash
curl -X GET https://api.ejemplo.com/api/resource \
  -H "Authorization: Bearer {tu_token_jwt}"
```

## 👥 Contribución

1. Fork del repositorio
2. Crea una rama para tu contribución (`git checkout -b feature/nueva-funcionalidad`)
3. Realiza tus cambios y haz commit (`git commit -m "Añade nueva funcionalidad"`)
4. Push a la rama (`git push origin feature/nueva-funcionalidad`)
5. Abre un Pull Request

## 📦 Recursos y Librerías Externas

- [Firmador SRI.jar](https://github.com/softec-apps/SRI-Signer) - Utilidad para firma digital
- [XML-Wrangler](https://github.com/saloonphp/xml-wrangler) - Biblioteca para generación de XML
- [OpenAPI Generator](https://openapi-generator.tech) - Generador de clientes API

---

Desarrollado con ❤️ por [Softec Apps](https://github.com/softec-apps)