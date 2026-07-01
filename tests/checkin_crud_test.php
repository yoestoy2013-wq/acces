<?php

require_once __DIR__ . '/test_helper.php';
require_once __DIR__ . '/../models/Evento.php';
require_once __DIR__ . '/../models/TicketType.php';
require_once __DIR__ . '/../models/Invitado.php';
require_once __DIR__ . '/../models/Checkin.php';

function runTest(): void
{
    echo "Running checkin CRUD test...\n";

    $pdo = getServerConnection();
    createTestDatabase($pdo);

    $eventoModel = new Evento();
    $ticketTypeModel = new TicketType();
    $invitadoModel = new Invitado();
    $checkinModel = new Checkin();

    echo "Creating event...\n";
    $eventoId = $eventoModel->create([
        'nombre' => 'Test Event Checkin',
        'fecha' => '2026-07-01',
        'lugar' => 'Test Venue',
    ]);

    echo "Creating ticket type...\n";
    $ticketTypeId = $ticketTypeModel->create([
        'evento_id' => $eventoId,
        'nombre' => 'General',
        'precio' => 100.00,
        'color' => 'blue',
        'activo' => 1,
    ]);

    echo "Creating invitado...\n";
    $invitadoId = $invitadoModel->create([
        'evento_id' => $eventoId,
        'ticket_type_id' => $ticketTypeId,
        'nombre' => 'Laura',
        'apellido' => 'Gomez',
        'dni' => '54321000',
        'email' => 'laura@example.com',
        'telefono' => '555-9876',
        'observaciones' => 'Check-in test',
    ]);

    echo "Searching invitado...\n";
    $results = $checkinModel->searchInvitados('Laura', $eventoId);
    if (count($results) !== 1 || $results[0]['id'] != $invitadoId) {
        throw new RuntimeException('Search invitado failed.');
    }

    echo "Registering checkin...\n";
    $checkin = $checkinModel->register($invitadoId, $ticketTypeId);
    if ($checkin['estado_ingreso'] !== 'ingresó' || $checkin['checkin_time'] === null) {
        throw new RuntimeException('Checkin registration failed.');
    }

    echo "Preventing double checkin...\n";
    try {
        $checkinModel->register($invitadoId, $ticketTypeId);
        throw new RuntimeException('Double checkin should not be allowed.');
    } catch (RuntimeException $e) {
        if ($e->getMessage() !== 'El invitado ya ingresó.') {
            throw new RuntimeException('Incorrect double checkin error: ' . $e->getMessage());
        }
    }

    echo "Verifying latest checkin status...\n";
    $latest = $checkinModel->findLatestByInvitado($invitadoId);
    if ($latest === null || $latest['estado_ingreso'] !== 'ingresó') {
        throw new RuntimeException('Latest checkin status is incorrect.');
    }

    echo "Checkin CRUD test passed successfully.\n";
}

try {
    runTest();
    echo "Checkin automatic test completed successfully.\n";
} catch (Throwable $e) {
    echo 'Test failed: ' . $e->getMessage() . "\n";
    exit(1);
}
