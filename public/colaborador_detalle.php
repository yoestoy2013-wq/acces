<?php
require_once __DIR__ . '/../controllers/ColaboradorController.php';
require_once __DIR__ . '/../models/Evento.php';
require_once __DIR__ . '/../models/Invitado.php';
require_once __DIR__ . '/../config/database.php';

$eventoId = isset($_GET['evento']) ? (int)$_GET['evento'] : 0;
$colaboradorId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$eventoModel = new Evento();
$colaboradorController = new ColaboradorController();
$invitadoModel = new Invitado();

$evento = null;
$colaborador = null;
$errors = [];

if ($eventoId > 0) {
    $evento = $eventoModel->find($eventoId);
    if (!$evento) {
        $errors[] = 'Evento no encontrado.';
    }
}

if ($colaboradorId > 0) {
    $colaborador = $colaboradorController->find($colaboradorId);
    if (!$colaborador) {
        $errors[] = 'Colaborador no encontrado.';
    }
}

// Get all guests for this collaborator
$db = getDatabaseConnection();
$invitados = [];
$estadisticas = [
    'total' => 0,
    'ingresados' => 0,
    'pendientes' => 0,
    'por_tipo' => []
];

if ($colaborador) {
    // Get all guests for this collaborator with ticket type info and checkin status
    $stmt = $db->prepare('
        SELECT i.*, 
               tt.nombre as tipo_nombre,
               c.estado_ingreso as checkin_estado
        FROM invitados i 
        LEFT JOIN ticket_types tt ON i.ticket_type_id = tt.id
        LEFT JOIN (
            SELECT ci1.*
            FROM checkins ci1
            JOIN (
                SELECT invitado_id, MAX(id) AS max_id
                FROM checkins
                GROUP BY invitado_id
            ) ci2 ON ci1.invitado_id = ci2.invitado_id AND ci1.id = ci2.max_id
        ) c ON c.invitado_id = i.id
        WHERE i.colaborador_id = :colaborador_id 
        ORDER BY i.nombre ASC
    ');
    $stmt->execute(['colaborador_id' => $colaboradorId]);
    $invitados = $stmt->fetchAll();
    
    // Calculate statistics
    $estadisticas['total'] = count($invitados);
    
    foreach ($invitados as $inv) {
        if ($inv['checkin_estado'] === 'ingresó') {
            $estadisticas['ingresados']++;
        } else {
            $estadisticas['pendientes']++;
        }
        
        // Group by ticket type
        $tipoNombre = $inv['tipo_nombre'] ?: 'Sin tipo';
        if (!isset($estadisticas['por_tipo'][$tipoNombre])) {
            $estadisticas['por_tipo'][$tipoNombre] = 0;
        }
        $estadisticas['por_tipo'][$tipoNombre]++;
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Detalle de Colaborador</title>
    <link rel='stylesheet' href='assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>'>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class='app'>
    <div class="header-container">
        <button class="back-button" onclick="history.back();" title="Volver">
            <i class="fas fa-chevron-left"></i>
        </button>
        <h1>COLABORADOR</h1>
        <button class="home-button" onclick="location.href='index.php';" title="Inicio">
            <i class="fas fa-home"></i>
        </button>
    </div>

    <?php if ($evento): ?>
    <p class="event-meta"><?=htmlspecialchars($evento['nombre'])?></p>
    <?php else: ?>
    <p class="event-meta" style='color:#d9534f'>No hay evento seleccionado.</p>
    <?php endif; ?>

    <?php if ($errors): ?>
    <div class='errors' style='background:#ffe6e6;padding:12px;margin:12px 0;border:1px solid #ffb3b3;'>
        <?php foreach ($errors as $error): ?>
        <p><?=htmlspecialchars($error)?></p>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if ($colaborador && $evento): ?>
    <!-- Nombre del Colaborador -->
    <div class='actions-container' style='margin-top:16px;margin-bottom:24px'>
        <div style='width:350px;max-width:100%;text-align:center;padding:16px;background:#2a2a2a;border-radius:12px'>
            <h2 style='margin:0;font-size:20px;font-weight:700;color:#fff'><?=htmlspecialchars($colaborador['nombre'])?></h2>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class='actions-container'>
        <div style='width:350px;max-width:100%;display:flex;flex-direction:column;gap:8px'>
            <!-- Total de Invitados -->
            <div style='background:#2a2a2a;padding:16px;border-radius:12px;text-align:center'>
                <p style='margin:0 0 8px 0;font-size:14px;color:#aaa;font-weight:600'>Total de invitados</p>
                <p style='margin:0;font-size:32px;font-weight:700;color:#FF6A00'><?=$estadisticas['total']?></p>
            </div>

            <!-- Ingresados -->
            <div style='background:#2a2a2a;padding:16px;border-radius:12px;text-align:center'>
                <p style='margin:0 0 8px 0;font-size:14px;color:#aaa;font-weight:600'>Ingresaron</p>
                <p style='margin:0;font-size:32px;font-weight:700;color:#4caf50'><?=$estadisticas['ingresados']?></p>
            </div>

            <!-- Pendientes -->
            <div style='background:#2a2a2a;padding:16px;border-radius:12px;text-align:center'>
                <p style='margin:0 0 8px 0;font-size:14px;color:#aaa;font-weight:600'>Pendientes</p>
                <p style='margin:0;font-size:32px;font-weight:700;color:#f44336'><?=$estadisticas['pendientes']?></p>
            </div>
        </div>
    </div>

    <!-- Detalle por Tipo de Ticket -->
    <?php if (!empty($estadisticas['por_tipo'])): ?>
    <div class='actions-container' style='margin-top:24px'>
        <div style='width:350px;max-width:100%'>
            <h3 style='margin:0 0 12px 0;font-size:16px;font-weight:700;color:#fff'>Detalle por tipo de ticket</h3>
            <div style='display:flex;flex-direction:column;gap:8px'>
                <?php foreach ($estadisticas['por_tipo'] as $tipo => $cantidad): ?>
                <div style='background:#2a2a2a;padding:12px 16px;border-radius:8px;display:flex;justify-content:space-between;align-items:center'>
                    <span style='font-size:15px;font-weight:600;color:#fff'><?=htmlspecialchars($tipo)?></span>
                    <span style='background:#FF6A00;color:#1e1e1e;font-size:14px;padding:4px 12px;border-radius:6px;font-weight:bold'><?=$cantidad?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Botón para volver a colaboradores -->
    <div class='actions-container' style='margin-top:24px'>
        <a href='colaboradores.php?evento=<?=$eventoId?>' style='text-decoration:none;width:100%'>
            <button class='action-button' style='width:100%'>Volver a Colaboradores</button>
        </a>
    </div>

    <?php else: ?>
    <p style='text-align:center;padding:24px;color:#aaa'>No se puede mostrar la información del colaborador.</p>
    <?php endif; ?>

</div>
</body>
</html>
