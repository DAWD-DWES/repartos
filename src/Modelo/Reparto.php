<?php

namespace App\Modelo;

/**
 * Reparto representa un reparto
 */
class Reparto {

    /**
     * 
     * @var string|null Identificador de la lista de reparto que corresponde al identificador de la tasklist en Google Tasks
     */
    private ?string $listaRepartoId = null;

    /**
     * 
     * @var string|null Identificador del reparto que corresponde al identificador de la task en Google Tasks
     */
    private ?string $id = null;

    /**
     * 
     * @var string Dirección del reparto
     */
    private string $direccion;

    /**
     * 
     * @var string Producto del reparto
     */
    private string $producto;

    /**
     * 
     * @var float Latitud del destino del reparto
     */
    private float $lat;

    /**
     * 
     * @var float Longitud del destino del reparto
     */
    private float $lon;

    /**
     * Constructor de la clase ListaReparto
     * 
     * @param string $direccion Dirección del reparto
     * @param string $producto Producto del reparto
     * @param string $lat Latitud del destino del reparto
     * @param string $lon Longitud del destino del reparto
     * 
     * @returns Reparto
     */
    public function __construct(string $direccion, string $producto, float $lat, float $lon) {
        $this->direccion = $direccion;
        $this->producto = $producto;
        $this->lat = $lat;
        $this->lon = $lon;
    }

    public function getListaRepartoId(): ?string {
        return $this->listaRepartoId;
    }

    public function getId(): ?string {
        return $this->id;
    }

    public function getDireccion(): string {
        return $this->direccion;
    }

    public function getProducto(): string {
        return $this->producto;
    }

    public function getLat(): float {
        return $this->lat;
    }

    public function getLon(): float {
        return $this->lon;
    }

    public function setListaRepartoId(string $listaRepartoId): void {
        $this->listaRepartoId = $listaRepartoId;
    }

    public function setId(string $id): void {
        $this->id = $id;
    }

    public function setDireccion(string $direccion): void {
        $this->direccion = $direccion;
    }

    public function setProducto(string $producto): void {
        $this->producto = $producto;
    }

    public function setLat(float $lat): void {
        $this->lat = $lat;
    }

    public function setLon(float $lon): void {
        $this->lon = $lon;
    }

}