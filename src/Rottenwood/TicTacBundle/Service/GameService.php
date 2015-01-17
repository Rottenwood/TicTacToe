<?php
/**
 * Author: Rottenwood
 * Date Created: 16.01.15 1:34
 */

namespace Rottenwood\TicTacBundle\Service;

use Doctrine\ORM\EntityManager;
use Rottenwood\TicTacBundle\Entity\Game;

class GameService {

    /** @var EntityManager $em */
    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function newGame() {
        $game = new Game();

        return $game;
    }

    /**
     * Массив всех клеток поля
     * @return array
     */
    public function getAllFields() {
        $letters = $this->createLettersArray();

        $emptyFields = [];
        for ($i = 0; $i < Game::BOARD_AXIS_Y; $i++) {
            for ($x = 1; $x <= Game::BOARD_AXIS_X; $x++) {
                $emptyFields[] = $letters[$i] . $x;
            }
        }

        return $emptyFields;
    }

    /**
     * Массив свободных клеток поля
     * @param Game $game
     * @return array
     */
    public function getEmptyFields(Game $game) {
        $occupiedFields = array_merge($game->getTics(), $game->getTacs());

        $allFields = array_filter($this->getAllFields(),
            function ($field) use ($occupiedFields) {
                return !in_array($field, $occupiedFields);
            });

        return $allFields;
    }

    /**
     * Создание массива с буквами для обозначения строк
     * @return array
     */
    public function createLettersArray() {
        return range('a', 'z');
    }
}