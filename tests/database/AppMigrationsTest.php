<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * End-to-end smoke test that runs every App migration (including the
 * indexes/foreign-keys migration) and the full DatabaseSeeder chain
 * against the SQLite testing connection.
 *
 * @internal
 */
final class AppMigrationsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate     = true;
    protected $migrateOnce = false;
    protected $refresh     = true;
    protected $namespace   = 'App';
    protected $seed        = \App\Database\Seeds\DatabaseSeeder::class;

    public function testCoreTablesArePopulatedAfterSeed(): void
    {
        $db = db_connect();

        $this->assertSame(6, (int) $db->table('user')->countAllResults());
        $this->assertSame(6, (int) $db->table('paket')->countAllResults());
        $this->assertSame(6, (int) $db->table('pemesanan')->countAllResults());
        $this->assertSame(6, (int) $db->table('pembayaran')->countAllResults());
        $this->assertSame(6, (int) $db->table('portofolio')->countAllResults());
    }

    public function testJadwalProduksiSeederPopulatesAllColumns(): void
    {
        $db  = db_connect();
        $row = $db->table('jadwal_produksi')->get()->getRowArray();

        $this->assertNotNull($row, 'expected at least one jadwal_produksi row');
        $this->assertNotEmpty($row['tanggal_shooting'] ?? '');
        $this->assertNotEmpty($row['tanggal_mulai_editing'] ?? '');
        $this->assertNotEmpty($row['catatan_produksi'] ?? '');
    }

    public function testPengeluaranOperasionalSeederUsesMigrationColumns(): void
    {
        $db = db_connect();
        if (! $db->tableExists('pengeluaran_operasional')) {
            $this->markTestSkipped('pengeluaran_operasional table missing');
        }

        $row = $db->table('pengeluaran_operasional')->get()->getRowArray();
        $this->assertNotNull($row);
        $this->assertNotEmpty($row['nama_pengeluaran'] ?? '');
        $this->assertGreaterThan(0, (int) ($row['nominal'] ?? 0));
        $this->assertNotEmpty($row['tanggal_pengeluaran'] ?? '');
    }
}
