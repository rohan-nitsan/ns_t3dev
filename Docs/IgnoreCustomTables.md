
# Ignore custom tables from Analyze Database

There are two ways of ignoring your custom ables from analyzing database tools.

## 1st way ##

In this way, we ignore the custom tables using **AlterTableDefinitionStatementsEvent**.

Step 1: Register Event in **Services.yaml**

[`Configuration/Services.yaml`](Configuration/Services.yaml)

```
Vendor\YourExtension\EventListener\ConnectionMigrator:
    tags:
      - name: event.listener
        identifier: 'table-connection'
        method: 'schemaEvent'
        event: TYPO3\CMS\Core\Database\Event\AlterTableDefinitionStatementsEvent
```

Step 2: Create a file for the Event Listener

[`Classes/EventListener/ConnectionMigrator.php`](Classes/EventListener/ConnectionMigrator.php)

Store your table prefix in `$tablePrefix` variable. 


If you want to ignore some specific tables, then you can register your tables in `$tables` array and ignore or remove line no. **23** to **28** in [`Classes/EventListener/ConnectionMigrator.php`](Classes/EventListener/ConnectionMigrator.php).

## 2nd way ##

In this way, we ignore the custom tables using **System Objects**.

Step 1: Register System Object  in **ext_localconf.php**.

[`ext_localconf.php`](ext_localconf.php)

```
//----------------------------------------------------------------------------------------------------------------------
// Database compare hide own tables
//----------------------------------------------------------------------------------------------------------------------
    // override connection migrator
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Database\\Schema\\ConnectionMigrator'] = [
        'className' => 'Vendor\\YourExtension\\Database\\Schema\\ConnectionMigrator'
    ];
//----------------------------------------------------------------------------------------------------------------------
    // Ignore tables with prefixes 
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['DATABASE_COMPARE']['IGNORE_TABLE_PREFIXES'] = [
        'your_table_prefix',
    ];

    // Ignore individual table 
    $GLOBALS['TYPO3_CONF_VARS']['SYS']['DATABASE_COMPARE']['IGNORE_TABLE_NAMES'] = [
        'your_table_name',
    ];
//---------------------------------------------------------------------------------------------------------------------- 
```

Step 2: Create a file for the **ConnectionMigrator.php**

[`Classes/Database/Schema/ConnectionMigrator`](Classes/Database/Schema/ConnectionMigrator.php)


