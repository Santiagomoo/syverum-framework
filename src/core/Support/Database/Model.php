<?php

namespace Core\Support\Database;

use ArrayAccess;
use Core\Services\Database\Database;
use JsonSerializable;
use RuntimeException;

abstract class Model implements ArrayAccess, JsonSerializable
{
    protected ?string $table = null;
    protected string $primaryKey = 'id';
    protected bool $incrementing = true;
    protected array $fillable = [];
    protected array $casts = [];
    protected array $attributes = [];
    protected array $original = [];
    protected bool $exists = false;

    public function __construct(array $attributes = [], bool $exists = false)
    {
        if ($exists) {
            $this->exists = true;
            $this->setRawAttributes($attributes, true);
            return;
        }

        if ($attributes !== []) {
            $this->fill($attributes);
        }
    }

    public static function query(): ModelQuery
    {
        return (new static())->newQuery();
    }

    public static function all(array $columns = ['*']): array
    {
        return static::query()->get($columns);
    }

    public static function first(array $columns = ['*']): ?static
    {
        return static::query()->first($columns);
    }

    public static function find(mixed $id, array $columns = ['*']): ?static
    {
        if ($id === null) {
            return null;
        }

        $instance = new static();

        return static::query()
            ->where($instance->getKeyName(), $id)
            ->first($columns);
    }

    public static function findOrFail(mixed $id, array $columns = ['*']): static
    {
        $model = static::find($id, $columns);

        if ($model === null) {
            throw new RuntimeException(sprintf('%s model not found.', static::class));
        }

        return $model;
    }

    public static function create(array $attributes): static
    {
        $model = new static();
        $model->fill($attributes);
        $model->save();

        return $model;
    }

    public static function where(string $column, mixed $operator = null, mixed $value = null): ModelQuery
    {
        return static::query()->where($column, $operator, $value);
    }

    public static function count(): int
    {
        return static::query()->count();
    }

    public static function hydrate(array $items): array
    {
        $instance = new static();
        $models = [];

        foreach ($items as $item) {
            $models[] = $instance->newFromBuilder((array) $item);
        }

        return $models;
    }

    public function newQuery(): ModelQuery
    {
        return new ModelQuery(static::class);
    }

    public function fill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            }
        }

        return $this;
    }

    public function forceFill(array $attributes): static
    {
        foreach ($attributes as $key => $value) {
            $this->setAttribute($key, $value);
        }

        return $this;
    }

    public function update(array $attributes): bool
    {
        $this->fill($attributes);
        return $this->save();
    }

    public function getTable(): string
    {
        return $this->table ?? $this->resolveTableName();
    }

    public function setTable(string $table): static
    {
        $this->table = $table;

        return $this;
    }

    public function getKeyName(): string
    {
        return $this->primaryKey;
    }

    public function getKey(): mixed
    {
        return $this->getAttribute($this->getKeyName());
    }

    public function setKey(mixed $value): void
    {
        $this->attributes[$this->getKeyName()] = $value;
    }

    public function exists(): bool
    {
        return $this->exists;
    }

    public function save(): bool
    {
        return $this->exists ? $this->performUpdate() : $this->performInsert();
    }

    public function delete(): bool
    {
        if (!$this->exists) {
            return false;
        }

        $key = $this->getKey();

        if ($key === null) {
            throw new RuntimeException('Cannot delete model without primary key value.');
        }

        $affected = Database::affectingStatement(
            'DELETE FROM ' . $this->getTable() . ' WHERE ' . $this->getKeyName() . ' = :pk',
            ['pk' => $key]
        );

        if ($affected > 0) {
            $this->exists = false;
        }

        return $affected > 0;
    }

    public function refresh(): static
    {
        $fresh = static::find($this->getKey());

        if ($fresh === null) {
            throw new RuntimeException('Unable to refresh model that no longer exists.');
        }

        $this->setRawAttributes($fresh->getAttributes(), true);
        $this->exists = true;

        return $this;
    }

    public function getAttribute(string $key): mixed
    {
        if (!array_key_exists($key, $this->attributes)) {
            return null;
        }

        return $this->castAttribute($key, $this->attributes[$key]);
    }

    public function setAttribute(string $key, mixed $value): void
    {
        $this->attributes[$key] = $this->prepareAttributeValue($key, $value);
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getOriginal(?string $key = null): mixed
    {
        if ($key === null) {
            return $this->original;
        }

        return $this->original[$key] ?? null;
    }

    public function toArray(): array
    {
        $attributes = [];

        foreach ($this->attributes as $key => $value) {
            $attributes[$key] = $this->castAttribute($key, $value);
        }

        return $attributes;
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists((string) $offset, $this->attributes);
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $this->getAttribute((string) $offset);
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->setAttribute((string) $offset, $value);
    }

    public function offsetUnset(mixed $offset): void
    {
        unset($this->attributes[(string) $offset]);
    }

    public function __get(string $key): mixed
    {
        return $this->getAttribute($key);
    }

    public function __set(string $key, mixed $value): void
    {
        $this->setAttribute($key, $value);
    }

    public function __isset(string $key): bool
    {
        return $this->offsetExists($key);
    }

    public function __unset(string $key): void
    {
        $this->offsetUnset($key);
    }

    protected function newFromBuilder(array $attributes): static
    {
        $model = new static();
        $model->exists = true;
        $model->setRawAttributes($attributes, true);

        return $model;
    }

    protected function setRawAttributes(array $attributes, bool $sync = false): void
    {
        $this->attributes = $attributes;

        if ($sync) {
            $this->syncOriginal();
        }
    }

    protected function syncOriginal(): void
    {
        $this->original = $this->attributes;
    }

    protected function getDirty(): array
    {
        $dirty = [];

        foreach ($this->attributes as $key => $value) {
            if (!array_key_exists($key, $this->original) || $value !== $this->original[$key]) {
                $dirty[$key] = $value;
            }
        }

        return $dirty;
    }

    protected function performInsert(): bool
    {
        $attributes = $this->getPersistableAttributes();

        if ($attributes === []) {
            throw new RuntimeException('Cannot insert model without attributes.');
        }

        $columns = array_keys($attributes);
        $placeholders = array_map(static fn(string $column): string => ':' . $column, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->getTable(),
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        $result = Database::statement($sql, $attributes);

        if (!$result) {
            return false;
        }

        if ($this->incrementing) {
            $keyName = $this->getKeyName();
            $id = Database::lastInsertId();

            if ($id !== '' && $id !== null) {
                $this->attributes[$keyName] = is_numeric($id) ? (int) $id : $id;
            }
        }

        $this->exists = true;
        $this->syncOriginal();

        return true;
    }

    protected function performUpdate(): bool
    {
        $dirty = $this->getDirty();
        $primaryKey = $this->getKeyName();
        unset($dirty[$primaryKey]);

        if ($dirty === []) {
            return true;
        }

        $key = $this->getKey();

        if ($key === null) {
            throw new RuntimeException('Cannot update model without primary key value.');
        }

        $assignments = [];
        $params = [];

        foreach ($dirty as $column => $value) {
            $assignments[] = $column . ' = :set_' . $column;
            $params['set_' . $column] = $value;
        }

        $params['pk'] = $key;

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :pk',
            $this->getTable(),
            implode(', ', $assignments),
            $primaryKey
        );

        $updated = Database::affectingStatement($sql, $params);

        if ($updated > 0) {
            $this->syncOriginal();
        }

        return $updated > 0;
    }

    protected function getPersistableAttributes(): array
    {
        $attributes = $this->attributes;

        if ($this->incrementing) {
            unset($attributes[$this->getKeyName()]);
        }

        return $attributes;
    }

    protected function isFillable(string $key): bool
    {
        if ($this->fillable === []) {
            return true;
        }

        return in_array($key, $this->fillable, true);
    }

    protected function resolveTableName(): string
    {
        $class = static::class;
        $class = str_contains($class, '\\') ? substr($class, strrpos($class, '\\') + 1) : $class;

        $snake = strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $class));
        return $this->pluralize($snake);
    }

    protected function pluralize(string $value): string
    {
        if (str_ends_with($value, 'y')) {
            return substr($value, 0, -1) . 'ies';
        }

        if (str_ends_with($value, 's')) {
            return $value;
        }

        return $value . 's';
    }

    protected function castAttribute(string $key, mixed $value): mixed
    {
        if (!array_key_exists($key, $this->casts) || $value === null) {
            return $value;
        }

        return match ($this->casts[$key]) {
            'int', 'integer' => (int) $value,
            'float', 'double', 'real' => (float) $value,
            'bool', 'boolean' => (bool) $value,
            'array' => $this->castArray($value),
            'json' => $this->castJson($value),
            default => $value,
        };
    }

    protected function prepareAttributeValue(string $key, mixed $value): mixed
    {
        if (!array_key_exists($key, $this->casts) || $value === null) {
            return $value;
        }

        return match ($this->casts[$key]) {
            'json', 'array' => $this->encodeJsonValue($value),
            default => $value,
        };
    }

    private function castArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }

        return (array) $value;
    }

    private function castJson(mixed $value): mixed
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return $decoded ?? $value;
        }

        return $value;
    }

    private function encodeJsonValue(mixed $value): mixed
    {
        if (!is_array($value) && !is_object($value)) {
            return $value;
        }

        $encoded = json_encode($value);

        if ($encoded === false) {
            throw new RuntimeException('Failed to encode attribute value to JSON.');
        }

        return $encoded;
    }
}