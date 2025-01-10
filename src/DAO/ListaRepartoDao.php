<?php

namespace App\DAO;

use App\Modelo\Reparto;
use App\Modelo\ListaReparto;
use App\DAO\RepartoDao;
use Google\Service\Tasks;
use Google\Service\Tasks\TaskList;

class ListaRepartoDao {

    private Tasks $servicio;
    private RepartoDao $repartoDao;

    /**
     * Patrón para ignorar la lista de tareas por defecto en Google Tasks
     */
    const PATRON_TAREA_DEFECTO = "/Mis tareas|Lista de/";

    /**
     * Máximo de repartos en una lista de repartos
     */
    const MAX_RESULTADOS = 100;

    /**
     * Constructor objeto ListaRepartoDao
     * 
     * Para realizar las operaciones de persistencia requiere un objeto de acceso al servicio de Google Tasks
     * 
     * @param Tasks $servicio Acceso al API de Google Tasks
     * 
     * @returns Objeto ListaRepartoDao
     */
    public function __construct(Tasks $servicio) {
        $this->servicio = $servicio;
        $this->repartoDao = new RepartoDao($servicio);
    }

    /**
     * Persiste por primera vez el estado de un objeto ListaReparto
     *
     * 
     * @param Reparto $listaReparto reparto a persistir
     * 
     * @returns Bool Resultado de la operación
     */
    public function crea(ListaReparto $listaReparto): bool {
        $result = true;
        $taskList = new TaskList();
        try {
            $taskList->setTitle($listaReparto->getNombre());
            $taskListInstance = $this->servicio->tasklists->insert($taskList);
            $listaReparto->setId($taskListInstance->getId());
        } catch (Google_Exception $ex) {
            $result = false;
        }
        return ($result);
    }

    /**
     * Actualiza el estado de un objeto ListaReparto
     *
     * 
     * @param Reparto $listaReparto reparto a modificar
     * 
     * @returns Bool Resultado de la operación
     */
    public function modifica(ListaReparto $listaReparto): bool {
        $result = true;
        for ($i = 0; $i < count($listaReparto->getRepartos()); $i++) {
            try {
                if ($i === 0) {
                    $this->servicio->tasks->move($listaReparto->getId(), $listaReparto->getRepartos()[$i]->getId());
                } else {
                    $this->servicio->tasks->move($listaReparto->getId(), $listaReparto->getRepartos()[$i]->getId(), ['previous' => $listaReparto->getRepartos()[$i - 1]->getId()]);
                }
            } catch (Google_Exception $ex) {
                $result = false;
            }
        }
        return ($result);
    }

    /**
     * Borra el estado de un objeto ListaReparto
     *
     * 
     * @param string $listaRepartoId Identificador de la lista de reparto
     * 
     * @returns Bool Resultado de la operación
     */
    public function elimina(string $listaRepartoId): bool {
        $result = true;
        try {
            $this->servicio->tasklists->delete($listaRepartoId);
        } catch (Google_Exception $ex) {
            $result = false;
        }
        return ($result);
    }

    /**
     * Recupera un objeto ListaReparto dado el identificador de la lista de repartos
     * 
     * @param string $listaRepartoId Identificador de la lista de reparto
     * @param string $repartoId Identificador del reparto
     * 
     * @returns Objeto ListaReparto asociado al identificador de la lista de repartos
     */
    public function recuperaPorId(string $id): ?ListaReparto {
        $taskList = $this->servicio->tasklists->get($id);
        if (!is_null($taskList)) {
            $listaReparto = new ListaReparto($taskList->getTitle());
            $listaReparto->setId($taskList->getId());
            $tasksList = $this->servicio->tasks->listTasks($taskList->getId());
            $repartos = [];
            foreach ($tasksList->getItems() as $task) {
                $repartos[intval($task->position)] = $this->repartoDao->recuperaPorListaIdRepartoId($id, $task->getId());
            }
            ksort($repartos);
            $listaReparto->setRepartos($repartos);
            return ($listaReparto);
        }
    }

    /**
     * Recupera todos los objetos de ListaReparto
     * 
     * 
     * @returns array ListaReparto con todas las listas de reparto almacenadas
     */
    public function recuperaTodo(): ?array {
        $optParams = ['maxResults' => self::MAX_RESULTADOS];
        $tasklists = $this->servicio->tasklists->listTasklists($optParams);
        $listasReparto = [];
        foreach ($tasklists->getItems() as $taskList) {
            if (!preg_match(self::PATRON_TAREA_DEFECTO, $taskList->getTitle())) {
                $listasReparto[] = $this->recuperaPorId($taskList->getId());
            }
        }
        return ($listasReparto);
    }

}
