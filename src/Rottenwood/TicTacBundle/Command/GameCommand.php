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

    const MENU_GAME_SAVE = 0;
    const MENU_GAME_NEW = 1;
    const MENU_GAME_LOAD = 2;

    /** @var GameService $gameService */
    private $gameService;

    protected function configure() {
        $this->setName('game:start')->setDescription('Start tic-tac-toe game now!');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $this->gameService = $this->getContainer()->get('game');
        $table = $this->getHelper('table');
        $questionHelper = $this->getHelper('question');

        $menuItem = $this->runMenu($questionHelper, $input, $output);

        if ($menuItem == self::MENU_GAME_NEW) {
            $game = $this->gameService->newGame();
            $output->writeln(['Новая игра начинается!', '']);
        } else {
            $game = $this->loadGame($questionHelper, $input, $output);
        }

        if ($game) {
            $this->drawTable($game, $table, $output);

            $runNextRound = true;
            while ($this->gameService->getEmptyFields($game)
                && !$this->gameService->isGameOver($game)
                && $runNextRound) {
                $currentPlayer = $this->gameService->getCurrentPlayer($game);
                $runNextRound = $this->makeRound($game, $input, $output, $questionHelper);
                $this->drawTable($game, $table, $output);
            }

            if (isset($currentPlayer) && $this->gameService->isGameOver($game)) {
                $output->writeln(sprintf('Игра завершена. Победили %s!', $currentPlayer->getName()));
            } elseif (!$runNextRound) {
                $em->persist($game);
                $output->writeln('Ваша игра была записана. До новой встречи!');
            } else {
                $output->writeln('Игра завершена в ничью!');
            }
        } else {
            $output->writeln('Не найдено ни одной сохраннной игры.');
        }

        $em->flush();
    }

    /**
     * Отрисовка таблицы
     * @param Game            $game
     * @param TableHelper     $table
     * TableHelper задепрекейтили, но getHelper его все еще возвращает
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

        $choices = [self::MENU_GAME_SAVE => 'Сохранить игру и выйти'];
        $choices = array_merge($choices, $this->gameService->getEmptyFields($game));

        $question = new ChoiceQuestion(
            sprintf('Сейчас ходят %s:', $currentPlayer->getName()),
            $choices
        );
        $question->setPrompt('Введите номер пустой клетки: ');
        $question->setErrorMessage('Выбранное поле занято или не существует!');

        $fieldName = $questionHelper->ask($input, $output, $question);
        $answerId = array_search($fieldName, $question->getChoices());

        if ($answerId == self::MENU_GAME_SAVE) {
            return false;
        }

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
            self::MENU_GAME_NEW
        );
        $question->setPrompt('Каким будет ваш выбор?: ');
        $question->setErrorMessage('Пожалуйста, выберите номер из списка!');

        $menuItem = $questionHelper->ask($input, $output, $question);

        return array_search($menuItem, $question->getChoices());
    }

    /**
     * Загрузка сохраненной игры
     * @param QuestionHelper  $questionHelper
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return bool|Game
     */
    private function loadGame(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output) {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $gameRepository = $em->getRepository('RottenwoodTicTacBundle:Game');

        $savedGames = $gameRepository->findAll();

        if (!$savedGames) {
            return false;
        }

        $question = new ChoiceQuestion(
            'Список сохраненных игр:',
            array_map(function (Game $game) {
                return $game->getId();
            },
                $savedGames)
        );
        $question->setPrompt('Какую игру вы хотите загрузить?: ');
        $question->setErrorMessage('Пожалуйста, выберите номер из списка!');

        $gameId = $questionHelper->ask($input, $output, $question);

        $game = $gameRepository->find($gameId);
        $em->remove($game);

        return $game;
    }
}
