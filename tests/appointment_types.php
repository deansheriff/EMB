<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$expected = [
    'Fertility and Treatment Consultation' => 5000000,
    'PGT Consultation' => 5000000,
    'Embryology Career Consultation' => 2500000,
    'TTC Community' => 0,
    'General Enquiry or Partnership' => 0,
];

$rows = query_all('SELECT name, price, currency, is_active FROM appointment_types ORDER BY sort_order, name');
$seeded = [];
foreach ($rows as $row) {
    if (array_key_exists($row['name'], $expected)) {
        $seeded[$row['name']] = (int) $row['price'];
        if ($row['currency'] !== 'NGN' || (int) $row['is_active'] !== 1) {
            throw new RuntimeException('Seeded appointment types must be active and use NGN.');
        }
    }
}
if ($seeded !== $expected) {
    throw new RuntimeException('The seeded appointment types or prices do not match the required catalogue.');
}

$columnExists = (int) query_value(
    "SELECT COUNT(*) FROM information_schema.columns
     WHERE table_schema = DATABASE() AND table_name = 'appointments' AND column_name = 'appointment_type_id'"
);
if ($columnExists !== 1) {
    throw new RuntimeException('Appointments must retain a reference to their configured appointment type.');
}

$pdo = db();
$pdo->beginTransaction();
try {
    $name = 'QA configurable appointment ' . bin2hex(random_bytes(4));
    $price = money_to_subunit('12345.67');
    $pdo->prepare('INSERT INTO appointment_types (name, description, price, currency, sort_order, is_active) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([$name, 'Temporary admin configuration test.', $price, 'NGN', 9999, 1]);
    $created = query_one('SELECT * FROM appointment_types WHERE name = ?', [$name]);
    if (!$created || (int) $created['price'] !== 1234567 || (int) $created['is_active'] !== 1) {
        throw new RuntimeException('A new admin-configured appointment type did not retain its price.');
    }
    $pdo->rollBack();
} catch (Throwable $exception) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    throw $exception;
}

echo json_encode([
    'passed' => true,
    'seeded_types' => $seeded,
    'admin_price_round_trip' => true,
    'appointment_type_reference' => true,
], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR) . PHP_EOL;
