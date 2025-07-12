<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

abstract class SqlRepository
{
    protected function executeQuery(string $sql, ?array $params = null)
    {
        $conn = new PDO('sqlite:' . __DIR__ . '/../../database.db');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        try {
            // Prepare and execute SQL statement
            $statement = $conn->prepare($sql);
            if ($params !== null) {
                $statement->execute($params);
            } else {
                $statement->execute();
            }

            // Return results if any, otherwise return 0
            if ($statement->columnCount() > 0) {
                return $statement->fetchAll(PDO::FETCH_ASSOC);
            }
            return 0;
        } catch (\Exception $e) {
            throw $e;
        } finally {
            $conn = null;
        }
    }
}
