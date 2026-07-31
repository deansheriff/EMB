<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

$code = 'QA-PAY-' . strtoupper(bin2hex(random_bytes(4)));
$reference = 'QA-REF-' . strtoupper(bin2hex(random_bytes(5)));
$appointmentId = 0;
$exitCode = 1;

try {
    $stmt = db()->prepare(
        "INSERT INTO appointments
         (booking_code, consultation_type, name, email, phone, preferred_contact, status, amount_due, currency, payment_status, consented_at)
         VALUES (?, 'Fertility consultation', 'QA Paystack Client', 'qa.paystack@example.com', '+2347000000001', 'Email', 'pending_payment', 2500000, 'NGN', 'pending', NOW())"
    );
    $stmt->execute([$code]);
    $appointmentId = (int) db()->lastInsertId();
    db()->prepare(
        "INSERT INTO appointment_payments (appointment_id, reference, amount, currency, status)
         VALUES (?, ?, 2500000, 'NGN', 'pending')"
    )->execute([$appointmentId, $reference]);

    $mismatchRejected = false;
    try {
        complete_paystack_payment([
            'reference' => $reference,
            'status' => 'success',
            'amount' => 2400000,
            'currency' => 'NGN',
        ], 'webhook', ['event' => 'charge.success']);
    } catch (RuntimeException $exception) {
        $mismatchRejected = str_contains($exception->getMessage(), 'amount mismatch');
    }
    $before = query_one('SELECT status FROM appointment_payments WHERE reference = ?', [$reference]);

    complete_paystack_payment([
        'reference' => $reference,
        'status' => 'success',
        'amount' => 2500000,
        'currency' => 'NGN',
        'gateway_response' => 'Successful',
        'channel' => 'card',
    ], 'webhook', ['event' => 'charge.success']);

    $after = query_one(
        'SELECT a.status, a.payment_status, p.status AS payment_record_status
         FROM appointments a JOIN appointment_payments p ON p.appointment_id = a.id WHERE a.id = ?',
        [$appointmentId]
    );
    $passed = $mismatchRejected
        && $before['status'] === 'pending'
        && $after['status'] === 'new'
        && $after['payment_status'] === 'paid'
        && $after['payment_record_status'] === 'success';
    echo json_encode([
        'passed' => $passed,
        'mismatch_rejected' => $mismatchRejected,
        'status_after_mismatch' => $before['status'],
        'final' => $after,
    ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT) . PHP_EOL;
    $exitCode = $passed ? 0 : 1;
} finally {
    if ($appointmentId > 0) {
        db()->prepare("DELETE FROM email_logs WHERE related_type = 'appointment' AND related_id = ?")->execute([$appointmentId]);
        db()->prepare('DELETE FROM appointments WHERE id = ?')->execute([$appointmentId]);
    }
}
exit($exitCode);
