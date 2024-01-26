<?php

namespace Zephyrforge\Zephyrforge;

use krzysztofzylka\DatabaseManager\Exception\DatabaseManagerException;
use krzysztofzylka\DatabaseManager\Table;
use Zephyrforge\Zephyrforge\Exception\HiddenException;
use Zephyrforge\Zephyrforge\Libs\Log\Log;
use Zephyrforge\Zephyrforge\Libs\Model\LoadModel;

/**
 * Model
 */
class Model
{

    use LoadModel;

    /**
     * Model name
     * @var string
     */
    public string $name;

    /**
     * Controller instance
     * @var Controller
     */
    public Controller $controller;

    /**
     * Database column name or false
     * @var bool
     */
    public string|false $useTable;

    /**
     * Database table instance
     * @var Table
     */
    public Table $tableInstance;

    /**
     * Creates a new record in the table with the specified data.
     * @param array $data An array of data to be inserted into the table.
     * @return bool True if the record is successfully created, false if there is no database connection or the useTable property is false.
     * @throws HiddenException If an error occurs while executing the database query.
     */
    public function create(array $data): bool
    {
        if (!$_ENV['DATABASE'] && $this->useTable !== false) {
            return false;
        }

        try {
            return $this->tableInstance->insert(
                data: $data
            );
        } catch (DatabaseManagerException $exception) {
            Log::throwableLog($exception);

            throw new HiddenException(
                $exception->getHiddenMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Reads records from the table based on the specified conditions, columns, and order by.
     * @param array|null $conditions An optional array of conditions to filter the records.
     * @param array|null $columns An optional array of columns to select.
     * @param string|null $orderBy An optional string specifying the column to order the records.
     * @return array|false An array of records if found, or false if there is no database connection or the useTable property is false.
     * @throws HiddenException If an error occurs while executing the database query.
     */
    public function read(
        ?array $conditions = null,
        ?array $columns = null,
        ?string $orderBy = null
    ): false|array
    {
        if (!$_ENV['DATABASE'] && $this->useTable !== false) {
            return false;
        }

        try {
            return $this->tableInstance->find(
                condition: $conditions,
                columns: $columns,
                orderBy: $orderBy
            );
        } catch (DatabaseManagerException $exception) {
            Log::throwableLog($exception);

            throw new HiddenException(
                $exception->getHiddenMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Updates a record in the table with the specified ID using the provided data.
     * @param int $id The ID of the record to update.
     * @param array $data An array containing the new data for the record.
     * @return bool Returns true if the record was successfully updated, or false if there is no database connection or the useTable property is false.
     * @throws HiddenException If an error occurs while executing the database query.
     */
    public function update(int $id, array $data): bool
    {
        if (!$_ENV['DATABASE'] && $this->useTable !== false) {
            return false;
        }

        try {
            return $this->tableInstance->setId($id)->update(
                data: $data
            );
        } catch (DatabaseManagerException $exception) {
            Log::throwableLog($exception);

            throw new HiddenException(
                $exception->getHiddenMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Delete a record from the table by ID.
     * @param int $id The ID of the record to delete.
     * @return bool Returns true if the record is successfully deleted, false otherwise.
     * @throws HiddenException Throws a HiddenException if an error occurs during the deletion process.
     */
    public function delete(int $id): bool
    {
        if (!$_ENV['DATABASE'] && $this->useTable !== false) {
            return false;
        }

        try {
            return $this->tableInstance->delete(
                id: $id
            );
        } catch (DatabaseManagerException $exception) {
            Log::throwableLog($exception);

            throw new HiddenException(
                $exception->getHiddenMessage(),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Retrieves the structure of the table.
     * @return array An array representing the structure of the table.
     */
    public function tableStructure(): array
    {
        return [];
    }

}