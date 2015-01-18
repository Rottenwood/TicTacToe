<?php

namespace Rottenwood\TicTacBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
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

    /**
     * @ORM\ManyToMany(targetEntity="Player")
     **/
    private $players;

    public function __construct(ArrayCollection $players) {
        $this->players = $players;
        $this->fields = new ArrayCollection();
    }

    /**
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
     * Поиск клетки игрового поля по ее имени
     * @param string $name
     * @return \Doctrine\Common\Collections\Collection|static
     */
    public function getFieldByName($name) {
        $expr = Criteria::expr();
        $criteria = Criteria::create();
        $criteria->where(
            $expr->eq('name', $name)
        );
        $criteria->setMaxResults(1);

        return $this->fields->matching($criteria);
    }
}
