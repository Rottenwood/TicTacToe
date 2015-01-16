<?php
/**
 * Author: Rottenwood
 * Date Created: 16.01.15 1:00
 */

namespace Rottenwood\TicTacBundle\Command;

use Rottenwood\TicTacBundle\Entity\Game;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GameCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('game:start')->setDescription('Start tic-tac-toe game now!');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $table = $this->getHelper('table');
        $game = $this->getContainer('game');

        $output->writeln('Новая игра начинается!');

        $this->drawTable($table, $output);
    }

    /**
     * Отрисовка таблицы
     * Некоторые методы консольных хелперов задепрекейтили, но альтернативу пока не дали
     * @param TableHelper     $table
     * @param OutputInterface $output
     */
    private function drawTable(TableHelper $table, OutputInterface $output) {
        $imageTic = ' X ';
        $imageTac = ' O ';
        $imageBorder = '---';
        $imageSpace = ' ';
        $letters = range('a', 'z');

        $headers = range(1, Game::BOARD_AXIS_X);
        array_unshift($headers, $imageSpace);
        $headers = $this->addSpaces($headers, $imageSpace);

        $table->setHeaders($headers);

        $rows = [];
        for ($i = 1; $i <= Game::BOARD_AXIS_Y; $i++) {
            $row = array_pad([], Game::BOARD_AXIS_X, $imageSpace);
            array_unshift($row, $letters[$i - 1]);
            $rows[] = $this->addSpaces($row, $imageSpace);

            if ($i != Game::BOARD_AXIS_Y) {
                $borderRow = array_pad([], Game::BOARD_AXIS_X, $imageBorder);
                array_unshift($borderRow, $imageBorder);
                $rows[] = $borderRow;
            }
        }

        $table->setRows($rows);
        $table->render($output);
    }

    /**
     * Добавление "пробелов" ко всем элементам массива
     * @param array  $array
     * @param string $imageSpace
     * @return array
     */
    private function addSpaces(array $array, $imageSpace) {
        return array_map(
            function ($field) use ($imageSpace) {
                return $imageSpace . $field . $imageSpace;
            },
            $array
        );
    }
}