<?php

namespace NITSAN\NsT3dev\EventListener;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent;

class ConnectionMigrator
{
    /**
     * Returns the current schema for the install tool
     *
     * @param array $list List of SQL statements
     * @return array SQL statements required for the install tool
     */
    public function Schema(array $list):array
    {   
        $tablePrefix = 'your_table_prefix';
        $connectionPool = GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);
        $connection = $connectionPool->getConnectionByName('Default');
        $tables = [];
        $schemaManager = $connection->createSchemaManager();
        foreach ($schemaManager->listTableNames() as $tableName) {
            if (! \str_starts_with($tableName, $tablePrefix)) {
                continue;
            }
            $tables[] = $tableName;
        }
        
        foreach($tables as $mytable){
            $query = "SHOW CREATE TABLE `$mytable`";
            $result = $connection->query($query);
            $row = $result->fetch();
            
            $createTableQuery = $row['Create Table'];

            $createTableQuery = str_replace('"', '`', $createTableQuery);
            $createTableQuery = preg_replace('/CONSTRAINT `[a-zA-Z0-9_-]+` /', '', $createTableQuery);
            $createTableQuery = preg_replace('/ DEFAULT CHARSET=[^ ;]+/', '', $createTableQuery);
            $createTableQuery = preg_replace('/ COLLATE=[^ ;]+/', '', $createTableQuery);

            $list[] = $createTableQuery . ";\n";
        }
        return ['sqlString' => $list];
    }

    /**
     * Alter schema to avoid TYPO3 dropping Aimeos tables
     *
     * @param AlterTableDefinitionStatementsEvent $event Event object
     */
    public function schemaEvent(AlterTableDefinitionStatementsEvent $event)
    {
        $list = self::Schema([]);

        foreach ($list['sqlString'] ?? [] as $sql) {
            $event->addSqlData($sql);
        }
    }
}
