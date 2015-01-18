<?php
/**
 * Author: Rottenwood
 * Date Created: 16.01.15 1:34
 */

namespace Rottenwood\TicTacBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Rottenwood\TicTacBundle\Entity\Field;
use Rottenwood\TicTacBundle\Entity\Game;
use Rottenwood\TicTacBundle\Entity\Player;

class GameService {

    /** @var EntityManager $em */
    private $em;
    /** @var EntityRepository $playerRepository */
    private $playerRepository;

    public function __construct(EntityManager $em) {
        $this->em = $em;
        $this->playerRepository = $this->em->getRepository('RottenwoodTicTacBundle:Player');
    }

    /**
     * Создание новой игры
     * @return Game
     */
    public function startNewGame() {
        $players = new ArrayCollection(
            [
                $this->initialisePlayer('X', 'крестики'),
                $this->initialisePlayer('O', 'нолики'),
            ]
        );

        if (Game::NUMBER_OF_PLAYERS > 2) {
            $letters = $this->createLettersArray();
            for ($i = 2; $i < Game::NUMBER_OF_PLAYERS; $i++) {
                $symbol = array_rand(array_diff($letters, ['X', 'O']));
                $symbol = ucfirst($letters[$symbol]);

                $players->add($this->initialisePlayer($symbol));
            }
        }

        $this->em->flush();

        return new Game($players);
    }

    /**
     * Массив названий всех клеток поля
     * @return array
     */
    public function getAllFieldsNames() {
        $letters = $this->createLettersArray();

        $fields = [];
        for ($i = 0; $i < Game::BOARD_AXIS_Y; $i++) {
            for ($x = 1; $x <= Game::BOARD_AXIS_X; $x++) {
                $fields[] = $letters[$i] . $x;
            }
        }

        return $fields;
    }

    /**
     * Массив свободных клеток поля
     * @param Game $game
     * @return array
     */
    public function getEmptyFields(Game $game) {
        $occupiedFieldNames =
            $game->getFields()->map(function (Field $field) {
                return $field->getName();
            });

        return array_diff($this->getAllFieldsNames(), $occupiedFieldNames->toArray());
    }

    /**
     * Создание массива с буквами для обозначения строк
     * @return array
     */
    public function createLettersArray() {
        return range('a', 'z');
    }

    public function checkWinner(Game $game) {

    }

    /**
     * Определение текущего игрока
     * @param Game $game
     * @return Player
     */
    public function getCurrentPlayer(Game $game) {
        $players = $game->getPlayers();

        if ($game->getFields()->isEmpty() || !$players->current()) {
            $player = $players->first();
        } else {
            $player = $players->current();
        }

        $players->next();

        return $player;
    }

    /**
     * Создание и инициализация игрока
     * @param string $symbol
     * @param string $name
     * @return Player
     */
    private function initialisePlayer($symbol, $name = '') {
        $player = $this->playerRepository->findOneByName($name);

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