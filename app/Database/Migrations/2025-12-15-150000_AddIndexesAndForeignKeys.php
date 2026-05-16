<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Adds secondary indexes and foreign key constraints across the
 * domain tables. Runs after all CreateXTable migrations so every
 * referenced table is guaranteed to exist.
 *
 * On non-MySQL drivers (e.g. SQLite used by PHPUnit) foreign keys
 * are skipped because ALTER TABLE ADD CONSTRAINT is not supported.
 * Indexes are added on every driver.
 */
class AddIndexesAndForeignKeys extends Migration
{
    /**
     * @var array<int, array{table:string, name:string, column:string}>
     */
    private array $indexes = [
        ['table' => 'user',            'name' => 'idx_user_role',                'column' => 'role'],
        ['table' => 'paket',           'name' => 'idx_paket_kategori',           'column' => 'kategori'],
        ['table' => 'paket',           'name' => 'idx_paket_is_active',          'column' => 'is_active'],
        ['table' => 'pemesanan',       'name' => 'idx_pemesanan_status',         'column' => 'status_pemesanan'],
        ['table' => 'pemesanan',       'name' => 'idx_pemesanan_tanggal_acara',  'column' => 'tanggal_acara'],
        ['table' => 'pembayaran',      'name' => 'idx_pembayaran_status_verif',  'column' => 'status_verifikasi'],
        ['table' => 'pembayaran',      'name' => 'idx_pembayaran_jenis',         'column' => 'jenis_pembayaran'],
        ['table' => 'jadwal_produksi', 'name' => 'idx_jadwal_status',            'column' => 'status_produksi'],
        ['table' => 'portofolio',      'name' => 'idx_portofolio_kategori',      'column' => 'kategori'],
    ];

    /**
     * @var array<int, array{name:string, table:string, column:string, ref_table:string, ref_column:string, on_delete:string, on_update:string}>
     */
    private array $foreignKeys = [
        [
            'name'       => 'fk_pemesanan_user',
            'table'      => 'pemesanan',
            'column'     => 'id_user',
            'ref_table'  => 'user',
            'ref_column' => 'id_user',
            'on_delete'  => 'RESTRICT',
            'on_update'  => 'CASCADE',
        ],
        [
            'name'       => 'fk_pemesanan_paket',
            'table'      => 'pemesanan',
            'column'     => 'id_paket',
            'ref_table'  => 'paket',
            'ref_column' => 'id_paket',
            'on_delete'  => 'RESTRICT',
            'on_update'  => 'CASCADE',
        ],
        [
            'name'       => 'fk_pembayaran_pemesanan',
            'table'      => 'pembayaran',
            'column'     => 'id_pemesanan',
            'ref_table'  => 'pemesanan',
            'ref_column' => 'id_pemesanan',
            'on_delete'  => 'CASCADE',
            'on_update'  => 'CASCADE',
        ],
        [
            'name'       => 'fk_jadwal_pemesanan',
            'table'      => 'jadwal_produksi',
            'column'     => 'id_pemesanan',
            'ref_table'  => 'pemesanan',
            'ref_column' => 'id_pemesanan',
            'on_delete'  => 'CASCADE',
            'on_update'  => 'CASCADE',
        ],
        [
            'name'       => 'fk_jadwal_editor',
            'table'      => 'jadwal_produksi',
            'column'     => 'id_editor',
            'ref_table'  => 'user',
            'ref_column' => 'id_user',
            'on_delete'  => 'RESTRICT',
            'on_update'  => 'CASCADE',
        ],
        [
            'name'       => 'fk_portofolio_paket',
            'table'      => 'portofolio',
            'column'     => 'id_paket',
            'ref_table'  => 'paket',
            'ref_column' => 'id_paket',
            'on_delete'  => 'CASCADE',
            'on_update'  => 'CASCADE',
        ],
    ];

    public function up()
    {
        foreach ($this->indexes as $idx) {
            $this->addIndexIfMissing($idx['table'], $idx['name'], $idx['column']);
        }

        if (! $this->isMysql()) {
            return;
        }

        foreach ($this->foreignKeys as $fk) {
            $this->addForeignKeyIfMissing($fk);
        }
    }

    public function down()
    {
        if ($this->isMysql()) {
            foreach (array_reverse($this->foreignKeys) as $fk) {
                $this->dropForeignKeyIfExists($fk['table'], $fk['name']);
            }
        }

        foreach (array_reverse($this->indexes) as $idx) {
            $this->dropIndexIfExists($idx['table'], $idx['name']);
        }
    }

    private function isMysql(): bool
    {
        $driver = strtolower((string) ($this->db->DBDriver ?? ''));

        return in_array($driver, ['mysqli', 'mysql', 'mariadb', 'pdo_mysql'], true);
    }

    private function physicalTable(string $logicalTable): string
    {
        return $this->db->DBPrefix . $logicalTable;
    }

    private function addIndexIfMissing(string $table, string $indexName, string $column): void
    {
        if (! $this->db->tableExists($table) || $this->indexExists($table, $indexName)) {
            return;
        }

        $sql = sprintf(
            'CREATE INDEX %s ON %s (%s)',
            $this->db->escapeIdentifiers($indexName),
            $this->db->escapeIdentifiers($this->physicalTable($table)),
            $this->db->escapeIdentifiers($column),
        );

        $this->db->query($sql);
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! $this->db->tableExists($table) || ! $this->indexExists($table, $indexName)) {
            return;
        }

        if ($this->isMysql()) {
            $sql = sprintf(
                'ALTER TABLE %s DROP INDEX %s',
                $this->db->escapeIdentifiers($this->physicalTable($table)),
                $this->db->escapeIdentifiers($indexName),
            );
        } else {
            $sql = 'DROP INDEX ' . $this->db->escapeIdentifiers($indexName);
        }

        $this->db->query($sql);
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $physical = $this->physicalTable($table);

        if ($this->isMysql()) {
            $database = $this->db->getDatabase();
            $row      = $this->db->query(
                'SELECT COUNT(*) AS c FROM information_schema.statistics
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?',
                [$database, $physical, $indexName],
            )->getRowArray();

            return ((int) ($row['c'] ?? 0)) > 0;
        }

        // SQLite (and as a safe fallback): use PRAGMA index_list
        $rows = $this->db->query('PRAGMA index_list(' . $this->db->escape($physical) . ')')->getResultArray();

        foreach ($rows as $row) {
            if (($row['name'] ?? '') === $indexName) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array{name:string, table:string, column:string, ref_table:string, ref_column:string, on_delete:string, on_update:string} $fk
     */
    private function addForeignKeyIfMissing(array $fk): void
    {
        if (! $this->db->tableExists($fk['table']) || ! $this->db->tableExists($fk['ref_table'])) {
            return;
        }

        if ($this->foreignKeyExists($fk['table'], $fk['name'])) {
            return;
        }

        $sql = sprintf(
            'ALTER TABLE %s ADD CONSTRAINT %s FOREIGN KEY (%s) REFERENCES %s (%s) ON DELETE %s ON UPDATE %s',
            $this->db->escapeIdentifiers($this->physicalTable($fk['table'])),
            $this->db->escapeIdentifiers($fk['name']),
            $this->db->escapeIdentifiers($fk['column']),
            $this->db->escapeIdentifiers($this->physicalTable($fk['ref_table'])),
            $this->db->escapeIdentifiers($fk['ref_column']),
            $fk['on_delete'],
            $fk['on_update'],
        );

        $this->db->query($sql);
    }

    private function dropForeignKeyIfExists(string $table, string $constraint): void
    {
        if (! $this->db->tableExists($table) || ! $this->foreignKeyExists($table, $constraint)) {
            return;
        }

        $sql = sprintf(
            'ALTER TABLE %s DROP FOREIGN KEY %s',
            $this->db->escapeIdentifiers($this->physicalTable($table)),
            $this->db->escapeIdentifiers($constraint),
        );

        $this->db->query($sql);
    }

    private function foreignKeyExists(string $table, string $constraint): bool
    {
        $database = $this->db->getDatabase();
        $row      = $this->db->query(
            'SELECT COUNT(*) AS c FROM information_schema.table_constraints
             WHERE table_schema = ? AND table_name = ?
               AND constraint_name = ? AND constraint_type = ?',
            [$database, $this->physicalTable($table), $constraint, 'FOREIGN KEY'],
        )->getRowArray();

        return ((int) ($row['c'] ?? 0)) > 0;
    }
}
