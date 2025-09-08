# Carpeta `clients`

Esta carpeta contiene código cliente generado automáticamente a partir de la definición de OpenAPI.

## Propósito
El contenido de esta carpeta se utiliza para facilitar la interacción con la API desde aplicaciones externas o scripts. **No se debe modificar manualmente** ya que este código se puede regenerar en cualquier momento a partir del archivo `openapi.yaml` o `openapi.json`.

## Generación de clientes
Ejecutar el siguiente comando para la creación de un cliente en un lenguaje especifico apuntando al archivo .json generado por la librería **l5-swagger**.
```bash
openapi-generator-cli generate -i /ruta/a/storage/api-docs/api-docs.json -g {lenguaje} -o clients/nombre-cliente
```
