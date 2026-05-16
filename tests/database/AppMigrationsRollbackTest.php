<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Services;

/**
 * Verifies that every App migration can roll back cleanly after a
 * fresh up-migrate pass. Catches inverse-operation bugs in
 * AddIndexesAndForeignKeys (drop FK, drop INDEX) and the create-table
 * migrations.
 *
 * @internal
 */
final class AppMigrationsRollbackTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'App';

    public function testMigrationsRollbackCleanly(): void
    {
        $runner = Services::migrations(null, null, false);
        $runner->setNamespace('App');

        $this->assertTrue($runner->regress(0, 'App'));

        $db        = db_connect();
        $remaining = ['user', 'paket', 'pemesanan', 'pembayaran', 'jadwal_produksi', 'portofolio'];
        foreach ($remaining as $table) {
            $this->assertFalse($db->tableExists($table), "Table {$table} should have been dropped");
        }
    }
}
