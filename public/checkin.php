<?php
require_once __DIR__ . '/../controllers/CheckinController.php';
require_once __DIR__ . '/../models/Evento.php';

$eventoId = isset($_GET['evento']) ? (int)$_GET['evento'] : 0;
$q = trim($_GET['q'] ?? '');
$errors = [];
$success = '';
$checkinController = new CheckinController();
$evento = null;
$results = [];

if ($eventoId) {
    $evento = $checkinController->findEvento($eventoId);
    if (!$evento) {
        $errors[] = 'Evento no encontrado. Seleccione un evento válido.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $invitadoId = isset($_POST['invitado_id']) ? (int)$_POST['invitado_id'] : 0;
    $q = trim($_POST['q'] ?? '');
    $eventoId = isset($_POST['evento']) ? (int)$_POST['evento'] : $eventoId;

    try {
        $checkinController->registerCheckin($invitadoId);
        $success = 'Ingreso registrado correctamente.';
        header('Location: checkin.php?evento=' . $eventoId . '&q=' . urlencode($q));
        exit;
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}

if ($q !== '' || $evento) {
    $results = $checkinController->searchInvitados($q, $eventoId);
}
?>
<!doctype html><html><head><meta charset='utf-8'><title>Check-in</title>
<link rel='stylesheet' href='assets/css/style.css'></head><body><div class='app' style='max-width:900px;text-align:left'>
<h1>CHECK-IN</h1>
<?php if ($evento): ?>
<p>Evento: <?=htmlspecialchars($evento['nombre'])?></p>
<?php endif; ?>
<?php if ($errors): ?>
<div class='errors' style='background:#ffe6e6;padding:12px;margin:12px 0;border:1px solid #ffb3b3;'>
<?php foreach ($errors as $error): ?>
<p><?=htmlspecialchars($error)?></p>
<?php endforeach; ?>
</div>
<?php endif; ?>
<?php if ($success): ?>
<div class='success' style='background:#e6ffea;padding:12px;margin:12px 0;border:1px solid #b3ffcc;'><p><?=htmlspecialchars($success)?></p></div>
<?php endif; ?>
<form method='get' style='margin-bottom:24px'>
<input type='hidden' name='evento' value='<?=htmlspecialchars($eventoId)?>'>
<input name='q' placeholder='Buscar invitado por nombre, apellido o DNI' value='<?=htmlspecialchars($q)?>' style='width:100%;padding:12px;margin:8px 0'>
<button type='submit'>Buscar</button>
</form>
<?php if ($q === '' && !$evento): ?>
<p>Introduce un término de búsqueda o selecciona un evento para empezar.</p>
<?php endif; ?>
<?php if ($results): ?>
<table border='1' cellpadding='8' style='width:100%'>
<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>DNI</th><th>Evento</th><th>Ticket</th><th>Estado</th><th>Hora de ingreso</th><th>Acciones</th></tr>
<?php foreach ($results as $guest): ?>
<tr>
<td><?=htmlspecialchars($guest['id'])?></td>
<td><?=htmlspecialchars($guest['nombre'])?></td>
<td><?=htmlspecialchars($guest['apellido'])?></td>
<td><?=htmlspecialchars($guest['dni'])?></td>
<td><?=htmlspecialchars($guest['evento_nombre'])?></td>
<td><?=htmlspecialchars($guest['ticket_nombre'])?> <?=htmlspecialchars($guest['ticket_precio']) ? '(' . htmlspecialchars($guest['ticket_precio']) . ')' : ''?></td>
<td><?=htmlspecialchars($guest['checkin_estado'] ?: 'pendiente')?></td>
<td><?=htmlspecialchars($guest['checkin_time'] ?? '-')?></td>
<td><div class='actions'>
<?php if (($guest['checkin_estado'] ?? '') === 'ingresó'): ?>
<button disabled>Ingresó</button>
<?php else: ?>
<form method='post' style='display:inline'>
<input type='hidden' name='invitado_id' value='<?=htmlspecialchars($guest['id'])?>'>
<input type='hidden' name='evento' value='<?=htmlspecialchars($eventoId)?>'>
<input type='hidden' name='q' value='<?=htmlspecialchars($q)?>'>
<button type='submit'>Registrar</button>
</form>
<?php endif; ?>
</div></td>
</tr>
<?php endforeach; ?>
</table>
<?php elseif ($q !== '' || $evento): ?>
<p>No se encontraron invitados para esa búsqueda.</p>
<?php endif; ?>
<p><a href='eventos.php'>Volver</a></p>
</div></body></html>
