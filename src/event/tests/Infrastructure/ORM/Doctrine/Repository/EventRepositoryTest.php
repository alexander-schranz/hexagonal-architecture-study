<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Infrastructure\ORM\Doctrine\Repository;

use Doctrine\Common\EventManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Tools\ResolveTargetEntityListener;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;
use FrameworkCompatibilityProject\Event\Domain\Model\Event;
use FrameworkCompatibilityProject\Event\Domain\Model\EventInterface;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslation;
use FrameworkCompatibilityProject\Event\Domain\Model\EventTranslationInterface;
use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;
use FrameworkCompatibilityProject\Event\Infrastructure\ORM\Doctrine\Repository\EventRepository;
use FrameworkCompatibilityProject\Event\Tests\Domain\Repository\EventRepositoryTestTrait;
use PHPUnit\Framework\TestCase;

class EventRepositoryTest extends TestCase
{
    use EventRepositoryTestTrait;

    private ?EntityManager $entityManager = null;

    /**
     * @var string
     */
    protected $databaseFile = __DIR__ . '/../../../../var/cache/doctrine.sqlite';

    protected function createEventRepository(): EventRepositoryInterface
    {
        return new EventRepository($this->getEntityManager());
    }

    protected function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    protected function purge()
    {
        if (file_exists($this->databaseFile)) {
            unlink($this->databaseFile);
        }
    }

    protected function tearDown(): void
    {
        if ($this->entityManager) {
            $this->entityManager->close();
            $this->entityManager = null;
        }
    }

    private function getEntityManager(): EntityManagerInterface
    {
        if ($this->entityManager) {
            return $this->entityManager;
        }

        if (!is_dir(\dirname($this->databaseFile))) {
            mkdir(\dirname($this->databaseFile), 0777, true);
        }

        $connection = [
            'driver' => 'pdo_sqlite',
            'path' => $this->databaseFile,
        ];

        $config = Setup::createConfiguration(
            true,
            __DIR__ . '/../../../../var/cache'
        );

        $namespaces = [
            __DIR__ . '/../../../../../src/Infrastructure/ORM/Doctrine/config' => 'FrameworkCompatibilityProject\Event\Domain\Model',
        ];
        $driver = new SimplifiedXmlDriver($namespaces);

        $config->setMetadataDriverImpl($driver);

        $eventManager = new EventManager();
        $resolveTargetEntityListener = new ResolveTargetEntityListener();
        $resolveTargetEntityListener->addResolveTargetEntity(
            EventInterface::class,
            Event::class,
            []
        );
        $resolveTargetEntityListener->addResolveTargetEntity(
            EventTranslationInterface::class,
            EventTranslation::class,
            []
        );
        $eventManager->addEventListener(Events::loadClassMetadata, $resolveTargetEntityListener);

        $this->entityManager = EntityManager::create($connection, $config, $eventManager);

        $schemaTool = new SchemaTool($this->entityManager);
        $classes = $this->entityManager->getMetadataFactory()->getAllMetadata();

        if (!file_exists($this->databaseFile)) {
            $schemaTool->createSchema($classes);
        } else {
            $schemaTool->updateSchema($classes);
        }

        return $this->entityManager;
    }
}
