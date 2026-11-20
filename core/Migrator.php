<?php
namespace Core;

class Migrator
{
    private \PDO $pdo;
    private string $directory;

    public function __construct(\PDO $pdo, string $directory)
    {
        $this->pdo       = $pdo;
        $this->directory = rtrim($directory, '/');
    }

    public function migrate(): array
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS migrations (
                id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(150) NOT NULL UNIQUE,
                applied_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB'
        );

        $done    = $this->pdo->query('SELECT name FROM migrations')->fetchAll(\PDO::FETCH_COLUMN);
        $applied = [];

        $files = glob($this->directory . '/*.sql');
        sort($files);

        foreach ($files as $file) {
            $name = basename($file);
            if (in_array($name, $done, true)) {
                continue;
            }
            $this->runFile($file);
            $stmt = $this->pdo->prepare('INSERT INTO migrations (name) VALUES (?)');
            $stmt->execute([$name]);
            $applied[] = $name;
        }

        return $applied;
    }

    public function runFile(string $file): void
    {
        foreach (self::splitStatements((string) file_get_contents($file)) as $statement) {
            $this->pdo->exec($statement);
        }
    }

    public static function splitStatements(string $sql): array
    {
        $statements = [];
        $current    = '';
        $quote      = null;
        $length     = strlen($sql);

        for ($i = 0; $i < $length; $i++) {
            $char = $sql[$i];

            if ($quote !== null) {
                $current .= $char;
                if ($char === '\\' && $i + 1 < $length) {
                    $current .= $sql[++$i];
                } elseif ($char === $quote) {
                    $quote = null;
                }
                continue;
            }

            if ($char === "'" || $char === '"' || $char === '`') {
                $quote = $char;
                $current .= $char;
            } elseif ($char === '-' && substr($sql, $i, 2) === '--') {
                $end = strpos($sql, "\n", $i);
                $i   = $end === false ? $length : $end;
            } elseif ($char === ';') {
                if (trim($current) !== '') {
                    $statements[] = trim($current);
                }
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (trim($current) !== '') {
            $statements[] = trim($current);
        }
        return $statements;
    }
}
