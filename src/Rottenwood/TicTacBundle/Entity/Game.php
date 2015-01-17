<?php

namespace Rottenwood\TicTacBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Игровая сессия
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
     * @ORM\OneToMany(targetEntity="Field", mappedBy="game")
     **/
    private $fields;

    public function __construct() {
        $this->fields = new ArrayCollection();
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
    public function getFields() {
        return $this->fields;
    }

    /**
     * @param ArrayCollection $fields
     */
    public function setFields($fields) {
        $this->fields = $fields;
    }

    /**
     * Добавление игрового поля
     * @param Field $field
     */
    public function addField($field) {
        $this->getFields()->add($field);
    }
}
