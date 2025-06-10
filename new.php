<?php

declare(strict_types=1);

/**
 * Generator pro čtení řádků ze souboru
 */
function readLines(string $filename): iterable
{
    $handle = fopen($filename, 'r');
    if (!$handle) {
        throw new RuntimeException("Soubor nelze otevřít: $filename");
    }

    while (!feof($handle)) {
        $line = fgets($handle);
        if ($line !== false) {
            yield rtrim($line);
        }
    }

    fclose($handle);
}

/**
 * Hlavní logika pro zpracování řádků
 */
function processLines(
    iterable $lines,
    callable $filter,
    callable $decorator
): array {
    $stats = [];

    foreach ($lines as $line) {
        if (!$filter($line)) {
            continue;
        }

        $decorated = $decorator($line);

        echo "Zpracováno: $decorated\n";
        if (!isset($stats[$decorated])) {
            $stats[$decorated] = 0;
        }

        $stats[$decorated]++;
    }

    arsort($stats);
    return $stats;
}

// Filtr: vynechává DEBUG zprávy
$filter = function (string $line): bool {
    return !preg_match('/test\.DEBUG/i', $line);
};

// Dekorátor: extrahuje úroveň logování (např. "error", "info")
$decorator = function (string $line): string {
    if (preg_match('/test\.(\w+)/i', $line, $matches)) {
        return strtolower($matches[1]);
    }
    return 'unknown';
};

// ----------- Spuštění skriptu -----------

if ($argc < 2) {
    echo "Použití: php modern.php <soubor>\n";
    exit(1);
}

$filename = $argv[1];

try {
    $lines = readLines($filename);
    $result = processLines($lines, $filter, $decorator);

    foreach ($result as $key => $count) {
        echo "$key: $count\n";
    }
} catch (Throwable $e) {
    echo "Chyba: " . $e->getMessage() . "\n";
    exit(1);
}
