<?php

namespace Rottenwood\TicTacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Game
 * @ORM\Table()
 * @ORM\Entity
 */
class Game {

    const BOARD_AXIS_X = 3;
    const BOARD_AXIS_Y = 3;

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Крестики
     * @var array
     * @ORM\Column(name="tics", type="simple_array")
     */
    private $tics = [];

    /**
     * Нолики
     * @var array
     * @ORM\Column(name="tacs", type="simple_array")
     */
    private $tacs = [];


    /**
     * Get id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set tics
     * @param array $tics
     * @return Game
     */
    public function setTics(array $tics) {
        $this->tics = $tics;

        return $this;
    }

    /**
     * Get tics
     * @return array
     */
    public function getTics() {
        return $this->tics;
    }

    /**
     * Добавление крестика
     * @param $tic
     */
    public function addTic($tic) {
        $tics = $this->getTics();
        $tics[] = $tic;
        $this->setTics($tics);
    }

    /**
     * Set tacs
     * @param array $tacs
     * @return Game
     */
    public function setTacs(array $tacs) {
        $this->tacs = $tacs;

        return $this;
    }

    /**
     * Get tacs
     * @return array
     */
    public function getTacs() {
        return $this->tacs;
    }

    /**
     * Добавление нолика
     * @param $tac
     */
    public function addTac($tac) {
        $tacs = $this->getTacs();
        $tacs[] = $tac;
        $this->setTacs($tacs);
    }
}
