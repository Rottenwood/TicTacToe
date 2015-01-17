<?php
/**
 * Author: Rottenwood
 * Date Created: 16.01.15 1:34
 */

namespace Rottenwood\TicTacBundle\Service;

use Doctrine\ORM\EntityManager;
use Rottenwood\TicTacBundle\Entity\Game;
use Rottenwood\TicTacBundle\Entity\Player;

class GameService {

    /** @var EntityManager $em */
    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function startNewGame() {
        $game = new Game();

        $playerTic = $this->initialisePlayer('X', 'крестики');
        $playerTac = $this->initialisePlayer('O', 'нолики');

        if (Game::NUMBER_OF_PLAYERS > 2) {
            $letters = $this->createLettersArray();
            for ($i = 2; $i < Game::NUMBER_OF_PLAYERS; $i++) {
                $symbol = array_rand(array_diff($letters, ['X', 'O']));
                $symbol = ucfirst($letters[$symbol]);

                $this->initialisePlayer($symbol);
            }
        }

        $this->em->flush();

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
        $occupiedFields = array_merge($game->getSymbols());

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

    public function checkWinner($game) {

    }

    public function currentPlayer($game) {

    }

    private function initialisePlayer($symbol, $name = '') {
        $player = $this->em->getRepository('RottenwoodTicTacBundle:Player')->findByName($name);

        if (!$player) {
            if (!$name) {
                $name = 'буквы ' . $symbol;
            }

            $player = new Player();
            $player->setName($name);
            $player->setSymbol($symbol);
            $this->em->persist($player);
        }

        return $player;
    }
}