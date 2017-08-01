<?php

namespace Ostrolucky\AppEventDispatcher\Test\Adapter;

use Concise\Core\TestCase;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Ostrolucky\AppEventDispatcher\Adapter\DoctrineEventManagerAdapter;
use Ostrolucky\AppEventDispatcher\AppEventDispatcher;

class DoctrineEventManagerAdapterTest extends TestCase
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
        EntityManager::create($connection, $configuration, new DoctrineEventManagerAdapter(new AppEventDispatcher));
    }
}
