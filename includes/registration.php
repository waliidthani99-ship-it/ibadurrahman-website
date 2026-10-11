<?php
/**
 * Official student registration number helpers.
 *
 * Format: IBR-YYYY-NNNN  (e.g. IBR-2026-0001)
 * Numbers are generated on the server, are unique and are never reused.
 * Generation MUST be called inside a database transaction.
 */

if (!function_exists('generate_registration_number')) {
    function generate_registration_number(PDO $pdo, $year = null) {
        $year = ($year === null || $year === '' || !is_numeric($year)) ? (int)date('Y') : (int)$year;

        $pdo->prepare("INSERT IGNORE INTO registration_counters (year, last_number) VALUES (?, 0)")->execute([$year]);

        $stmt = $pdo->prepare("SELECT last_number FROM registration_counters WHERE year = ? FOR UPDATE");
        $stmt->execute([$year]);
        $last = (int)$stmt->fetchColumn();
        $next = $last + 1;

        $pdo->prepare("UPDATE registration_counters SET last_number = ? WHERE year = ?")->execute([$next, $year]);

        return sprintf('IBR-%04d-%04d', $year, $next);
    }
}

if (!function_exists('assign_registration_number_if_missing')) {
    /**
     * Locks a student row and assigns an official number when it has none.
     * MUST be called inside a transaction. Returns the (existing or new) number.
     */
    function assign_registration_number_if_missing(PDO $pdo, $studentId, $year = null) {
        $stmt = $pdo->prepare("SELECT registration_number FROM students WHERE id = ? FOR UPDATE");
        $stmt->execute([$studentId]);
        $existing = $stmt->fetchColumn();
        if ($existing === false) return null;
        if ($existing !== null && $existing !== '') return $existing;

        $number = generate_registration_number($pdo, $year);
        $pdo->prepare("UPDATE students SET registration_number = ? WHERE id = ?")->execute([$number, $studentId]);
        return $number;
    }
}

if (!function_exists('format_registration_number')) {
    function format_registration_number($number) {
        return ($number !== null && trim((string)$number) !== '') ? $number : '—';
    }
}

if (!function_exists('format_admission_year')) {
    function format_admission_year($year) {
        return ($year !== null && $year !== '') ? (string)$year : 'Unknown';
    }
}
