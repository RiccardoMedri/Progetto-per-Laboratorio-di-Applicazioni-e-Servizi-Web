<?php

require_once "config.php";

class Database
{
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;


    private $dbh;
    public $error;
    private $stmt;

    public function __construct(
        $host = null,
        $user = null,
        $pass = null,
        $dbname = null

    ) {
        if ($host !== null) {
            $this->host = $host;
        }

        if ($user !== null) {
            $this->user = $user;
        }

        if ($pass !== null) {
            $this->pass = $pass;
        }

        if ($dbname !== null) {
            $this->dbname = $dbname;
        }


        $dsn =
            'mysql:host=' .
            $this->host .
            ';' .
            'dbname=' .
            $this->dbname;

        $options = [
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        ];

        $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        $this->dbh->exec("SET NAMES 'utf8'");
    }

    public function close()
    {
        $this->dbh = null;
        $this->stmt = null;
        return true;
    }


    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        $this->stmt->bindValue($param, $value, $type);
    }

    
    public function execute($nameValuePairArray = null)
    {
        $ret = false;
        try {
            if (
                is_array($nameValuePairArray) &&
                !empty($nameValuePairArray)
            ) {
                $ret = $this->stmt->execute($nameValuePairArray);
            } else {
                $ret = $this->stmt->execute();
            }
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
        }

        return $ret;
    }


    public function resultset(
        $nameValuePairArray = null,
        $FETCH_ASSOC = PDO::FETCH_ASSOC
    ) {
        $this->execute($nameValuePairArray);
        if (isset($FETCH_ASSOC) && $FETCH_ASSOC == PDO::FETCH_ASSOC) {
            $rows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->stmt->closeCursor();
            return $rows;
        } else {
            $rows = $this->stmt->fetchAll();
            $this->stmt->closeCursor();
            return $rows;
        }
    }

    public function fetchAll($FETCH_ASSOC = PDO::FETCH_ASSOC)
    {
        if (isset($FETCH_ASSOC) && $FETCH_ASSOC == PDO::FETCH_ASSOC) {
            $rows = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
            $this->stmt->closeCursor();
            return $rows;
        } else {
            $rows = $this->stmt->fetchAll();
            $this->stmt->closeCursor();
            return $rows;
        }
    }

    public function fetch($FETCH_ASSOC = PDO::FETCH_ASSOC)
    {
        if (isset($FETCH_ASSOC) && $FETCH_ASSOC == PDO::FETCH_ASSOC) {
            $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
            return $row;
        } else {
            $row = $this->stmt->fetch();
            return $row;
        }
    }

    public function single(
        $nameValuePairArray = null,
        $FETCH_ASSOC = PDO::FETCH_ASSOC
    ) {
        $this->execute($nameValuePairArray);
        if (isset($FETCH_ASSOC) && $FETCH_ASSOC == PDO::FETCH_ASSOC) {
            $row = $this->stmt->fetch(PDO::FETCH_ASSOC);
            $this->stmt->closeCursor();
            return $row;
        } else {
            $row = $this->stmt->fetch();
            $this->stmt->closeCursor();
            return $row;
        }
    }

    public function rowCount()
    {
        return $this->stmt->rowCount();
    }

    public function lastInsertId()
    {
        return $this->dbh->lastInsertId();
    }

    function find($table, $field, $value, $orderByClause = '')
    {
        $query =
            "SELECT * FROM $table WHERE $field = :value" .
            (!empty($orderByClause) ? " ORDER BY $orderByClause" : '');
        $this->query($query);
        $this->bind(':value', $value);
        $row = $this->single();
        return $row;
    }


    function findAll(
        $table,
        $field = null,
        $value = null,
        $orderBy = '',
        $columns = null
    ) {
        $query =
            "SELECT " .
            (!is_null($columns)
                ? (is_string($columns)
                    ? $columns
                    : implode(',', $columns))
                : "*") .
            " FROM $table";

        if (is_array($field) && !empty($field)) {
            $whereClauses = [];
            foreach ($field as $fieldName => $_) {
                $whereClauses[] = " ($fieldName = :$fieldName) ";
            }
            $query .= " WHERE TRUE AND " . implode(' AND ', $whereClauses);
        } elseif ($field) {
            $query .= " WHERE $field = :value";
        }
        $query .= " $orderBy";

        $this->query($query);

        if (is_array($field) && !empty($field)) {
            foreach ($field as $fieldName => $fieldValue) {
                $this->bind(":$fieldName", $fieldValue);
            }
        } elseif ($field) {
            $this->bind(':value', $value);
        }

        $rows = $this->resultSet();
        return $rows;
    }



    function findAllTicketBy(
        $table,
        $orderByColumn,
        $orderByDirection = 'ASC',
        $field = null,
        $value = null
    ) {
        $query = "SELECT * FROM $table";

        if ($field) {
            $query .= " WHERE $field = :value";
        }
        $query .= " ORDER BY $orderByColumn $orderByDirection";

        $this->query($query);

        if ($field) {
            $this->bind(':value', $value);
        }

        $rows = $this->resultSet();
        return $rows;
    }

    /**
     * Inserisce un record.
     */
    public function insert($table, $data)
    {
        $ret = false;

        $fields = "";
        $values = "";

        foreach ($data as $field => $value) {
            if ($fields == "") {
                $fields = "`$field`";
                $values = ":$field";
            } else {
                $fields .= ",$field";
                $values .= ",:$field";
            }
        }
        $query = "INSERT INTO $table ($fields) VALUES ($values) ";
        $this->query($query);
        foreach ($data as $field => $value) {
            $this->bind(":$field", $value);
        }
        try {
            if ($this->execute() === false) {
                $ret = false;
            } else {
                $ret = $this->lastInsertId();
            }
        } catch (PDOException $ex) {
            throw $ex;
        }

        return $ret;
    }

    /**
     * Aggiorna un record.
     */
    public function update($table, $id_field, $id_value, $data)
    {
        $assign = "";

        foreach ($data as $field => $value) {
            if ($field == $id_field) {
                continue;
            }

            if ($assign == '') {
                $assign = "$field=:$field";
            } else {
                $assign .= ",$field=:$field";
            }
        }

        $query = "UPDATE $table SET $assign WHERE `$id_field`=:$id_field ";

        $this->query($query);

        foreach ($data as $field => $value) {
            if ($field == $id_field) {
                continue;
            }
            $this->bind(":$field", $value);
        }

        $this->bind(":$id_field", $id_value);

        $ret = $this->execute();

        return $ret;
    }

    /**
     * Elimina un record
     */
    public function delete($table, $id_field, $id_value)
    {
        $ret = false;
        $query = "DELETE FROM $table WHERE $id_field= :id_value ";
        $this->query($query);
        $this->bind(":id_value", $id_value);
        try {
            $ret = $this->execute();
        } catch (PDOException $ex) {
            throw $ex;
        }

        return $ret;
    }

    public function query($query)
    {
        $this->stmt = $this->dbh->prepare($query);
    }
}