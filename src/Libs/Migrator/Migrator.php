<?php

namespace Zephyrforge\Zephyrforge\Libs\Migrator;

use krzysztofzylka\DatabaseManager\CreateTable;
use krzysztofzylka\DatabaseManager\DatabaseManager;
use krzysztofzylka\DatabaseManager\Exception\DatabaseManagerException;
use krzysztofzylka\DatabaseManager\Exception\TransactionException;
use krzysztofzylka\DatabaseManager\Table;
use krzysztofzylka\DatabaseManager\Transaction;
use Krzysztofzylka\File\File;
use Throwable;
use Zephyrforge\Zephyrforge\Controller;
use Zephyrforge\Zephyrforge\Exception\HiddenException;
use Zephyrforge\Zephyrforge\Exception\MainException;
use Zephyrforge\Zephyrforge\Exception\NotFoundException;
use Zephyrforge\Zephyrforge\Kernel;
use Zephyrforge\Zephyrforge\Libs\Log\Log;

class Migrator
{

    /**
     * Migration data
     * @var array
     */
    private array $migrationData = [];


    /**
     * Table instance
     * @var Table
     */
    private Table $tableInstance;

    /**
     * Constructor for the class.
     *
     * It initializes and sets up the necessary table for migrations.
     * If the table does not exist, it creates the table with the required columns.
     *
     * @throws MainException If an exception occurs during the database operation.
     */
    public function __construct()
    {
        try {
            $this->tableInstance = new Table('migrations');

            if (!$this->tableInstance->exists()) {
                (new CreateTable())
                    ->setName('migrations')
                    ->addIdColumn()
                    ->addSimpleVarcharColumn('name', 32)
                    ->addDateModifyColumn()
                    ->addDateCreatedColumn()
                    ->execute();
            }
        } catch (DatabaseManagerException $exception) {
            Log::throwableLog($exception, $exception->getHiddenMessage());

            throw new MainException($exception->getMessage(), 500, $exception);
        }
    }

    /**
     * Run migration
     * @return void
     * @throws DatabaseManagerException
     * @throws HiddenException
     * @throws TransactionException
     */
    public function runMigrations(): void
    {
        $scan = scandir(Kernel::$projectPath . '/migrations');
        $scan = array_diff($scan, ['.', '..']);
        sort($scan);

        foreach ($scan as $fileName) {
            if (File::getExtension($fileName) !== 'sql') {
                continue;
            }

            $filePath = Kernel::$projectPath . '/migrations/' . $fileName;
            $name = str_replace('.sql', '', $fileName);

            if ($this->tableInstance->findCount(['migrations.name' => $name])) {
                continue;
            }

            Log::log('Execute migration', 'INFO', ['name' => $name, 'filePath' => $filePath, 'fileName' => $fileName]);

            $transaction = new Transaction();
            $transaction->begin();
            try {
                DatabaseManager::$connection->getConnection()->query(
                    file_get_contents($filePath)
                );

                $this->tableInstance->insert([
                    'name' => $name
                ]);

                $transaction->commit();;
            } catch (Throwable $throwable) {
                $transaction->rollback();

                Log::log($throwable, $throwable instanceof DatabaseManagerException ? $throwable->getHiddenMessage() : $throwable->getMessage());

                throw new HiddenException(
                    $throwable instanceof DatabaseManagerException ? $throwable->getHiddenMessage() : $throwable->getMessage(),
                    500,
                    $throwable
                );
            }
        }
    }

    /**
     * Creates migrations based on the defined table structures of the models.
     * Generates SQL statements for creating or dropping tables based on the model conditions.
     * @return void
     * @throws NotFoundException
     */
    public function createMigrations(): void
    {
        $models = $this->getModelList();
        $controller = new Controller();

        foreach ($models as $model) {
            $model = $controller->loadModel($model['name']);

            if (is_callable($model->tableInstance)) {
                continue;
            }

            $tableStructure = $model->tableStructure();

            // Ok
            if (empty($tableStructure) && !$model->tableInstance->exists()) {
                continue;
            }

            // Delete table
            if (empty($tableStructure) && $model->tableInstance->exists()) {
                $this->migrationData[] = 'DROP TABLE ' . $model->useTable;

                continue;
            }

            // Create table
            if (!empty($tableStructure) && !$model->tableInstance->exists()) {
                $columns = $this->generateColumns($tableStructure);
                $this->migrationData[] = 'CREATE TABLE ' . $model->useTable . ' (' . PHP_EOL . implode(',' . PHP_EOL, $columns) . PHP_EOL . ')';

                continue;
            }

            //update column
            $databaseTableStructure = $model->tableInstance->columnList();
            $tableStructureName = array_column($tableStructure, 'name');
            $databaseTableStructureName = array_column($databaseTableStructure, 'Field');

            //column to delete
            foreach ($databaseTableStructureName as $key => $value) {
                if (!in_array($value, $tableStructureName)) {
                    $this->migrationData[] = 'ALTER TABLE `' . $model->useTable . '` DROP COLUMN `' . $value . '`';
                    unset($databaseTableStructureName[$key], $databaseTableStructure[$key]);
                }
            }

            $databaseTableStructureName = array_values($databaseTableStructureName);

            //add column
            foreach ($tableStructureName as $key => $value) {
                if (!isset($databaseTableStructureName[$key]) || $databaseTableStructureName[$key] !== $value) {
                    $columnData = [$tableStructure[$key]];

                    if ($key === 0) {
                        $columnData[0]['first'] = true;
                    } else {
                        $columnData[0]['after'] = $databaseTableStructureName[$key - 1];
                    }

                    $columnString = $this->generateColumns($columnData);

                    array_splice($databaseTableStructure, $key, 0, [[
                        'Field' => $columnData[0]['name'],
                        'Type' => $columnData[0]['type'] . (@$columnData['length'][0] ? ('(' . $columnString['length'][0] . ')') : ''),
                        'Null' => ($columnString[0]['null'] ?? false) ? 'YES' : 'NO',
                        'Key' => ($columnString[0]['primary_key'] ?? false) ? 'PRI' : '',
                        'Default' => ($columnString[0]['default'] ?? false) ? 'YES' : 'NO',
                        'Extra' => ''
                    ]]);

                    $this->migrationData[] = 'ALTER TABLE `' . $model->useTable . '` ADD COLUMN ' . $columnString[0];
                    $databaseTableStructureName = array_column($databaseTableStructure, 'Field');
                }
            }
        }

        # Update differences
        foreach (array_keys($tableStructureName) as $key) {
            $columnStructure = $tableStructure[$key];
            $columnData = $this->generateColumns([$columnStructure]);
            $databaseColumnStructure = $databaseTableStructure[$key];
            $type = $columnStructure['type'];

            if (isset($columnStructure['length']) && $type !== 'int' && $columnStructure['length'] !== 11) {
                $type .= "({$columnStructure['length']})";
            }

            if (!isset($columnStructure['null'] )) {
                $columnStructure['null']  = 'false';
            }

            $changeName = $columnStructure['name'] !== $databaseColumnStructure['Field'];
            $changeType = $type !== $databaseColumnStructure['Type'];

            var_dump([$type, $databaseColumnStructure['Type']]);

            if ($changeName || $changeType) {
                $this->migrationData[] = 'ALTER TABLE `' . $model->useTable . '` CHANGE `' . $columnStructure['name'] . '` ' . $columnData[0];
            }
        }

        if (!empty($this->migrationData)) {
            $migrationFilePath = Kernel::$projectPath . '/migrations/' . time() . '.sql';
            file_put_contents($migrationFilePath, implode(';' . PHP_EOL, $this->migrationData) . ';');
        }
    }

    /**
     * Generate an array of formatted columns based on the given table structure.
     *
     * @param array $tableStructure The table structure containing information about each column.
     *                             Each column should have 'name', 'type', and optional 'length', 'default',
     *                             'null', 'auto_increment', 'primary_key', 'after', 'first'.
     * @return array An array of formatted columns.
     */
    private function generateColumns(array $tableStructure): array
    {
        $columns = [];

        foreach ($tableStructure as $column) {
            $name = $column['name'];
            $type = $column['type'];

            if (isset($column['length']) && $type !== 'int' && $column['length'] !== 11) {
                $type .= "({$column['length']})";
            }

            $default = '';
            if (isset($column['default'])) {
                $defaultVal = $column['default'];

                if (is_string($defaultVal) && @!$column['default_function']) {
                    $defaultVal = "'$defaultVal'";
                }

                $default = "DEFAULT $defaultVal";
            }

            $null = @$column['null'] ? 'NULL' : 'NOT NULL';
            $autoIncrement = @$column['auto_increment'] ? 'AUTO_INCREMENT' : '';
            $primaryKey = @$column['primary_key'] ? 'PRIMARY KEY' : '';
            $after = @$column['after'] ? ('AFTER ' . $column['after']) : '';
            $first = @$column['first'] ? 'FIRST' : '';

            $string = trim( "`$name` $type $null $default $autoIncrement $primaryKey $after $first");

            while(true) {
                if (!str_contains($string, '  ')) {
                    break;
                }

                $string = str_replace('  ', ' ', $string);
            }

            $columns[] = $string;
        }

        return $columns;
    }

    /**
     * Get the list of models available in the project's model directory.
     * @return array The array of model names without the '.php' extension.
     */
    private function getModelList(): array
    {
        $models = [];

        foreach (scandir(Kernel::$projectPath . '/model') as $modelFilename) {
            if (File::getExtension($modelFilename) !== 'php') {
                continue;
            }

            $name = str_replace('.php', '', $modelFilename);
            $class = '\\model\\' . $name;

            if (!class_exists($class)) {
                continue;
            }

            $models[] = [
                'name' => $name,
                'class' => $class
            ];
        }

        return $models;
    }

}