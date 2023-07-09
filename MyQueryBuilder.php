<?

class MyQueryBuilder
{
    private $config;
    private $connection;
    private $query;
    private $join;

    public function __construct($config)
    {
        $this->config = $config;

        $this->connection = new mysqli(
            $this->config['host'],
            $this->config['username'],
            $this->config['password'],
            $this->config['database'],
            $this->config['port']
        );

        if ($this->connection->connect_error) {
            die('Ошибка подключения к базе данных: ' . $this->connection->connect_error);
        }

        $this->resetQuery();
    }

    public function select($columns)
    {
        $this->resetQuery();
        $this->query['type'] = 'SELECT';
        $this->query['columns'] = $columns;
        return $this;
    }

    public function from($table)
    {
        $this->query['table'] = $table;
        return $this;
    }

    public function where($column, $operator, $value)
    {
        $this->query['conditions'][] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        return $this;
    }

    public function orderBy($column, $direction = 'ASC')
    {
        $this->query['orderBy'] = [
            'column' => $column,
            'direction' => $direction
        ];
        return $this;
    }

    public function limit($limit)
    {
        $this->query['limit'] = $limit;
        return $this;
    }

    public function insert($table, $data)
    {
        $this->resetQuery();
        $this->query['type'] = 'INSERT';
        $this->query['table'] = $table;
        $this->query['data'] = $data;
        return $this;
    }

    public function update($table, $data)
    {
        $this->resetQuery();
        $this->query['type'] = 'UPDATE';
        $this->query['table'] = $table;
        $this->query['data'] = $data;
        return $this;
    }

    public function delete($table)
    {
        $this->resetQuery();
        $this->query['type'] = 'DELETE';
        $this->query['table'] = $table;
        return $this;
    }

    public function join($table, $condition, $type = 'INNER')
    {
        $this->join .= " $type JOIN $table ON $condition";
        return $this;
    }

    public function execute()
    {
        $sql = $this->buildQuery();

        try {
            $statement = $this->connection->prepare($sql);

            if ($statement === false) {
                throw new Exception($this->connection->error);
            }

            if (!empty($this->query['conditions'])) {
                $params = array_column($this->query['conditions'], 'value');
                $types = str_repeat('s', count($params));
                array_unshift($params, $types);
                $statement->bind_param(...$params);
            }

            if ($this->query['type'] === 'INSERT') {
                $values = array_values($this->query['data']);
                $placeholders = implode(', ', array_fill(0, count($this->query['data']), '?'));
                $sql = str_replace('?', $placeholders, $sql);
                $types = str_repeat('s', count($values));
                $params = array_merge([$types], $values);
                $statement->bind_param(...array_values($params));
            }

            $statement->execute();

            if ($this->query['type'] === 'SELECT') {
                $result = $statement->get_result();
                return $result->fetch_all(MYSQLI_ASSOC);
            } else {
                return true; // Возвращаем true для INSERT, UPDATE, DELETE запросов
            }
        } catch (Exception $e) {
            echo 'Ошибка выполнения запроса: ' . $e->getMessage();
            exit;
        }

        return null;
    }

    private function resetQuery()
    {
        $this->query = [
            'type' => null,
            'columns' => null,
            'table' => null,
            'conditions' => [],
            'orderBy' => null,
            'limit' => null,
            'data' => []
        ];
        $this->join = null;
    }

    private function buildQuery()
    {
        $sql = $this->query['type'] . ' ';

        if ($this->query['type'] === 'SELECT') {
            $sql .= $this->buildSelectQuery();
        } elseif ($this->query['type'] === 'INSERT') {
            $sql .= $this->buildInsertQuery();
        } elseif ($this->query['type'] === 'UPDATE') {
            $sql .= $this->buildUpdateQuery();
        } elseif ($this->query['type'] === 'DELETE') {
            $sql .= $this->buildDeleteQuery();
        }

        return $sql;
    }

    private function buildSelectQuery()
    {
        $sql = '';

        if (is_array($this->query['columns'])) {
            $sql .= implode(', ', $this->query['columns']);
        } else {
            $sql .= $this->query['columns'];
        }

        $sql .= ' FROM ' . $this->query['table'];

        if (!empty($this->join)) {
            $sql .= $this->join;
        }

        if (!empty($this->query['conditions'])) {
            $sql .= ' WHERE ';
            $conditions = [];

            foreach ($this->query['conditions'] as $condition) {
                $conditions[] = $condition['column'] . ' ' . $condition['operator'] . ' ' . $this->connection->real_escape_string($condition['value']);
            }

            $sql .= implode(' AND ', $conditions);
        }

        if (!empty($this->query['orderBy'])) {
            $sql .= ' ORDER BY ' . $this->query['orderBy']['column'] . ' ' . $this->query['orderBy']['direction'];
        }

        if (!empty($this->query['limit'])) {
            $sql .= ' LIMIT ' . $this->query['limit'];
        }

        return $sql;
    }

    private function buildInsertQuery()
    {
        $columns = implode(', ', array_keys($this->query['data']));
        $placeholders = implode(', ', array_fill(0, count($this->query['data']), '?'));

        return 'INSERT INTO ' . $this->query['table'] . ' (' . $columns . ') VALUES (' . $placeholders . ')';
    }

    private function buildUpdateQuery()
    {
        $sql = 'UPDATE ' . $this->query['table'] . ' SET ';
        $updates = [];

        foreach ($this->query['data'] as $column => $value) {
            $updates[] = $column . ' = \'' . $this->connection->real_escape_string($value) . '\'';
        }

        $sql .= implode(', ', $updates);

        if (!empty($this->query['conditions'])) {
            $sql .= ' WHERE ';
            $conditions = [];

            foreach ($this->query['conditions'] as $condition) {
                $conditions[] = $condition['column'] . ' ' . $condition['operator'] . ' ' . $condition['value'];
            }

            $sql .= implode(' AND ', $conditions);
        }

        return $sql;
    }

    private function buildDeleteQuery()
    {
        $sql = 'DELETE FROM ' . $this->query['table'];

        if (!empty($this->query['conditions'])) {
            $sql .= ' WHERE ';
            $conditions = [];

            foreach ($this->query['conditions'] as $condition) {
                $conditions[] = $condition['column'] . ' ' . $condition['operator'] . ' ' . $condition['value'];
            }

            $sql .= implode(' AND ', $conditions);
        }

        return $sql;
    }
}
