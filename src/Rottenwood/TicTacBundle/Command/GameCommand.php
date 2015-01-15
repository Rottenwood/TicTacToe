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

        for ($y = 1; $y <= Game::BOARD_AXIS_Y; $y++) {
            $this->drawRow($table, $output);
        }
    }

    /**
     * Отрисовка табличной строки
     * Некоторые методы консольных хелперов задепрекейтили, но альтернативу пока не дали
     * @param TableHelper     $table
     * @param OutputInterface $output
     */
    private function drawRow(TableHelper $table, OutputInterface $output) {
        $imageTic = ' X ';
        $imageTac = ' O ';
        $imageEmpty = '   ';

        $table->setRows([array_pad([], Game::BOARD_AXIS_X, $imageEmpty)]);
        $table->render($output);
    }
}