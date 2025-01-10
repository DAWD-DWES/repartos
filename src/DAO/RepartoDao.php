<?php

namespace App\DAO;

use App\Modelo\Reparto;
use Google\Service\Tasks;
use Google\Service\Tasks\Task;

class RepartoDao {

    private Tasks $servicio;

    /**
     * Constructor objeto RepartoDao
     * 
     * Para realizar las operaciones de persistencia requiere un objeto de acceso al servicio de Google Tasks
     * 
     * @param Tasks $servicio Acceso al API de Google Tasks
     * 
     * @returns Objeto RepartoDao
     */
    public function __construct(Tasks $servicio) {
        $this->servicio = $servicio;
    }

    /**
     * Persiste por primera vez el estado de un objeto Reparto
     *
     * 
     * @param Reparto $reparto reparto a persistir
     * 
     * @returns Bool Resultado de la operación
     */
    public function crea(Reparto $reparto): bool {
        $result = true;
        $note = $reparto->getLat() . "&" . $reparto->getLon();
        $title = ucfirst($reparto->getProducto()) . " & " . ucfirst($reparto->getDireccion());
        $op = ['title' => $title, 'notes' => $note];
        $task = new Task($op);
        try {
            $taskInstance = $this->servicio->tasks->insert($reparto->getListaRepartoId(), $task);
            $reparto->setId($taskInstance->getId());
        } catch (Google_Exception $ex) {
            $result = false;
        }
        return ($result);
    }

    /**
     * Actualiza el estado de un objeto Reparto
     *
     * 
     * @param Reparto $reparto reparto a modificar
     * 
     * @returns Bool Resultado de la operación
     */
    public function modifica(Reparto $reparto): bool {
        
    }

    /**
     * Borra el estado de un objeto Reparto
     *
     * 
     * @param string $listaRepartoId Identificador de la lista de reparto
     * @param string $repartoId Identificador del reparto
     * 
     * @returns Bool Resultado de la operación
     */
    public function elimina(string $listaRepartoId, string $repartoId): bool {
        $result = true;
        try {
            $this->servicio->tasks->delete($listaRepartoId, $repartoId);
        } catch (Google_Exception $ex) {
            $result = false;
        }
        return ($result);
    }

    /**
     * Recupera un objeto Reparto dado el identificador de la lista de repartos y del reparto
     * 
     * @param string $listaRepartoId Identificador de la lista de reparto
     * @param string $repartoId Identificador del reparto
     * 
     * @returns Objeto Reparto asociado al identificador de la lista de repartos y el reparto
     */
    public function recuperaPorListaIdRepartoId(string $listaRepartoId, string $repartoId): ?Reparto {
        $task = $this->servicio->tasks->get($listaRepartoId, $repartoId);
        $campos = explode("&", $task->getTitle());
        $coord = explode("&", $task->getNotes());
        $reparto = new Reparto(trim($campos[1]), trim($campos[0]), trim($coord[0]), trim($coord[1]));
        $reparto->setListaRepartoId($listaRepartoId);
        $reparto->setId($repartoId);
        return ($reparto);
    }

}
