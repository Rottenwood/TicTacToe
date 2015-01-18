<?php
/**
 * Author: Rottenwood
 * Date Created: 16.01.15 1:00
 */

namespace Rottenwood\TicTacBundle\Command;

use Rottenwood\TicTacBundle\Entity\Field;
use Rottenwood\TicTacBundle\Entity\Game;
use Rottenwood\TicTacBundle\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Helper\TableHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

class GameCommand extends ContainerAwareCommand {

    /** @var GameService $gameService */
    private $gameService;

    protected function configure() {
        $this->setName('game:start')->setDescription('Start tic-tac-toe game now!');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->gameService = $this->getContainer()->get('game');
        $table = $this->getHelper('table');
        $questionHelper = $this->getHelper('question');

        $game = $this->gameService->startNewGame();

        $output->writeln(['Новая игра начинается!', '']);

        while ($this->gameService->getEmptyFields($game)) {
            $this->drawTable($table, $output);
            $this->makeRound($game, $input, $output, $questionHelper);
        }

        $output->writeln('Игра завершена в ничью!');
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
        $letters = $this->gameService->createLettersArray();

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
        $output->writeln('');
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

    /**
     * Запуск и просчет игрового раунда
     * @param Game            $game
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @param QuestionHelper  $questionHelper
     */
    private function makeRound(Game $game,
                               InputInterface $input,
                               OutputInterface $output,
                               QuestionHelper $questionHelper) {
        $currentPlayer = $this->gameService->getCurrentPlayer($game);

        $questionChoice = new ChoiceQuestion(
            sprintf('Сейчас ходят %s:', $currentPlayer->getName()),
            $this->gameService->getEmptyFields($game)
        );
        $questionChoice->setPrompt('Введите номер пустой клетки: ');
        $questionChoice->setErrorMessage('Выбранное поле занято или не существует!');

        $fieldName = $questionHelper->ask($input, $output, $questionChoice);

        // Добавление поля
        $field = new Field($game, $currentPlayer, $fieldName);

        $game->addField($field);

        $output->writeln([sprintf('Вы заняли клетку %s.', $field->getName()), '']);
    }
}