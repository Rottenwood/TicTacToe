<?php

namespace Rottenwood\TicTacBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Game
 * @ORM\Table(name="games")
 * @ORM\Entity
 */
class Game {

    const NUMBER_OF_PLAYERS = 2;
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
     * @ORM\ManyToMany(targetEntity="Player")
     * @ORM\JoinTable()
     **/
    private $players;

    /**
     * Крестики, Нолики и другие символы в случае если игроков больше двух
     * @var array
     * @ORM\Column(name="symbols", type="simple_array")
     */
    private $symbols = [];

    public function __construct() {
        $this->players = new ArrayCollection();
    }

    /**
     * Get id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getPlayers() {
        return $this->players;
    }

    /**
     * @param ArrayCollection $players
     */
    public function setPlayers($players) {
        $this->players = $players;
    }

    /**
     * @return array
     */
    public function getSymbols() {
        return $this->symbols;
    }

    /**
     * @param array $symbols
     */
    public function setSymbols($symbols) {
        $this->symbols = $symbols;
    }

    /**
     * Добавление символа
     * @param $symbol
     */
    public function addSymbol($symbol) {
        $symbols = $this->getSymbols();
        $symbols[] = $symbol;
        $this->setSymbols($symbols);
    }
}
