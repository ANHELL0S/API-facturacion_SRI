<?php

namespace App\Services;

use Saloon\XmlWrangler\XmlWriter;
use Saloon\XmlWrangler\Data\RootElement;
use App\Services\AccessKeyGenerator;
use App\Services\XmlBlockGenerator;
use App\Enums\TipoComprobanteEnum;

class DocumentGenerator
{
    protected $xmlBlockGenerator;

    public function __construct(XmlBlockGenerator $xmlBlockGenerator)
    {
        $this->xmlBlockGenerator = $xmlBlockGenerator;
    }

    public function newFactura(array $preparedData): array
    {
        try {
            $preparedData['codDoc'] = TipoComprobanteEnum::FACTURA->value;

            // Generación de la clave de acceso
            $accessKey = AccessKeyGenerator::generate($preparedData);

            // Obtención de las partes necesarias
            $infoTributaria = $this->xmlBlockGenerator->addInfoTributaria($preparedData, $accessKey);
            $infoFactura = $this->xmlBlockGenerator->addInfoFactura($preparedData);
            $detalles = $this->xmlBlockGenerator->addDetalles($preparedData["detalles"]);
            $rubrosTerceros = $this->xmlBlockGenerator->addRubrosTerceros($preparedData["otrosRubrosTerceros"] ?? []);
            $infoAdicional = $this->xmlBlockGenerator->addInfoAdicional($preparedData["infoAdicional"] ?? []);

            // Crear el root element
            $rootElement = RootElement::make("factura")->setAttributes(["id" => "comprobante", "version" => "2.1.0"]);

            // Write the XML
            $xml = XmlWriter::make()->write($rootElement, $this->xmlBlockGenerator->filterNullValues([
                "infoTributaria" => $infoTributaria,
                "infoFactura" => $infoFactura,
                "detalles" => $detalles,
                "otrosRubrosTerceros" => $rubrosTerceros,
                "infoAdicional" => $infoAdicional
            ]));

            return ['xml' => $xml, "accessKey" => $accessKey];
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}