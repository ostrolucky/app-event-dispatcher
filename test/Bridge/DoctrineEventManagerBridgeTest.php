<?php

namespace Ostrolucky\AppEventDispatcher\Test\Bridge;

use Concise\Core\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Ostrolucky\AppEventDispatcher\AppEventDispatcher;
use Ostrolucky\AppEventDispatcher\Bridge\DoctrineEventManagerBridge;

class DoctrineEventManagerBridgeTest extends TestCase
{
    /**
     * @expectedException \Doctrine\ORM\ORMException
     */
    public function testCanBeInjected()
    {
        /** @var Connection $connection */
        $connection = $this->mock(Connection::class)->get();
        /** @var Configuration $configuration */
        $configuration = $this->niceMock(Configuration::class)->get();
        EntityManager::create($connection, $configuration, new DoctrineEventManagerBridge(new AppEventDispatcher));
    }
}