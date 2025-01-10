<?php

namespace App\Modelo;

use App\Modelo\Reparto;
use App\ServicioMap;



/**
 * ListaReparto representa una lista de repartos
 */
class ListaReparto {

    /**
     * 
     * @var string|null Identificador de la lista de reparto que corresponde al identificador de la tasklist en Google Tasks
     */
    private ?string $id = null;

    /**
     * 
     * @var string Nombre de la lista de repartos
     */
    private string $nombre;

    /**
     * 
     * @var array Array de repartos de la lista de repartos
     */
    private array $repartos = [];

    /**
     * Constructor de la clase ListaReparto
     * 
     * @param string $nombre Nombre de la lista de repartos
     * 
     * @returns ListaReparto
     */
    public function __construct(string $nombre) {
        $this->nombre = $nombre;
    }


    /**
     * Ordena la ruta de reparto para entregar los repartos de una ListaReparto
     * 
     * @param ServicioMap $servicioMapa Acceso al API de Bing Maps
     * 
     * @returns Array con el orden adecuado de entrega.
     */
    public function ordena(ServicioMap $servicioMapa) {
        if (count($this->getRepartos()) < 2) {
            $resultado = array_map(function ($x) {
                return $x->getId();
            }, $this->getRepartos());
        } else {
            $puntos = implode("|", array_map(function ($x) {
                        return "{$x->getLat()},{$x->getLon()}";
                    }, $this->getRepartos()));
            $orden = $servicioMapa->ordenarRuta($puntos);
            $orden = array_map(function ($x) {
                return $x - 1;
            }, $orden);
            for ($i = 0; $i < count($orden); $i++) {
                $repartosAux[] = $this->getRepartos()[$orden[$i]];
            }
            ksort($repartosAux);
            $this->setRepartos($repartosAux);
            $resultado = array_map(function ($x) {
                return $x->getId();
            }, $this->getRepartos());
        }
        return ($resultado);
    }

    public function getId(): ?string {
        return $this->id;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    public function getRepartos(): array {
        return $this->repartos;
    }

    public function setId(string $id): void {
        $this->id = $id;
    }

    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }

    public function setRepartos(array $repartos): void {
        $this->repartos = $repartos;
    }

}
