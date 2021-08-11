<?php

declare(strict_types=1);

namespace FrameworkCompatibilityProject\Event\Tests\Infrastructure\ORM\Cycle\Repository;

use Cycle\ORM\Factory;
use Cycle\ORM\ORM;
use Cycle\ORM\Schema as ORMSchema;
use Cycle\ORM\Transaction;
use Cycle\ORM\TransactionInterface;
use Cycle\Schema;
use FrameworkCompatibilityProject\Event\Domain\Repository\EventRepositoryInterface;
use FrameworkCompatibilityProject\Event\Infrastructure\ORM\Cycle\Repository\EventRepository;
use FrameworkCompatibilityProject\Event\Infrastructure\ORM\Cycle\Schema\EventSchemaGenerator;
use FrameworkCompatibilityProject\Event\Tests\Domain\Repository\EventRepositoryTestTrait;
use PHPUnit\Framework\TestCase;
use Spiral\Database\Config\DatabaseConfig;
use Spiral\Database\DatabaseManager;
use Spiral\Database\Driver\HandlerInterface;
use Spiral\Database\Driver\MySQL\MySQLDriver;
use Spiral\Database\Driver\SQLite\SQLiteDriver;

class EventRepositoryTest extends TestCase
{
    use EventRepositoryTestTrait;

    /**
     * @var string
     */
    protected $databaseFile = __DIR__ . '/../../../../var/cache/cycle.sqlite';

    protected ?TransactionInterface $transaction = null;

    protected ?ORM $orm = null;

    protected function createEventRepository(): EventRepositoryInterface
    {
        $this->orm = $this->getORM();
        $this->transaction = $this->getTransaction();

        return new EventRepository(
            $this->orm,
            $this->transaction
        );
    }

    protected function purge()
    {
        if (file_exists($this->databaseFile)) {
            $database = $this->getORM()->getFactory()->database('default');

            foreach ($database->getTables() as $table) {
                $schema = $table->getSchema();

                foreach ($schema->getForeignKeys() as $foreign) {
                    $schema->dropForeignKey($foreign->getColumns());
                }

                $schema->save(HandlerInterface::DROP_FOREIGN_KEYS);
            }

            foreach ($database->getTables() as $table) {
                $schema = $table->getSchema();
                $schema->declareDropped();
                $schema->save();
            }

            $this->orm = null;
        }
    }

    protected function flush(): void
    {
        $this->getTransaction()->run();
    }

    protected function tearDown(): void
    {
        if ($this->orm) {
            $this->orm->getHeap()->clean();
            $this->orm->getFactory()->database()->getDriver()->disconnect();
        }

        $this->orm = null;
        $this->transaction = null;
    }

    private function getORM(): ORM
    {
        if ($this->orm) {
            return $this->orm;
        }

        $dbal = new DatabaseManager(
            new DatabaseConfig([
                'default' => 'default',
                'databases' => [
                    'default' => ['connection' => 'mysql'],
                ],
                'connections' => [
                    'sqlite' => [
                        'driver' => SQLiteDriver::class,
                        'connection' => 'sqlite:' . $this->databaseFile,
                        'username' => '',
                        'password' => '',
                    ],
                    'mysql' => [
                        'driver' => MySQLDriver::class,
                        'connection' => 'mysql:host=127.0.0.1:3357;dbname=cycle_orm',
                        'username' => 'root',
                        'password' => '',
                    ],
                ],
            ])
        );

        $registry = new Schema\Registry($dbal);

        $compiledSchema = (new Schema\Compiler())->compile($registry, [
            new EventSchemaGenerator(),
            new Schema\Generator\ResetTables(),       // re-declared table schemas (remove columns)
            new Schema\Generator\GenerateRelations(), // generate entity relations
            new Schema\Generator\ValidateEntities(),  // make sure all entity schemas are correct
            new Schema\Generator\RenderTables(),      // declare table schemas
            new Schema\Generator\RenderRelations(),   // declare relation keys and indexes
            new Schema\Generator\SyncTables(),        // sync table changes to database
            new Schema\Generator\GenerateTypecast(),  // typecast non string columns
        ]);

        $this->orm = new ORM(new Factory($dbal), new ORMSchema($compiledSchema));

        return $this->orm;
    }

    private function getTransaction(): TransactionInterface
    {
        if ($this->transaction) {
            return $this->transaction;
        }

        return $this->transaction = new Transaction($this->getORM());
    }
}
