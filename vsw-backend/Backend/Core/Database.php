<?php

class Database
{
    public $database;
    private $username, $password, $host = '127.0.0.1', $port = 3306, $dbname;

    private $query_list = [
        'insert' => 'insert into %s(%s) VALUES (%s)',
        'delete' => 'delete from %s where %s',
        'update' => 'update %s set %s where %s',
        'select' => 'select %s from %s %s',
        'select_c' => 'select %s from %s %s where %s',
        's_left_join' => 'select %s from %s left join %s on %s',
        's_right_join' => 'select %s from %s right join %s on %s',
        's_inner_join' => 'select %s from %s inner join %s on %s'
    ];

    /**
     * @throws Exception
     */
    public function __construct($username, $password, $dbname, $host = null, $port = null)
    {
        if (null != $host) {
            $this->host = $host;
        }
        if (null != $port) {
            $this->port = $port;
        }
        $this->username = $username;
        $this->password = $password;
        $this->dbname = $dbname;

        try {
            $this->database = new PDO(
                sprintf('mysql:dbname=%s;host=%s;port=%d', $this->dbname, $this->host, $this->port),
                $this->username,
                $this->password
            );
        } catch (PDOException $e) {
            throw new Exception("Database error. Error Info <br>" . $e->getMessage());
        }
    }

    /*
     * gets a single entry
     */
    function get($table, $columns, $where, $join = '')
    {
        $w_cols = array_keys($where);
        $w_values = array_values($where);
        $sql = sprintf($this->query_list['select_c'], join(',', $columns), $table, $join, join('=?,', $w_cols) . '=? ');


        $stmt = $this->database->prepare($sql);
        try {
            $stmt->execute($w_values);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return $e->getCode();
        }
    }

    /*
     * retrieves multiple entries
     */
    function select($table, $columns, $where, $join = '')
    {
        if (empty($where)) {
            $sql = sprintf($this->query_list['select'], join(',', $columns), $table, $join);
        } else {
            $w_cols = array_keys($where);
            $w_values = array_values($where);
            $sql = sprintf($this->query_list['select_c'], join(',', $columns), $table, $join, join('=?,', $w_cols) . '=? ');
        }


        $stmt = $this->database->prepare($sql);
        try {
            $stmt->execute($w_values ?? null);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return $e->getCode();
        }
    }

    function delete($table, $where)
    {
        $cols = array_keys($where);
        $values = array_values($where);
        $sql = sprintf($this->query_list['delete'], $table, join('=?,', $cols) . '=?');
        $stmt = $this->database->prepare($sql);
        try {
            $stmt->execute($values);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return $e->getCode();
        }
    }

    function update($table, $data, $where)
    {
        $w_cols = array_keys($where);
        $w_values = array_values($where);
        $cols = array_keys($data);
        $values = array_values($data);
        $set_p = join('=?,', $cols) . '=?';
        $w_p = join('=?,', $w_cols) . '=?';

        $exec_array = $this->merge_array($values, $w_values);

        $sql = sprintf($this->query_list['update'], $table, $set_p, $w_p);
        $stmt = $this->database->prepare($sql);
        try {
            $stmt->execute($exec_array);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            return $e->getCode();
        }
    }

    private function merge_array($a, $b)
    {
        foreach ($b as $item) {
            $a[] = $item;
        }
        return $a;
    }

    /*
     * private methods
     */

    function insert($table, $data)
    {
        $cols = array_keys($data);
        $values = array_values($data);
        $sql = sprintf($this->query_list['insert'], $table, join(',', $cols), $this->placeholder(count($cols)));
        $stmt = $this->database->prepare($sql);
        try {
            $stmt->execute($values);
            return $stmt->rowCount() > 0 ? $this->database->lastInsertId() : false;
        } catch (PDOException $e) {
            return $e->getCode();
        }

    }

    private function placeholder($length): string
    {
        return rtrim(str_repeat('?,', $length), ',');
    }
}