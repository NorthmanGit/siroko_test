<?php

namespace App\Tests\Infrastructure;

use PHPUnit\Framework\TestCase;
use Doctrine\DBAL\DriverManager;

class PostgresConnectionTest extends TestCase
{
    public function testPostgresConnection(): void
    {
        // Agafa la URL de la BD des de l'entorn
        $databaseUrl = $_ENV['DATABASE_URL'] ?? 'pgsql://user:pass@db:5432/app';

        // Crea una connexió amb Doctrine DBAL
        $connection = DriverManager::getConnection(['url' => $databaseUrl]);

        // Executa una query simple
        $result = $connection->executeQuery('SELECT 1')->fetchOne();

        $this->assertEquals(1, (int) $result);
    }
}
