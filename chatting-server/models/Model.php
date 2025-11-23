<?php
abstract class Model
{

    protected static string $table;
    protected static string $primary_key = "id";

    public static function find(mysqli $connection, int $id)
    {
        $sql = sprintf(
            "SELECT * from %s WHERE %s = ?",
            static::$table,
            static::$primary_key
        );

        $query = $connection->prepare($sql);
        $query->bind_param("i", $id);
        $query->execute();

        $data = $query->get_result()->fetch_assoc();

        return $data ? new static($data) : null;
    }

    public static function findAll(mysqli $connection)
    {
        $sql = sprintf("SELECT * from %s", static::$table);

        $query = $connection->prepare($sql);
        $query->execute();
        $result = $query->get_result();

        $objects = [];
        while ($data = $result->fetch_assoc()) {
            $objects[] = new static($data);
        }

        return $objects;
    }
    public static function deleteRow(mysqli $connection, int $id)
    {
        $sql = sprintf(
            "DELETE FROM %s WHERE %s = ?",
            static::$table,
            static::$primary_key
        );

        $query = $connection->prepare($sql);

        $query->bind_param("i", $id);
        if ($query->execute()) {
            return true;
        } else {
            return false;
        }
    }
    public function add(mysqli $connection, array $data)
    {
        $columns = array_keys($data);
        $placeholders = str_repeat('?,', count($columns) - 1) . '?';
        $sql = sprintf("INSERT INTO %s (%s) VALUES (%s)", static::$table, implode(',', $columns), $placeholders);
        $query = $connection->prepare($sql);
        $types = '';
        $allvalues = [];
        foreach ($data as $value) {
            if (is_int($value)) {
                $types .= 'i';
            } else {
                $types .= 's';
            }
            $allvalues[] = $value;
        }
        $query->bind_param($types, ...$allvalues);
        if ($query->execute()) {
            return $connection->insert_id;
        } else {
            return false;
        }
    }
    public static function update(mysqli $connection, int $id, array $data)
    {
        if (empty($data)) {
            return false;
        }
        $columns = array_keys($data);
        $placeholders = implode('=?, ', $columns) . '=?';
        $sql = sprintf("UPDATE %s SET %s WHERE %s = ?", static::$table, $placeholders, static::$primary_key);
        $query = $connection->prepare($sql);
        $types = '';
        $values = [];
        foreach ($data as $val) {
            if (is_int($val)) {
                $types .= 'i';
            } else {
                $types .= 's';
            }
            $values[] = $val;
        }
        $types .= 'i';
        $values[] = $id;
        $query->bind_param($types, ...$values);
        if ($query->execute()) {
            return true;
        } else {
            return false;
        }
    }
}
