<?php

namespace Rottenwood\TicTacBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Player
 * @ORM\Table(name="players")
 * @ORM\Entity
 */
class Player {

    /**
     * @var integer
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", length=100)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="symbol", type="string", length=1)
     */
    private $symbol;

    /**
     * Get id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set name
     * @param string $name
     * @return Player
     */
    public function setName($name) {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * Set symbol
     * @param string $symbol
     * @return Player
     */
    public function setSymbol($symbol) {
        $this->symbol = $symbol;

        return $this;
    }

    /**
     * Get symbol
     * @return string
     */
    public function getSymbol() {
        return $this->symbol;
    }
}
