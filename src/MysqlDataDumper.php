<?php
/*
 * This file is part of the infotech/mysql-data-dumper package.
 *
 * (c) Infotech, Ltd
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Infotech\MysqlDataDumper;

use PDO;

/**
 * Allows to backup and restore data from MySQL database.
 */
class MysqlDataDumper
{
    /**
     * @var PDO[]
     */
    private $connections = [];

    /**
     * @var array
     */
    private $dataBackup = [];

    /**
     * Add PDO connection for backup and restore
     *
     * @param PDO $connection
     */
    public function addConnection(PDO $connection)
    {
        $this->connections[] = $connection;
    }

    /**
     * Save database state
     */
    public function backup()
    {
        foreach ($this->connections as $connId => $connection) {
            foreach ($this->fetchTableNames($connection) as $table) {
                $this->dataBackup[$connId . $table] = [];
                foreach ($connection->query('SELECT * FROM `' . $table . '`')->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $this->dataBackup[$connId . $table][] = $row;
                }
            }
        }
    }

    /**
     * Restore previously saved database state
     */
    public function restore()
    {
        foreach ($this->connections as $connId => $connection) {
            $connection->exec('SET FOREIGN_KEY_CHECKS=0');

            foreach ($this->fetchTableNames($connection) as $table) {

                $connection->exec('DELETE FROM `' . $table . '`');

                if (!empty($this->dataBackup[$connId . $table])) {
                    $formatValue = function ($value) use ($connection) {
                        return null === $value ? 'NULL' : $connection->quote($value);
                    };
                    $formatRow = function ($row) use ($formatValue) {
                        return '(' . implode(', ', array_map($formatValue, $row)) . ')';
                    };


                    $connection->exec(
                        'INSERT INTO `' . $table . '` VALUES '
                            . implode(', ', array_map($formatRow, $this->dataBackup[$connId . $table]))
                    );
                }
            }

            $connection->exec('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    /**
     * @param PDO $connection
     *
     * @return array
     */
    private function fetchTableNames($connection)
    {
        return $connection->query('SHOW FULL TABLES WHERE TABLE_TYPE = "BASE TABLE"')->fetchAll(PDO::FETCH_COLUMN);
    }

}
