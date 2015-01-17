<?php

namespace Rottenwood\TicTacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Игровое поле
 * @ORM\Table(name="fields")
 * @ORM\Entity
 */
class Field {

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=5)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="fields")
     **/
    private $game;

    /**
     * @ORM\ManyToOne(targetEntity="Player")
     **/
    private $player;

    function __construct(Game $game, Player $player, $name) {
        $this->game = $game;
        $this->player = $player;
        $this->name = $name;
    }

    /**
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return Game
     */
    public function getGame() {
        return $this->game;
    }

    /**
     * @param Game $game
     */
    public function setGame($game) {
        $this->game = $game;
    }

    /**
     * @return Player
     */
    public function getPlayer() {
        return $this->player;
    }

    /**
     * @param Player $player
     */
    public function setPlayer($player) {
        $this->player = $player;
    }
}
