<?php

namespace Doctrine\DBAL\Migrations\Tests\Functional;

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Migrations\Configuration\Configuration;
use Doctrine\DBAL\Migrations\OutputWriter;
use Symfony\Component\Console\Output\ConsoleOutput;

class HtmlSqlQueryUpTest extends \Doctrine\DBAL\Migrations\Tests\MigrationTestCase
{
    /**
     * @var Configuration
     */
    private $config;

    /**
     * @var Connection
     */
    private $connection;
    
    /**
     * @var OutputWriter
     */
    private $outputWriter;

    public function setUp()
    {
        $params = array('driver' => 'pdo_sqlite', 'memory' => true);
        $this->connection = DriverManager::getConnection($params);
        $console = new ConsoleOutput();
        foreach (array('p', 'ul', 'li') as $tag) {
            $console->setStyle($tag);
        }
        $this->outputWriter = new OutputWriter(function($message) use ($console) {
            $console->write($message);
        });
        $this->config = new Configuration($this->connection, $this->outputWriter);
        $this->config->setMigrationsNamespace('Doctrine\DBAL\Migrations\Tests\Functional');
        $this->config->setMigrationsDirectory('.');
    }

    public function testMigrateUpHtmlSqlQuery()
    {
        $version = new \Doctrine\DBAL\Migrations\Version($this->config, 1, 'Doctrine\DBAL\Migrations\Tests\Functional\MigrationMigrateUp');

        $this->assertFalse($this->config->hasVersionMigrated($version));
        $version->execute('up');

        $schema = $this->connection->getSchemaManager()->createSchema();
        $this->assertTrue($schema->hasTable('foo'));
        $this->assertTrue($schema->getTable('foo')->hasColumn('id'));
        $this->assertTrue($schema->getTable('foo')->hasColumn('description'));
        $this->assertTrue($this->config->hasVersionMigrated($version));
    }

    public function testMigrateDownHtmlSqlQuery()
    {
        $version = new \Doctrine\DBAL\Migrations\Version($this->config, 1, 'Doctrine\DBAL\Migrations\Tests\Functional\MigrationMigrateUp');

        $this->assertFalse($this->config->hasVersionMigrated($version));
        $version->execute('up');

        $schema = $this->connection->getSchemaManager()->createSchema();
        $this->assertTrue($schema->hasTable('foo'));
        $this->assertTrue($schema->getTable('foo')->hasColumn('id'));
        $this->assertTrue($schema->getTable('foo')->hasColumn('description'));
        $this->assertTrue($this->config->hasVersionMigrated($version));

        $version->execute('down');
        $schema = $this->connection->getSchemaManager()->createSchema();
        $this->assertFalse($schema->hasTable('foo'));
        $this->assertFalse($this->config->hasVersionMigrated($version));

    }

}

class MigrationMigrateUp extends \Doctrine\DBAL\Migrations\AbstractMigration
{
    public function down(Schema $schema)
    {
      $schema->dropTable('foo');
    }

    public function up(Schema $schema)
    {
        $this->_addSql('CREATE TABLE foo (id INTEGER NOT NULL, description TEXT NOT NULL)');
        
        $this->_addSql(sprintf(
            'INSERT INTO foo (id, description) VALUES (1, "%s")',
            '<p>This is a paragraph</p><ul><li>This is a list item</li></ul>'
        ));
    }
}
