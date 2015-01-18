<?php
/**
 * Author: Rottenwood
 * Date Created: 18.01.15 22:27
 */

namespace Rottenwood\TicTacBundle\Tests\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Rottenwood\TicTacBundle\Entity\Game;
use Rottenwood\TicTacBundle\Entity\Player;
use Rottenwood\TicTacBundle\Service\GameService;

class GameServiceTest extends \PHPUnit_Framework_TestCase {

    public function testNewGame() {
        $gameRepository = $this->createRepositoryMock();

        $em = $this->createEntityManagerMock();

        $em->expects($this->once())
           ->method('getRepository')
           ->will($this->returnValue($gameRepository));

        $gameService = new GameService($em);

        $result = $gameService->newGame();

        $this->assertEquals(2, $result->getPlayers()->count());
    }

    public function testGetAllFieldsNames() {
        $gameService = new GameService($this->createEntityManagerMock());

        $result = $gameService->getAllFieldsNames();

        $this->assertEquals(9, count($result));
        $this->assertEquals('b1', $result[3]);
    }

    public function testGetEmptyFields() {
        $game = $this->createNewGame();
        $em = $this->createEntityManagerMock();
        $gameService = new GameService($em);

        $result = $gameService->getEmptyFields($game);

        $this->assertEquals(9, count($result));
        $this->assertEquals('b1', $result[3]);
    }

    public function testCreateLettersArray() {
        $gameService = new GameService($this->createEntityManagerMock());

        $result = $gameService->createLettersArray();

        $this->assertEquals('f', $result[5]);
        $this->assertEquals('z', $result[25]);
        $this->assertEquals('P', $result[41]);
    }

    public function testIsGameOver() {
        $game = $this->createNewGame();
        $gameService = new GameService($this->createEntityManagerMock());

        $result = $gameService->isGameOver($game);

        $this->assertEquals(false, $result);
    }

    public function testGetCurrentPlayer() {
        $game = $this->createNewGame();
        $gameService = new GameService($this->createEntityManagerMock());

        $result = $gameService->getCurrentPlayer($game);

        $this->assertInstanceOf('Rottenwood\TicTacBundle\Entity\Player', $result);
    }

    public function testNextPlayer() {
        $game = $this->createNewGame();
        $gameService = new GameService($this->createEntityManagerMock());

        $result = $gameService->nextPlayer($game);

        $this->assertInstanceOf('Rottenwood\TicTacBundle\Entity\Player', $result);
    }

    public function testInitialisePlayer() {
        $method = self::getMethod('initialisePlayer');

        $gameRepository = $this->createRepositoryMock();

        $em = $this->createEntityManagerMock();
        $em->expects($this->once())
           ->method('getRepository')
           ->will($this->returnValue($gameRepository));

        $gameService = new GameService($em);

        $result = $method->invokeArgs($gameService, ['X', 'крестиков']);

        $this->assertInstanceOf('Rottenwood\TicTacBundle\Entity\Player', $result);
        $this->assertEquals('крестиков', $result->getName());

        $result = $method->invokeArgs($gameService, ['S']);

        $this->assertInstanceOf('Rottenwood\TicTacBundle\Entity\Player', $result);
        $this->assertEquals('буквы S', $result->getName());
    }

    public function testComputeWinningCombinations() {
        $method = self::getMethod('computeWinningCombinations');
        $gameService = new GameService($this->createEntityManagerMock());

        $result = $method->invokeArgs($gameService, [1, 2, 2, range('a', 'z')]);

        $this->assertEquals(4, count($result));
        $this->assertEquals('row', array_keys($result)[0]);
        $this->assertEquals('column', array_keys($result)[1]);
        $this->assertEquals('diagonal45', array_keys($result)[2]);
        $this->assertEquals('diagonal135', array_keys($result)[3]);
        $this->assertEquals('d', array_keys($result)[3][0]);
    }

    public function testComputeDiagonal() {
        $method = self::getMethod('computeDiagonal');
        $gameService = new GameService($this->createEntityManagerMock());

        $result = $method->invokeArgs($gameService, [1, 2, 2, 3, range('a', 'z')]);

        $this->assertEquals(2, count($result));
        $this->assertEquals('b2', $result[0]);
        $this->assertEquals('c3', $result[1]);
    }

    public function testComputeColumn() {
        $method = self::getMethod('computeColumn');
        $gameService = new GameService($this->createEntityManagerMock());

        $result = $method->invokeArgs($gameService, [3, 1, 3, range('a', 'z')]);

        $this->assertEquals(2, count($result));
        $this->assertEquals('d3', $result[0]);
        $this->assertEquals('b3', $result[1]);
    }

    public function testComputeRow() {
        $method = self::getMethod('computeRow');
        $gameService = new GameService($this->createEntityManagerMock());

        $result = $method->invokeArgs($gameService, [3, 1, 'a']);

        $this->assertEquals(2, count($result));
        $this->assertEquals('a1', $result[0]);
        $this->assertEquals('a3', $result[1]);
    }

    private function createNewGame() {
        $firstPlayer = new Player();
        $firstPlayer->setName('тест');
        $firstPlayer->setSymbol('Т');

        $secondPlayer = new Player();
        $secondPlayer->setName('тест');
        $secondPlayer->setSymbol('Т');

        return new Game(new ArrayCollection([$firstPlayer, $secondPlayer]));
    }

    private function createEntityManagerMock() {
        return $this->getMockBuilder('Doctrine\ORM\EntityManager')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    private function createRepositoryMock() {
        return $this->getMockBuilder('Doctrine\ORM\EntityRepository')
                    ->disableOriginalConstructor()
                    ->getMock();
    }

    private static function getMethod($name) {
        $class = new \ReflectionClass('Rottenwood\TicTacBundle\Service\GameService');
        $method = $class->getMethod($name);
        $method->setAccessible(true);

        return $method;
    }
}