<?php
require_once __DIR__ . '/../models/Invitado.php';
require_once __DIR__ . '/../models/Evento.php';
require_once __DIR__ . '/../models/TicketType.php';
require_once __DIR__ . '/../models/Checkin.php';
require_once __DIR__ . '/../helpers/QRCodeGenerator.php';

$invitadoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$invitadoModel = new Invitado();
$eventoModel = new Evento();
$ticketTypeModel = new TicketType();
$checkinModel = new Checkin();

$error = '';
$invitado = null;
$evento = null;
$ticketType = null;
$checkin = null;

if ($invitadoId <= 0) {
    $error = 'ID de invitado inválido.';
} else {
    $invitado = $invitadoModel->find($invitadoId);
    if (!$invitado) {
        $error = 'Invitado no encontrado.';
    } else {
        $evento = $eventoModel->find((int)$invitado['evento_id']);
        if (!$evento) {
            $error = 'Evento no encontrado.';
        } else {
            $ticketType = $ticketTypeModel->find((int)$invitado['ticket_type_id']);
            $checkin = $checkinModel->findLatestByInvitado($invitadoId);
        }
    }
}

$estado = 'Pendiente';
$fechaIngreso = null;
$horaIngreso = null;

if ($checkin && $checkin['estado_ingreso'] === 'ingresó') {
    $estado = 'Ingresó';
    if ($checkin['checkin_time']) {
        $timestamp = strtotime($checkin['checkin_time']);
        $fechaIngreso = date('d/m/Y', $timestamp);
        $horaIngreso = date('H:i', $timestamp);
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Entrada Digital</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }
        .entrada-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .error {
            background: #ffe6e6;
            border: 1px solid #ffb3b3;
            color: #d9534f;
            padding: 15px;
            border-radius: 4px;
            text-align: center;
            font-size: 16px;
        }
        .evento-info {
            text-align: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        .evento-nombre {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 8px;
        }
        .evento-fecha {
            font-size: 14px;
            color: #666;
            margin-bottom: 4px;
        }
        .evento-direccion {
            font-size: 14px;
            color: #666;
        }
        .qr-container {
            display: flex;
            justify-content: center;
            margin: 30px 0;
        }
        .qr-container img {
            width: 280px;
            height: 280px;
            border: 3px solid #f0f0f0;
        }
        .invitado-info {
            background: #f9f9f9;
            padding: 20px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e8e8e8;
            font-size: 15px;
        }
        .info-row:last-child {
            border-bottom: none;
        }
        .info-label {
            font-weight: bold;
            color: #333;
        }
        .info-value {
            color: #666;
            text-align: right;
            flex: 1;
            margin-left: 15px;
        }
        .estado-pendiente {
            color: #ff9800;
            font-weight: bold;
        }
        .estado-ingreso {
            color: #4caf50;
            font-weight: bold;
        }
        .ingreso-details {
            background: #e8f5e9;
            border: 1px solid #c8e6c9;
            border-radius: 4px;
            padding: 15px;
            margin-top: 15px;
        }
        .ingreso-details-title {
            font-weight: bold;
            color: #2e7d32;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .ingreso-detail-row {
            display: flex;
            justify-content: space-between;
            font-size: 14px;
            padding: 5px 0;
        }
        .ingreso-detail-label {
            font-weight: bold;
            color: #2e7d32;
        }
        .ingreso-detail-value {
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class='entrada-container'>
        <?php if ($error): ?>
            <div class='error'><?=htmlspecialchars($error)?></div>
        <?php else: ?>
            <!-- Evento Info -->
            <div class='evento-info'>
                <div class='evento-nombre'><?=htmlspecialchars($evento['nombre'])?></div>
                <div class='evento-fecha'><?=date('d/m/Y', strtotime($evento['fecha']))?></div>
                <div class='evento-direccion'><?=htmlspecialchars($evento['lugar'])?></div>
            </div>

            <!-- QR -->
            <div class='qr-container'>
                <img src='qr.php?id=<?=$invitadoId?>&size=280' alt='QR' />
            </div>

            <!-- Invitado Info -->
            <div class='invitado-info'>
                <div class='info-row'>
                    <span class='info-label'>Nombre</span>
                    <span class='info-value'><?=htmlspecialchars($invitado['nombre'])?></span>
                </div>
                <div class='info-row'>
                    <span class='info-label'>Tipo de Ticket</span>
                    <span class='info-value'><?=htmlspecialchars($ticketType['nombre'] ?? '')?></span>
                </div>
                <div class='info-row'>
                    <span class='info-label'>Estado</span>
                    <span class='info-value <?=$estado === 'Ingresó' ? 'estado-ingreso' : 'estado-pendiente'?>'><?=htmlspecialchars($estado)?></span>
                </div>

                <?php if ($estado === 'Ingresó'): ?>
                <div class='ingreso-details'>
                    <div class='ingreso-details-title'>Información de Ingreso</div>
                    <div class='ingreso-detail-row'>
                        <span class='ingreso-detail-label'>Fecha:</span>
                        <span class='ingreso-detail-value'><?=htmlspecialchars($fechaIngreso)?></span>
                    </div>
                    <div class='ingreso-detail-row'>
                        <span class='ingreso-detail-label'>Hora:</span>
                        <span class='ingreso-detail-value'><?=htmlspecialchars($horaIngreso)?></span>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
