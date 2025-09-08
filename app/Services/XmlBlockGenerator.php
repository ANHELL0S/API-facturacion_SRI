<?php

namespace App\Services;

use Saloon\XmlWrangler\Data\Element;

class XmlBlockGenerator
{
    public static function filterNullValues(array $data): array
    {
        return array_filter($data, function ($value) {
            return !is_null($value);
        });
    }

    public function addInfoTributaria(array $data, string $accessKey): Element
    {
        return Element::make(self::filterNullValues([
            "ambiente" => $data["ambiente"],
            "tipoEmision" => 1, // FIJO
            "razonSocial" => $data["razonSocial"],
            "nombreComercial" => $data["nombreComercial"] ?? null,
            "ruc" => $data["ruc"],
            "claveAcceso" => $accessKey,
            "codDoc" => $data["codDoc"],
            "estab" => $data["estab"],
            "ptoEmi" => $data["ptoEmi"],
            "secuencial" => $data["secuencial"],
            "dirMatriz" => $data["dirMatriz"],
            "agenteRetencion" => $data["agenteRetencion"] ?? null,
            "contribuyenteRimpes" => $data["contribuyenteRimpe"] ?? null,
        ]));
    }

    public function addTotalConImpuestos(array $data): ?Element
    {
        if (empty($data)) {
            return null;
        }

        $totalConImpuestos = [];

        foreach ($data as $impuesto) {
            $totalImpuesto = self::filterNullValues([
                "codigo" => $impuesto["codigo"],
                "codigoPorcentaje" => $impuesto["codigoPorcentaje"],
                "baseImponible" => $impuesto["baseImponible"],
                "valor" => $impuesto["valor"]
            ]);

            $totalConImpuestos[] = Element::make($totalImpuesto);
        }

        return Element::make([
            "totalImpuesto" => $totalConImpuestos,
        ]);
    }

    public function addPagos(array $data): ?Element
    {
        if (empty($data)) {
            return null;
        }

        $totalPagos = [];

        foreach ($data as $pago) {
            $totalPago = self::filterNullValues([
                "formaPago" => $pago["formaPago"],
                "total" => $pago["total"],
                "plazo" => $pago["plazo"] ?? null,
                "unidadTiempo" => $pago["unidadTiempo"] ?? null
            ]);

            $totalPagos[] = Element::make($totalPago);
        }

        return Element::make([
            "pago" => $totalPagos,
        ]);
    }

    public function addInfoFactura(array $data): Element
    {
        // Format the date
        $formattedDate = date('d/m/Y', strtotime($data['fechaEmision']));

        // Generate totalConImpuestos
        $totalConImpuestosData = $data["totalConImpuestos"] ?? null;
        $totalConImpuestos = $this->addTotalConImpuestos($totalConImpuestosData);

        // Generate pagos
        $pagosData = $data["pagos"] ?? null;
        $pagos = $this->addPagos($pagosData);

        // Validate contribuyenteEspecial length
        $contribuyenteEspecial = isset($data["contribuyenteEspecial"]) && strlen($data["contribuyenteEspecial"]) >= 3 ? $data["contribuyenteEspecial"] : null;

        return Element::make($this->filterNullValues([
            "fechaEmision" => $formattedDate,
            "dirEstablecimiento" => $data["dirEstablecimiento"] ?? null,
            "contribuyenteEspecial" => $contribuyenteEspecial,
            "obligadoContabilidad" => $data["obligadoContabilidad"] ?? null,
            "tipoIdentificacionComprador" => $data["tipoIdentificacionComprador"],
            "guiaRemision" => $data["guiaRemision"] ?? null,
            "razonSocialComprador" => $data["razonSocialComprador"],
            "identificacionComprador" => $data["identificacionComprador"],
            "direccionComprador" => $data["direccionComprador"] ?? null,
            "totalSinImpuestos" => $data["totalSinImpuestos"],
            "totalDescuento" => $data["totalDescuento"],
            "totalConImpuestos" => $totalConImpuestos,
            "propina" => $data["propina"] ?? null,
            "importeTotal" => $data["importeTotal"],
            "moneda" => $data["moneda"] ?? null,
            "pagos" => $pagos,
            "valorRetIva" => $data["valorRetIva"] ?? null,
            "valorRetRenta" => $data["valorRetRenta"] ?? null
        ]));
    }


    public function addDetalles(array $data): Element
    {
        $detallesElements = [];

        foreach ($data as $detalle) {
            $impuestosElements = [];
            foreach ($detalle["impuestos"] as $impuesto) {
                $impuestosElements[] = Element::make(self::filterNullValues([
                    "codigo" => $impuesto["codigo"],
                    "codigoPorcentaje" => $impuesto["codigoPorcentaje"],
                    "tarifa" => $impuesto["tarifa"],
                    "baseImponible" => $impuesto["baseImponible"],
                    "valor" => $impuesto["valor"]
                ]));
            }

            $detallesElements[] = Element::make(self::filterNullValues([
                "codigoPrincipal" => $detalle["codigoPrincipal"],
                "codigoAuxiliar" => $detalle["codigoAuxiliar"] ?? null,
                "descripcion" => $detalle["descripcion"],
                "cantidad" => $detalle["cantidad"],
                "precioUnitario" => $detalle["precioUnitario"],
                "descuento" => $detalle["descuento"],
                "precioTotalSinImpuesto" => $detalle["precioTotalSinImpuesto"],
                "impuestos" => Element::make(["impuesto" => $impuestosElements])
            ]));
        }

        return Element::make([
            "detalle" => $detallesElements
        ]);
    }

    public function addRubrosTerceros(array $data): ?Element
    {
        if (empty($data)) {
            return null;
        }

        $rubrosTerceros = [];

        foreach ($data as $rubro) {
            $rubrosTerceros[] = Element::make(self::filterNullValues([
                "concepto" => $rubro["concepto"],
                "total" => $rubro["total"]
            ]));
        }

        return Element::make([
            "rubro" => $rubrosTerceros
        ]);
    }


    public function addInfoAdicional(array $data): ?Element
    {
        if (empty($data)) {
            return null;
        }

        $camposAdicionales = [];

        foreach ($data as $nombre => $valor) {
            $camposAdicionales[] = Element::make($valor, [
                "nombre" => $nombre
            ]);
        }

        return Element::make([
            "campoAdicional" => $camposAdicionales
        ]);
    }
}
