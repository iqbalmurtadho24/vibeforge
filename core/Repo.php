<?php

if (!defined('APP_ENTRY')) {
    http_response_code(403);
    exit('Direct access forbidden');
}

/**
 * Repo - centralized data access layer (Section 3g, CLAUDE.md).
 *
 * Modules call Repo::table('entity')->{all,find,where,insert,update,delete}()
 * and never know whether the backing store is MySQL or a data/*.json file.
 * Mode is detected per table (DB_MODE=auto), or forced via DB_MODE=json|mysql.
 */
final class Repo
{
    /** @var array<string, string> table => 'sql'|'json' */
    private static array $modeCache = [];

    private static ?PDO $pdo = null;
    private static bool $pdoAttempted = false;

    private string $table;

    private function __construct(string $table)
    {
        $this->table = $table;
    }

    public static function table(string $table): self
    {
        return new self($table);
    }

    // ---- Public CRUD ----

    public function all(): array
    {
        return $this->mode() === 'sql' ? $this->sqlAll() : $this->jsonAll();
    }

    public function find($id): ?array
    {
        return $this->mode() === 'sql' ? $this->sqlFind($id) : $this->jsonFind($id);
    }

    public function where(array $criteria): array
    {
        return $this->mode() === 'sql' ? $this->sqlWhere($criteria) : $this->jsonWhere($criteria);
    }

    /** @return int|string new record id */
    public function insert(array $data)
    {
        return $this->mode() === 'sql' ? $this->sqlInsert($data) : $this->jsonInsert($data);
    }

    public function update($id, array $data): bool
    {
        return $this->mode() === 'sql' ? $this->sqlUpdate($id, $data) : $this->jsonUpdate($id, $data);
    }

    public function delete($id): bool
    {
        return $this->mode() === 'sql' ? $this->sqlDelete($id) : $this->jsonDelete($id);
    }

    // ---- Mode detection (Section 3g) ----

    private function mode(): string
    {
        $forced = defined('DB_MODE') ? DB_MODE : 'auto';

        if ($forced === 'json') {
            return 'json';
        }

        if (isset(self::$modeCache[$this->table])) {
            return self::$modeCache[$this->table];
        }

        $pdo = $this->connect();

        if ($pdo === null) {
            if ($forced === 'mysql') {
                $this->halt("Koneksi MySQL gagal. DB_MODE=mysql tidak mengizinkan fallback ke JSON untuk tabel '{$this->table}'.");
            }
            return self::$modeCache[$this->table] = 'json';
        }

        if (!$this->tableExists($pdo, $this->table)) {
            if ($forced === 'mysql') {
                $this->halt("Tabel '{$this->table}' tidak ditemukan di MySQL. DB_MODE=mysql tidak mengizinkan fallback ke JSON.");
            }
            return self::$modeCache[$this->table] = 'json';
        }

        return self::$modeCache[$this->table] = 'sql';
    }

    private function connect(): ?PDO
    {
        if (self::$pdoAttempted) {
            return self::$pdo;
        }
        self::$pdoAttempted = true;

        if (!defined('DB_HOST') || !defined('DB_NAME') || !defined('DB_USER') || DB_NAME === '') {
            return self::$pdo = null;
        }

        try {
            $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', DB_HOST, DB_PORT ?: '3306', DB_NAME);
            self::$pdo = new PDO($dsn, DB_USER, defined('DB_PASSWORD') ? DB_PASSWORD : '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (\PDOException $e) {
            self::$pdo = null;
            $this->logDebug('PDO connect gagal: ' . $e->getMessage());
        }

        return self::$pdo;
    }

    private function tableExists(PDO $pdo, string $table): bool
    {
        static $cache = [];
        if (isset($cache[$table])) {
            return $cache[$table];
        }

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = :table'
        );
        $stmt->execute(['table' => $table]);

        return $cache[$table] = ((int) $stmt->fetchColumn()) > 0;
    }

    private function halt(string $message): void
    {
        $this->logDebug($message);
        http_response_code(500);
        exit('Data layer error: ' . $message);
    }

    private function logDebug(string $message): void
    {
        if (defined('APP_DEBUG') && APP_DEBUG && defined('ROOT_PATH')) {
            $line = sprintf("[%s] [Repo] %s\n", date('Y-m-d H:i:s'), $message);
            @file_put_contents(ROOT_PATH . '/cache/debug.log', $line, FILE_APPEND);
        }
    }

    // ---- SQL backend ----

    private function sqlAll(): array
    {
        $stmt = $this->connect()->query("SELECT * FROM `{$this->table}`");
        return $stmt->fetchAll();
    }

    private function sqlFind($id): ?array
    {
        $stmt = $this->connect()->prepare("SELECT * FROM `{$this->table}` WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();

        return $row === false ? null : $row;
    }

    private function sqlWhere(array $criteria): array
    {
        if ($criteria === []) {
            return $this->sqlAll();
        }

        $clauses = [];
        $params = [];
        foreach ($criteria as $column => $value) {
            $clauses[] = "`{$column}` = :{$column}";
            $params[$column] = $value;
        }

        $sql = "SELECT * FROM `{$this->table}` WHERE " . implode(' AND ', $clauses);
        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    private function sqlInsert(array $data)
    {
        $columns = array_keys($data);
        $sql = sprintf(
            'INSERT INTO `%s` (%s) VALUES (%s)',
            $this->table,
            implode(', ', array_map(fn($c) => "`{$c}`", $columns)),
            implode(', ', array_map(fn($c) => ":{$c}", $columns))
        );

        $pdo = $this->connect();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($data);

        $lastId = $pdo->lastInsertId();

        return $lastId !== '0' && $lastId !== '' ? $lastId : ($data['id'] ?? null);
    }

    private function sqlUpdate($id, array $data): bool
    {
        $sets = array_map(fn($c) => "`{$c}` = :{$c}", array_keys($data));
        $sql = "UPDATE `{$this->table}` SET " . implode(', ', $sets) . ' WHERE id = :__id';

        $stmt = $this->connect()->prepare($sql);
        $stmt->execute($data + ['__id' => $id]);

        return $stmt->rowCount() > 0;
    }

    private function sqlDelete($id): bool
    {
        $stmt = $this->connect()->prepare("DELETE FROM `{$this->table}` WHERE id = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->rowCount() > 0;
    }

    // ---- JSON backend ----

    private function jsonPath(): string
    {
        $dataPath = defined('DATA_PATH') ? DATA_PATH : dirname(__DIR__) . '/data';
        return $dataPath . '/' . $this->table . '.json';
    }

    private function jsonAll(): array
    {
        $path = $this->jsonPath();
        if (!file_exists($path)) {
            return [];
        }

        // Atomic rename on write (see withLock) means a plain read here
        // never observes a partially-written file, so no lock is needed.
        $rows = json_decode(file_get_contents($path), true);

        return is_array($rows) ? $rows : [];
    }

    private function jsonFind($id): ?array
    {
        foreach ($this->jsonAll() as $row) {
            if ((string) ($row['id'] ?? '') === (string) $id) {
                return $row;
            }
        }

        return null;
    }

    private function jsonWhere(array $criteria): array
    {
        return array_values(array_filter($this->jsonAll(), function (array $row) use ($criteria): bool {
            foreach ($criteria as $column => $value) {
                if (!array_key_exists($column, $row) || (string) $row[$column] !== (string) $value) {
                    return false;
                }
            }
            return true;
        }));
    }

    private function jsonInsert(array $data)
    {
        return $this->withLock(function (array $rows) use ($data) {
            $id = $data['id'] ?? $this->nextId($rows);
            $data['id'] = $id;
            $rows[] = $data;

            return [$rows, $id];
        });
    }

    private function jsonUpdate($id, array $data): bool
    {
        return $this->withLock(function (array $rows) use ($id, $data) {
            $found = false;
            foreach ($rows as $index => $row) {
                if ((string) ($row['id'] ?? '') === (string) $id) {
                    $rows[$index] = array_merge($row, $data);
                    $found = true;
                    break;
                }
            }

            return [$rows, $found];
        });
    }

    private function jsonDelete($id): bool
    {
        return $this->withLock(function (array $rows) use ($id) {
            $before = count($rows);
            $rows = array_values(array_filter($rows, fn($row) => (string) ($row['id'] ?? '') !== (string) $id));

            return [$rows, count($rows) < $before];
        });
    }

    private function nextId(array $rows): int
    {
        $max = 0;
        foreach ($rows as $row) {
            if (isset($row['id']) && is_numeric($row['id'])) {
                $max = max($max, (int) $row['id']);
            }
        }

        return $max + 1;
    }

    /**
     * Mutex lives on a separate `.lock` file, not the data file itself,
     * because holding an open handle on a file while rename()-ing another
     * file onto it is unreliable on Windows (Section 3g note).
     */
    private function withLock(callable $mutator)
    {
        $path = $this->jsonPath();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $lockHandle = fopen($path . '.lock', 'c');
        if ($lockHandle === false) {
            throw new \RuntimeException("Tidak bisa membuka lock file untuk {$path}");
        }

        flock($lockHandle, LOCK_EX);

        try {
            $rows = file_exists($path) ? json_decode(file_get_contents($path), true) : [];
            if (!is_array($rows)) {
                $rows = [];
            }

            [$newRows, $result] = $mutator($rows);

            $tmpPath = $path . '.tmp_' . bin2hex(random_bytes(4));
            file_put_contents($tmpPath, json_encode($newRows, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            rename($tmpPath, $path);

            return $result;
        } finally {
            flock($lockHandle, LOCK_UN);
            fclose($lockHandle);
        }
    }
}
