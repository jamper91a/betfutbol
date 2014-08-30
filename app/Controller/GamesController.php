<?php

App::uses('AppController', 'Controller');

/**
 * Games Controller
 *
 * @property Game $Game
 * @property PaginatorComponent $Paginator
 */
class GamesController extends AppController {

    /**
     * Components
     *
     * @var array
     */
    public $components = array('Paginator');

    /**
     * index method
     *
     * @return void
     */
    public function index() {
        $this->Game->recursive = 0;
        $this->set('games', $this->Paginator->paginate());
    }

    /**
     * view method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function view($id = null) {
        if (!$this->Game->exists($id)) {
            throw new NotFoundException(__('Invalid game'));
        }
        $options = array('conditions' => array('Game.' . $this->Game->primaryKey => $id));
        $this->set('game', $this->Game->find('first', $options));
    }

    /**
     * add method
     *
     * @return void
     */
    public function add() {
        if ($this->request->is('post')) {
            $this->Game->create();
            if ($this->Game->save($this->request->data)) {

                $this->Session->setFlash(__('The game has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The game could not be saved. Please, try again.'));
            }
        }
    }

    /**
     * edit method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function edit($id = null) {
        if (!$this->Game->exists($id)) {
            throw new NotFoundException(__('Invalid game'));
        }
        if ($this->request->is(array('post', 'put'))) {
            if ($this->Game->save($this->request->data)) {
                $this->Session->setFlash(__('The game has been saved.'));
                return $this->redirect(array('action' => 'index'));
            } else {
                $this->Session->setFlash(__('The game could not be saved. Please, try again.'));
            }
        } else {
            $options = array('conditions' => array('Game.' . $this->Game->primaryKey => $id));
            $this->request->data = $this->Game->find('first', $options);
        }
    }

    /**
     * delete method
     *
     * @throws NotFoundException
     * @param string $id
     * @return void
     */
    public function delete($id = null) {
        $this->Game->id = $id;
        if (!$this->Game->exists()) {
            throw new NotFoundException(__('Invalid game'));
        }
        $this->request->allowMethod('post', 'delete');
        if ($this->Game->delete()) {
            $this->Session->setFlash(__('The game has been deleted.'));
        } else {
            $this->Session->setFlash(__('The game could not be deleted. Please, try again.'));
        }
        return $this->redirect(array('action' => 'index'));
    }

    /**
     * Se encarga de listar los partidos, para que se puedan crear las apuestas
     */
    public function listar() {
        $fecha = date("Y-m-d H:i:s");
        $options = array(
            "conditions" => array(
                "Game.fecha_juego >" => $fecha
            )
        );
        $partidos = $this->Game->find('all', $options);
        //$partidos=  $this->Game->findAllByVisible("1");
        $this->set("partidos", $partidos);
    }

    public function encurso() {
        $fecha = date("Y-m-d H:i:s");
        $options = array(
            "conditions" => array(
//                "Game.fecha_juego >" => $fecha,
                "Game.finalizado" => 0
            )
        );
        $games = $this->Game->find('all', $options);
        $this->set("games", $games);
    }

    public function finalizar($id) {
        if ($this->request->is(array('post', 'put'))) {
            if ($this->Game->save($this->request->data)) {
                $this->Session->setFlash(__('El juego se cerro correctamente.'));
                //Ahora verifico quien gano o perdio en todas las filas
                $this->loadModel("Row");
                $rows = $this->Row->findAllByGameId($id);
                debug($rows);
                foreach ($rows as $row) {
                    //VAriable que determina quien gano, si local, visitante o empate
                    $gano = "";
                    //Diferencia de goles
                    $diferencia = "";
                    //Total de goles
                    $totalGoles = "";
                    if ($this->request->data["Game"]["goles_local"] > $this->request->data["Game"]["goles_visitante"]) {
                        $gano = $this->request->data["Game"]["local"];
                        $diferencia = $this->request->data["Game"]["goles_local"] - $this->request->data["Game"]["goles_visitante"];
                    } else if ($this->request->data["Game"]["goles_local"] < $this->request->data["Game"]["goles_visitante"]) {
                        $gano = $this->request->data["Game"]["visitante"];
                        $diferencia = $this->request->data["Game"]["goles_visitante"] - $this->request->data["Game"]["goles_local"];
                    } else {
                        $gano = "Empate";
                        $diferencia = 0;
                    }
                    $totalGoles=$this->request->data["Game"]["goles_visitante"] + $this->request->data["Game"]["goles_local"];
                    
                    switch ($row["Row"]["tipo"]) {
                        case "ML":
                            //Para determinar si gano con ML, el equipo por el que aposto debio ganar

                            if ($row["Row"]["equipo"] == $gano)
                                $row["Row"]["estado"] = "2";
                            else
                                $row["Row"]["estado"] = "1";

                            break;
                        case "RL":
                            /**
                             * Para determinar si gano con RL debo:
                             * Si goles son negativos, el equipo debe perder
                             * Si goles son positivos, el equipo debe ganar
                             * Si goles de diferencia son mayores a los goles, gana
                             * Si goles de diferencia son iguales a los goles, empata
                             */
                            if ($row["Row"]["goles"] < 0) {
                                if ($row["Row"]["equipo"] == $gano) {
                                    if ($diferencia > $row["Row"]["goles"])
                                        $row["Row"]["estado"] = "2";
                                    else if ($diferencia == $row["Row"]["goles"])
                                        $row["Row"]["estado"] = "0";
                                    else
                                        $row["Row"]["estado"] = "1";
                                }
                            }else if ($row["Row"]["goles"] > 0) {
                                if ($row["Row"]["equipo"] != $gano) {
                                    if ($diferencia < abs($row["Row"]["goles"]))
                                        $row["Row"]["estado"] = "2";
                                    else if ($diferencia == abs($row["Row"]["goles"]))
                                        $row["Row"]["estado"] = "0";
                                    else
                                        $row["Row"]["estado"] = "1";
                                }
                            }
                            break;
                        case "A":
                            if($totalGoles> $row["Row"]["goles"])
                                $row["Row"]["estado"] = "2";
                            else if($totalGoles== $row["Row"]["goles"])
                                $row["Row"]["estado"] = "0";
                            else if($totalGoles < $row["Row"]["goles"])
                                $row["Row"]["estado"] = "1";
                            break;
                        case "B":
                            if($totalGoles< $row["Row"]["goles"])
                                $row["Row"]["estado"] = "2";
                            else if($totalGoles== $row["Row"]["goles"])
                                $row["Row"]["estado"] = "0";
                            else if($totalGoles > $row["Row"]["goles"])
                                $row["Row"]["estado"] = "1";
                            break;
                        default:
                            break;
                    }
                    $this->Row->save($row);
                }
                return $this->redirect(array('action' => 'encurso'));
            } else {
                $this->Session->setFlash(__('The game could not be saved. Please, try again.'));
            }
        }
        $game = $this->Game->findById($id);
        $this->set("game", $game);
    }

}