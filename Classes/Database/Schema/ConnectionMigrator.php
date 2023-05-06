<?php

declare(strict_types=1);

namespace NITSAN\NsT3dev\Database\Schema;

use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Table;

class ConnectionMigrator extends \TYPO3\CMS\Core\Database\Schema\ConnectionMigrator
{
    protected function buildSchemaDiff(bool $renameUnused = TRUE): SchemaDiff
    {
        return $this->cleanSchemaDiff(
            parent::buildSchemaDiff($renameUnused)
        );
    }
   
    protected function cleanSchemaDiff(SchemaDiff $schemaDiff): SchemaDiff
    {
        $schemaDiff->newTables     = $this->cleanTables($schemaDiff->newTables);
        $schemaDiff->changedTables = $this->cleanTables($schemaDiff->changedTables);
        $schemaDiff->removedTables = $this->cleanTables($schemaDiff->removedTables);
        
        return $schemaDiff;
    }
    
    protected function cleanTables(array $tables): array
    {
        $ignorePrefixes = $GLOBALS['TYPO3_CONF_VARS']['SYS']['DATABASE_COMPARE']['IGNORE_TABLE_PREFIXES'] ?? [];
        $ignoreTables   = $GLOBALS['TYPO3_CONF_VARS']['SYS']['DATABASE_COMPARE']['IGNORE_TABLE_NAMES'] ?? [];
        $deletedPrefix  = $this->deletedPrefix;
        
        return array_filter(
            $tables,
            function ($table) use ($ignorePrefixes, $ignoreTables, $deletedPrefix) {
                if ($table instanceof Table) {
                    $tableName = $table->getName();
                } else {
                    $tableName = trim($table->newName ?: $table->name, '`');
                }
                
                // If the tablename has a deleted prefix strip it of before comparing
                // it against the list of valid table names so that drop operations
                // don't get removed.
                if (strpos($tableName, $deletedPrefix) === 0) {
                    $tableName = substr($tableName, strlen($deletedPrefix));
                }
    
                if (in_array($tableName, $ignoreTables, true))
                    return FALSE;
                
                foreach($ignorePrefixes AS $prefix)
                    if (strpos($tableName, $prefix) === 0)
                        return FALSE;
                
                return TRUE;
            }
        );
    }
}