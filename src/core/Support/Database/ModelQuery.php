<?php

namespace Core\Support\Database;

use Core\Services\Database\Database;
use RuntimeException;

class ModelQuery
{
    private string $modelClass;
    private array $wheres = [];
    private array $orderBys = [];
    private ?int $limit = null;
    private ?int $offset = null;
    private ?Model $modelInstance = null;

    public function __construct(string $modelClass)
    {
        $this->modelClass = $modelClass;
    }

    public function __clone(): void
    {
        $this->modelInstance = null;
    }

    public function where(string $column, mixed $operator = null, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'and',
        ];

        return $this;
    }

    public function orWhere(string $column, mixed $operator = null, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => 'or',
        ];

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orderBys[] = [
            'column' => $column,
            'direction' => strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC',
        ];

        return $this;
    }

    public function limit(int $value): self
    {
        $this->limit = max(0, $value);

        return $this;
    }

    public function offset(int $value): self
    {
        $this->offset = max(0, $value);

        return $this;
    }

    public function get(array $columns = ['*']): array
    {
        [$sql, $bindings] = $this->compileSelect($columns);
        $records = Database::select($sql, $bindings);

        $modelClass = $this->modelClass;

        return $modelClass::hydrate($records);
    }

    public function first(array $columns = ['*']): ?Model
    {
        $clone = clone $this;
        $clone->limit = 1;

        $results = $clone->get($columns);

        return $results[0] ?? null;
    }

    public function value(string $column): mixed
    {
        $model = $this->first([$column]);

        return $model?->getAttribute($column);
    }

    public function count(): int
    {
        [$sql, $bindings] = $this->compileAggregate('COUNT(*)');
        $statement = Database::pdo()->prepare($sql);
        $statement->execute($bindings);

        $value = $statement->fetchColumn();

        return (int) ($value ?? 0);
    }

    public function exists(): bool
    {
        $clone = clone $this;
        $clone->orderBys = [];
        $clone->limit = 1;
        $clone->offset = null;

        [$sql, $bindings] = $clone->compileSelect(['1']);
        $statement = Database::pdo()->prepare($sql);
        $statement->execute($bindings);

        return $statement->fetchColumn() !== false;
    }

    public function delete(): int
    {
        if ($this->wheres === []) {
            throw new RuntimeException('Cannot delete without at least one where clause.');
        }

        [$whereSql, $bindings] = $this->compileWhereOnly();
        $model = $this->model();

        $sql = 'DELETE FROM ' . $model->getTable() . $whereSql;

        return Database::affectingStatement($sql, $bindings);
    }

    public function toSql(array $columns = ['*']): string
    {
        [$sql] = $this->compileSelect($columns);

        return $sql;
    }

    private function compileSelect(array $columns): array
    {
        $bindings = [];
        $model = $this->model();

        $sql = 'SELECT ' . implode(', ', $columns) . ' FROM ' . $model->getTable();
        $sql .= $this->compileWheres($bindings);

        if ($this->orderBys !== []) {
            $segments = array_map(
                static fn(array $order): string => $order['column'] . ' ' . $order['direction'],
                $this->orderBys
            );
            $sql .= ' ORDER BY ' . implode(', ', $segments);
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ' OFFSET ' . $this->offset;
        }

        return [$sql, $bindings];
    }

    private function compileAggregate(string $expression): array
    {
        $bindings = [];
        $model = $this->model();

        $sql = 'SELECT ' . $expression . ' FROM ' . $model->getTable();
        $sql .= $this->compileWheres($bindings);

        return [$sql, $bindings];
    }

    private function compileWhereOnly(): array
    {
        $bindings = [];
        $where = $this->compileWheres($bindings);

        return [$where, $bindings];
    }

    private function compileWheres(array &$bindings): string
    {
        if ($this->wheres === []) {
            return '';
        }

        $clauses = [];

        foreach ($this->wheres as $index => $where) {
            $param = 'w' . $index;
            $prefix = $index === 0 ? '' : strtoupper($where['boolean']) . ' ';
            $clauses[] = $prefix . $where['column'] . ' ' . $where['operator'] . ' :' . $param;
            $bindings[$param] = $where['value'];
        }

        return ' WHERE ' . implode(' ', $clauses);
    }

    private function model(): Model
    {
        if ($this->modelInstance === null) {
            $class = $this->modelClass;
            $this->modelInstance = new $class();
        }

        return $this->modelInstance;
    }
}