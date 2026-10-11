<?php
/**
 * One-time, idempotent migration for the students registration upgrade.
 *
 * Safe to run multiple times. CLI only.
 *   php database/migrate.php
 */

if (PHP_SAPI !== 'cli') {
    die("This migration is intended for CLI use only.\n");
}

require_once __DIR__ . '/../config/db_config.php';
require_once __DIR__ . '/../includes/registration.php';

$pdo = getDbConnection();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

function columnMeta($pdo, $table, $column) {
    $s = $pdo->prepare("SELECT COLUMN_TYPE, IS_NULLABLE FROM information_schema.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?");
    $s->execute([$table, $column]);
    return $s->fetch(PDO::FETCH_ASSOC) ?: null;
}
function indexExists($pdo, $table, $index) {
    $s = $pdo->prepare("SELECT COUNT(*) FROM information_schema.STATISTICS
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?");
    $s->execute([$table, $index]);
    return (int)$s->fetchColumn() > 0;
}
function tableExists($pdo, $table) {
    $s = $pdo->prepare("SELECT COUNT(*) FROM information_schema.TABLES
                        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?");
    $s->execute([$table]);
    return (int)$s->fetchColumn() > 0;
}

echo "== Migrating students table ==\n";

// 1. registration_number
if (!columnMeta($pdo, 'students', 'registration_number')) {
    $pdo->exec("ALTER TABLE students ADD COLUMN registration_number VARCHAR(50) NULL AFTER id");
    echo "  + added registration_number\n";
} else { echo "  = registration_number exists\n"; }

// 2. student_type
if (!columnMeta($pdo, 'students', 'student_type')) {
    $pdo->exec("ALTER TABLE students ADD COLUMN student_type ENUM('legacy','new') NOT NULL DEFAULT 'new' AFTER registration_id");
    echo "  + added student_type\n";
} else { echo "  = student_type exists\n"; }

// 3. current_class
if (!columnMeta($pdo, 'students', 'current_class')) {
    $pdo->exec("ALTER TABLE students ADD COLUMN current_class VARCHAR(100) NULL AFTER date_of_birth");
    echo "  + added current_class\n";
} else { echo "  = current_class exists\n"; }

// 4. admission_year
if (!columnMeta($pdo, 'students', 'admission_year')) {
    $pdo->exec("ALTER TABLE students ADD COLUMN admission_year SMALLINT NULL AFTER current_class");
    echo "  + added admission_year\n";
} else { echo "  = admission_year exists\n"; }

// 5. registered_at
if (!columnMeta($pdo, 'students', 'registered_at')) {
    $pdo->exec("ALTER TABLE students ADD COLUMN registered_at DATETIME NULL AFTER admission_year");
    echo "  + added registered_at\n";
} else { echo "  = registered_at exists\n"; }

// 6. status enum -> include 'Active'
$statusMeta = columnMeta($pdo, 'students', 'status');
if ($statusMeta && strpos($statusMeta['COLUMN_TYPE'], "'Active'") === false) {
    $pdo->exec("ALTER TABLE students MODIFY COLUMN status ENUM('Pending','Approved','Rejected','Active') NOT NULL DEFAULT 'Pending'");
    echo "  + extended status enum with 'Active'\n";
} else { echo "  = status enum ok\n"; }

// 7. date_of_birth nullable (legacy students may not know it)
$dobMeta = columnMeta($pdo, 'students', 'date_of_birth');
if ($statusMeta !== null && $dobMeta && $dobMeta['IS_NULLABLE'] === 'NO') {
    $pdo->exec("ALTER TABLE students MODIFY COLUMN date_of_birth DATE NULL");
    echo "  + date_of_birth is now nullable\n";
} else { echo "  = date_of_birth nullable\n"; }

// 7b. Unique index on registration_number
if (!indexExists($pdo, 'students', 'uq_registration_number')) {
    $pdo->exec("ALTER TABLE students ADD UNIQUE KEY uq_registration_number (registration_number)");
    echo "  + added unique index uq_registration_number\n";
} else { echo "  = unique index exists\n"; }

// 7c. index on student_type
if (!indexExists($pdo, 'students', 'idx_student_type')) {
    $pdo->exec("ALTER TABLE students ADD KEY idx_student_type (student_type)");
    echo "  + added index idx_student_type\n";
} else { echo "  = idx_student_type exists\n"; }

// 8. registration_counters
if (!tableExists($pdo, 'registration_counters')) {
    $pdo->exec("CREATE TABLE registration_counters (
        year SMALLINT NOT NULL,
        last_number INT NOT NULL DEFAULT 0,
        PRIMARY KEY (year)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "  + created registration_counters\n";
} else { echo "  = registration_counters exists\n"; }

echo "== Backfilling data ==\n";

$updated = $pdo->exec("UPDATE students SET registered_at = created_at WHERE registered_at IS NULL");
echo "  registered_at backfilled: " . (int)$updated . "\n";

// Assign official numbers to already approved/active students that have none.
$rows = $pdo->query("SELECT id, COALESCE(YEAR(registered_at), YEAR(created_at)) AS yr
                     FROM students
                     WHERE status IN ('Approved','Active')
                       AND (registration_number IS NULL OR registration_number = '')
                     ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$assigned = 0;
foreach ($rows as $row) {
    $pdo->beginTransaction();
    try {
        $number = generate_registration_number($pdo, $row['yr']);
        $pdo->prepare("UPDATE students SET registration_number = ?, registered_at = COALESCE(registered_at, created_at) WHERE id = ?")
            ->execute([$number, $row['id']]);
        $pdo->commit();
        echo "  #{$row['id']} -> {$number}\n";
        $assigned++;
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "  ! failed for #{$row['id']}: " . $e->getMessage() . "\n";
    }
}
echo "  numbers assigned: {$assigned}\n";

echo "== Migration complete ==\n";
