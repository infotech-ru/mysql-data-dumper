# MysqlDataDumper

Simple helper class that allows to backup and restore data from MySQL database.

```php
$dumper = new Infotech\MysqlDataDumper\MysqlDataDumper();
$dumper->addConnection($pdoInstance);
$dumper->backup(); // saves all rows from all tables of given connection(s) into memory

// execute process that makes indirect data modifications

$dumper->restore(); // restores saved data into database

// execute another process

$dumper->restore(); // restores the data again
```