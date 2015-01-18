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
    public function newGame() {
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

    /**
     * Определение того, подошел ли ход игры к ее завершению. Расчет позиций текущего игрока
     * Расчет производится исходя из последнего сделанного игроком хода
     * @param Game $game
     * @return bool
     */
    public function isGameOver(Game $game) {
        $fields = $game->getFields();
        $lastField = $fields->last();

        if (!$lastField) {
            return false;
        }

        $lastFieldName = $fields->last()->getName();
        $letters = $this->createLettersArray();

        $lastFieldX = (int)substr($lastFieldName, 1);
        $lastFieldY = $lastFieldName[0];

        $fieldsNeedToWin = min(Game::BOARD_AXIS_X, Game::BOARD_AXIS_Y) <= 5 ? 2 : 4;
        $winningCombinations = $this->computeWinningCombinations($lastFieldX, $lastFieldY, $fieldsNeedToWin, $letters);

        foreach ($winningCombinations as $winningCombination) {
            $winningCombination = array_filter($winningCombination,
                function ($fieldName) use ($game, $lastField) {
                    $field = $game->getFieldByName($fieldName)->first();

                    return $field && $field->getPlayer() === $lastField->getPlayer();
                });

            if (count($winningCombination) >= $fieldsNeedToWin) {
                return true;
            }
        }

        return false;
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

        return $player;
    }

    /**
     * Определение текущего игрока и передача хода следующему
     * @param Game $game
     * @return Player
     */
    public function nextPlayer(Game $game) {
        $player = $this->getCurrentPlayer($game);
        $game->getPlayers()->next();

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

    /**
     * Расчет комбинаций выигрышных полей
     * @param int   $x
     * @param int   $y
     * @param int   $fieldsNeedToWin
     * @param array $letters
     * @return array
     */
    private function computeWinningCombinations($x, $y, $fieldsNeedToWin, array $letters) {
        $yKey = array_search($y, $letters);

        $row = [];
        $column = [];
        $diagonal45 = [];
        $diagonal135 = [];

        for ($i = 1; $i <= $fieldsNeedToWin; $i++) {
            $axisXUp = $x + $i;
            $axisXDown = $x - $i;
            $axisYRight = $yKey + $i;
            $axisYLeft = $yKey - $i;

            $row = array_merge($row, $this->computeRow($axisXUp, $axisXDown, $y));
            $column = array_merge($column, $this->computeColumn($axisYRight, $axisYLeft, $x, $letters));
            $diagonal45 = array_merge($diagonal45,
                                      $this->computeDiagonal($axisYLeft, $axisYRight, $axisXUp, $axisXDown, $letters));
            $diagonal135 = array_merge($diagonal135,
                                       $this->computeDiagonal($axisYLeft, $axisYRight, $axisXDown, $axisXUp, $letters));
        }

        return [
            'row'         => $row,
            'column'      => $column,
            'diagonal45'  => $diagonal45,
            'diagonal135' => $diagonal135,
        ];
    }

    /**
     * Расчет диагональных линий полей, необходимых для выигрыша
     * @param int   $axisYFirst
     * @param int   $axisYSecond
     * @param int   $axisXFirst
     * @param int   $axisXSecond
     * @param array $letters
     * @return array
     */
    private function computeDiagonal($axisYFirst, $axisYSecond, $axisXFirst, $axisXSecond, array $letters) {
        $diagonal = [];

        if (array_key_exists($axisYFirst, $letters)) {
            $diagonal[] = $letters[$axisYFirst] . $axisXFirst;
        }

        if (array_key_exists($axisYSecond, $letters)) {
            $diagonal[] = $axisXSecond <= Game::BOARD_AXIS_Y ? $letters[$axisYSecond] . $axisXSecond : '';
        }

        return $diagonal;
    }

    /**
     * Расчет колонны полей, необходимых для выигрыша
     * @param int   $axisYRight
     * @param int   $axisYLeft
     * @param int   $x
     * @param array $letters
     * @return array
     */
    private function computeColumn($axisYRight, $axisYLeft, $x, array $letters) {
        $column = [];
        if (array_key_exists($axisYRight, $letters)) {
            $column[] = $letters[$axisYRight] . $x;
        }

        if (array_key_exists($axisYLeft, $letters)) {
            $column[] = $letters[$axisYLeft] . $x;
        }

        return $column;
    }

    /**
     * Расчет горизонтальной линии полей, необходимых для выигрыша
     * @param int    $axisXUp
     * @param int    $axisXDown
     * @param string $y
     * @return array
     */
    private function computeRow($axisXUp, $axisXDown, $y) {
        return [
            $y . $axisXDown,
            $y . $axisXUp,
        ];
    }
}