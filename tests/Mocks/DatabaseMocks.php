<?php

namespace Tests\Mocks;

// Mock PDO class
class MockPDO extends \PDO
{
    public function __construct() {}
    public function prepare(string $query, array $options = []): \PDOStatement|false { return new MockPDOStatement(); }
    public function query(string $query, ?int $fetchMode = null, mixed ...$fetchModeArgs): \PDOStatement|false { return new MockPDOStatement(); }
    public function lastInsertId(?string $name = null): string|false { return '1'; }
}

// Mock PDOStatement class
class MockPDOStatement extends \PDOStatement
{
    public $data = [];
    public $fetchMode = \PDO::FETCH_ASSOC;

    public function __construct() {}
    public function execute(?array $params = null): bool { return true; }
    public function fetch(int $mode = \PDO::FETCH_DEFAULT, int $cursorOrientation = \PDO::FETCH_ORI_NEXT, int $cursorOffset = 0): mixed { return current($this->data); }
    public function fetchAll(int $mode = \PDO::FETCH_DEFAULT, mixed ...$args): array { return $this->data; }
    public function setFetchMode(int $mode, mixed ...$args): bool { $this->fetchMode = $mode; return true; }
    public function bindParam(string|int $param, mixed &$var, int $type = \PDO::PARAM_STR, int $maxLength = 0, mixed $driverOptions = null): bool { return true; }
    public function bindValue(string|int $param, mixed $value, int $type = \PDO::PARAM_STR): bool { return true; }
    public function fetchColumn(int $column = 0): mixed { return 0; }
    public function errorInfo(): array { return ['00000', 0, '']; }
}

// Testable Model to override getDB()
class TestableModel extends \Core\Model
{
    public static $mockDb;

    protected static function getDB()
    {
        return self::$mockDb;
    }
}
