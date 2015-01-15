<?php
/**
 * Author: Rottenwood
 * Date Created: 16.01.15 1:34
 */

namespace Rottenwood\TicTacBundle\Service;

use Doctrine\ORM\EntityManager;

class GameService {

    /** @var EntityManager $em */
    private $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }
}