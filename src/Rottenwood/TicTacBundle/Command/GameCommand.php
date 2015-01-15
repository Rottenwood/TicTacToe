<?php
/**
 * Author: Rottenwood
 * Date Created: 16.01.15 1:00
 */

namespace Rottenwood\TicTacBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GameCommand extends ContainerAwareCommand {

    protected function configure() {
        $this->setName('game:start')->setDescription('Start tic-tac-toe game now!');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {

    }
}