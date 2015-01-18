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

    const MENU_GAME_NEW = 1;
    const MENU_GAME_LOAD = 2;

    /** @var GameService $gameService */
    private $gameService;

    protected function configure() {
        $this->setName('game:start')->setDescription('Start tic-tac-toe game now!');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $this->gameService = $this->getContainer()->get('game');
        $table = $this->getHelper('table');
        $questionHelper = $this->getHelper('question');

        $this->runMenu($questionHelper, $input, $output);

        $game = $this->gameService->startNewGame();

        $output->writeln(['Новая игра начинается!', '']);
        $this->drawTable($game, $table, $output);

        while ($this->gameService->getEmptyFields($game) && !$this->gameService->isGameOver($game)) {
            $currentPlayer = $this->gameService->getCurrentPlayer($game);
            $this->makeRound($game, $input, $output, $questionHelper);
            $this->drawTable($game, $table, $output);
        }

        if (isset($currentPlayer) && $this->gameService->isGameOver($game)) {
            $output->writeln(sprintf('Игра завершена. Победили %s!', $currentPlayer->getName()));
        } else {
            $output->writeln('Игра завершена в ничью!');
        }

    }

    /**
     * Отрисовка таблицы
     * Некоторые методы консольных хелперов задепрекейтили, но альтернативу пока не дали
     * @param Game            $game
     * @param TableHelper     $table
     * @param OutputInterface $output
     */
    private function drawTable(Game $game, TableHelper $table, OutputInterface $output) {
        $imageBorder = '---';
        $imageSpace = ' ';
        $letters = $this->gameService->createLettersArray();

        $headers = range(1, Game::BOARD_AXIS_X);
        array_unshift($headers, $imageSpace);
        $headers = $this->addSpaces($headers, $imageSpace);

        $table->setHeaders($headers);

        $rows = [];
        for ($i = 1; $i <= Game::BOARD_AXIS_Y; $i++) {
            $rowLetter = $letters[$i - 1];

            $row = [];
            for ($x = 1; $x <= Game::BOARD_AXIS_X; $x++) {
                $field = $game->getFieldByName($rowLetter . $x);

                $row[] = $field->isEmpty()
                    ? $imageSpace
                    : $field->current()->getPlayer()->getSymbol();
            }
            array_unshift($row, $rowLetter);
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
     * @return bool
     */
    private function makeRound(Game $game,
                               InputInterface $input,
                               OutputInterface $output,
                               QuestionHelper $questionHelper) {
        $currentPlayer = $this->gameService->nextPlayer($game);

        $question = new ChoiceQuestion(
            sprintf('Сейчас ходят %s:', $currentPlayer->getName()),
            $this->gameService->getEmptyFields($game)
        );
        $question->setPrompt('Введите номер пустой клетки: ');
        $question->setErrorMessage('Выбранное поле занято или не существует!');

        $fieldName = $questionHelper->ask($input, $output, $question);

        // Добавление поля
        $field = new Field($game, $currentPlayer, $fieldName);
        $game->addField($field);

        $output->writeln([sprintf('Вы заняли клетку "%s".', $field->getName()), '']);

        return true;
    }

    /**
     * Главное игровое меню
     * @param QuestionHelper  $questionHelper
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int
     */
    private function runMenu(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output) {
        $question = new ChoiceQuestion(
            'Добро пожаловать в Симфонические Крестики-Нолики!',
            [
                self::MENU_GAME_NEW  => 'Начать новую игру',
                self::MENU_GAME_LOAD => 'Загрузить старую',
            ],
            0
        );
        $question->setPrompt('Каким будет ваш выбор?: ');
        $question->setErrorMessage('Пожалуйста, выберите номер из списка!');

        $menuItem = $questionHelper->ask($input, $output, $question);

        return array_search($menuItem, $question->getChoices());
    }
}