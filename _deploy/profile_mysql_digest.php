<?php
define('BASEPATH', true);
require '/var/www/html/t2gworkroom/application/config/app-config.php';

$db = new mysqli(APP_DB_HOSTNAME, APP_DB_USERNAME, APP_DB_PASSWORD, APP_DB_NAME);
$db->set_charset('utf8mb4');

if (($argv[1] ?? '') === '--reset') {
    if (!$db->query('TRUNCATE TABLE performance_schema.events_statements_summary_by_digest')) {
        fwrite(STDERR, $db->error . PHP_EOL);
        exit(1);
    }
    echo "RESET\n";
    exit(0);
}

$sql = <<<'SQL'
SELECT COUNT_STAR,
       ROUND(SUM_TIMER_WAIT / 1000000000000, 4) AS total_seconds,
       ROUND(AVG_TIMER_WAIT / 1000000000, 3) AS avg_ms,
       LEFT(DIGEST_TEXT, 240) AS statement_text
FROM performance_schema.events_statements_summary_by_digest
WHERE SCHEMA_NAME = DATABASE()
ORDER BY SUM_TIMER_WAIT DESC
LIMIT 30
SQL;
$result = $db->query($sql);
if (!$result) {
    fwrite(STDERR, $db->error . PHP_EOL);
    exit(1);
}
echo json_encode($result->fetch_all(MYSQLI_ASSOC), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), PHP_EOL;
