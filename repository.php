<?php

class Repository {
    private $tableName;
    private $pk;
    private $conn;

    public function __construct(string $tableName, string $pk, mysqli $conn) {
        $this->tableName = $tableName;
        $this->pk = $pk;
        $this->conn = $conn;
    }

    public function count($filter = []): int {
        $query = "SELECT COUNT(*) AS count FROM {$this->tableName}";

        $whereClauses = $this->buildWhereClauses($filter);
        if ($whereClauses) {
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $result = $this->conn->query($query);
        if (!$result) {
            return 0;
        }

        $data = $result->fetch_assoc();
        return (int) $data['count'];
    }

    /**
     * Fetch multiple records with optional filters, sorting, and limits.
     */
    public function fetchBy($filter = [], string $sort = "", string $sortType = "ASC", int $max = 500): ?array {
        $sort = $sort ?: $this->pk;
        $query = "SELECT * FROM {$this->tableName}";

        $whereClauses = $this->buildWhereClauses($filter);
        if ($whereClauses) {
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $query .= " ORDER BY $sort $sortType LIMIT $max";

        $result = $this->conn->query($query);
        if (!$result) {
            return null;
        }

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Fetch a single record based on the primary key or other filters.
     */
    public function fetch($filter, string $expression = ""): ?array {
        $query = "SELECT * FROM {$this->tableName}";

        if (is_array($filter) && !empty($filter)) {
            $whereClauses = $this->buildWhereClauses($filter);
        } elseif (is_string($filter) && !empty($filter) && !strpos("=",$expression)) {
            $whereClauses = ["{$this->pk} = '" . $this->conn->real_escape_string($filter) . "'"];
        } elseif (!empty($expression)) {
            $whereClauses = [$expression];
        } else {
            return null;
        }

        if ($whereClauses) {
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $result = $this->conn->query($query);
        if (!$result) {
            return null;
        }

        return $result->fetch_assoc();
    }

    /**
     * Save a new record to the database.
     */
    public function save(array $data,$columns=[]): ?array {
        if ($this->fetch($data[$this->pk])) {
            return null;
        }
        if(count($columns)>0){
            $insertData = [];
            foreach($columns as $column){
                $insertData[$column] = $data[$column];
            }
            $data = $insertData;
        }

        $columns = implode(", ", array_map([$this->conn, 'real_escape_string'], array_keys($data)));
        $values = implode(", ", array_map([$this, 'quoteValue'], array_values($data)));

        $query = "INSERT INTO {$this->tableName} ($columns) VALUES ($values)";

        if ($this->conn->query($query)) {
            return $data;
        }

        return null;
    }

    /**
     * Update a record in the database.
     */
    public function update(array $data, $fields, $filter=[]): ?array {
        if (!$this->fetch($data[$this->pk])) {
            return null;
        }

        $setClauses = [];
        foreach ($fields as $field) {
            $setClauses[] = "`" . $this->conn->real_escape_string($field) . "` = " . $this->quoteValue($data[$field]);
        }

        if(!$filter){
            $filter = [];
            $filter[$this->pk] = $data[$this->pk];
        }

        $whereClauses = $this->buildWhereClauses($filter);

        if ($whereClauses) {
            $query = "UPDATE {$this->tableName} SET " . implode(", ", $setClauses) . " WHERE " . implode(" AND ", $whereClauses);

            if ($this->conn->query($query)) {
                return $this->fetch($data[$this->pk]);
            }
        }

        return null;
    }

    /**
     * Perform aggregate functions like sum, avg, etc.
     */
    public function aggregate(string $type = "SUM", string $column = "", $filter = []): string {
        $column = $column ?: $this->pk;
        $query = "SELECT $type(`" . $this->conn->real_escape_string($column) . "`) AS result FROM {$this->tableName}";

        $whereClauses = $this->buildWhereClauses($filter);

        if ($whereClauses) {
            $query .= " WHERE " . implode(" AND ", $whereClauses);
        }

        $result = $this->conn->query($query);
        if (!$result) {
            return NULL;
        }

        $data = $result->fetch_assoc();
        return $data['result']?$data['result']:'0';
    }

    /**
     * Build where clauses from filters.
     */
    private function buildWhereClauses($filter): array {
        $whereClauses = [];

        if(is_array($filter)){
            foreach ($filter as $column => $value) {
                $whereClauses[] = "`" . $this->conn->real_escape_string($column) . "` = '" . $this->conn->real_escape_string($value) . "'";
            }
        }
        else if (!empty($filter)) {
            $whereClauses[] = $filter;
        }

        return $whereClauses;
    }

    /**
     * Quote a value for safe SQL insertion.
     */
    private function quoteValue($value): string {
        return "'" . $this->conn->real_escape_string($value) . "'";
    }
}
