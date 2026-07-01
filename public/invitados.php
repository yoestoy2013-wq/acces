<?php
require_once __DIR__ . '/../controllers/InvitadoController.php';
require_once __DIR__ . '/../models/Evento.php';

$eventoId = isset($_GET['evento']) ? (int)$_GET['evento'] : 0;
$eventoModel = new Evento();
$invitadoController = new InvitadoController();
$evento = $eventoModel->find($eventoId);
$errors = [];
$success = '';
$invitado = null;
$ticketTypes = [];

if (!$evento) {
    $errors[] = 'Evento no encontrado. Vuelve a la lista de eventos.';
}

if ($evento) {
    $ticketTypes = $invitadoController->listTicketTypes($eventoId);
    $ticketTypeMap = [];
    foreach ($ticketTypes as $type) {
        $ticketTypeMap[$type['id']] = $type['nombre'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evento) {
    $formData = [
        'ticket_type_id' => isset($_POST['ticket_type_id']) ? (int)$_POST['ticket_type_id'] : 0,
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellido' => trim($_POST['apellido'] ?? ''),
        'dni' => trim($_POST['dni'] ?? ''),
        'email' => trim($_POST['email'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'observaciones' => trim($_POST['observaciones'] ?? ''),
    ];

    $errors = $invitadoController->validateData($formData);
    if (empty($errors)) {
        try {
            if (isset($_POST['id']) && $_POST['id'] !== '') {
                $invitadoId = (int)$_POST['id'];
                if ($invitadoController->update($invitadoId, $formData)) {
                    header('Location: invitados.php?evento=' . $eventoId);
                    exit;
                }
                $errors[] = 'No se pudo actualizar el invitado.';
            } else {
                $invitadoController->create($eventoId, $formData);
                header('Location: invitados.php?evento=' . $eventoId);
                exit;
            }
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}

if ($evento && isset($_GET['delete'])) {
    $invitadoId = (int)$_GET['delete'];
    if ($invitadoController->delete($invitadoId)) {
        header('Location: invitados.php?evento=' . $eventoId);
        exit;
    }
    $errors[] = 'No se pudo eliminar el invitado.';
}

if ($evento && isset($_GET['edit'])) {
    $invitado = $invitadoController->find((int)$_GET['edit']);
    if (!$invitado) {
        $errors[] = 'Invitado no encontrado.';
    }
}

$invitados = $evento ? $invitadoController->listByEvento($eventoId) : [];
?>
<!doctype html><html><head><meta charset='utf-8'><title>Invitados</title>
<link rel='stylesheet' href='assets/css/style.css'></head><body><div class='app' style='max-width:900px;text-align:left'>
<h1>INVITADOS</h1>
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
<?php if ($evento): ?>
<form method='post' style='margin-bottom:24px'>
<input type='hidden' name='id' value='<?=htmlspecialchars($invitado['id'] ?? '')?>'>
<label style='display:block;margin:8px 0'>Tipo de ticket</label>
<select name='ticket_type_id' required style='width:100%;padding:12px;margin:8px 0'>
<?php if (!$ticketTypes): ?>
<option value=''>No hay tipos de ticket disponibles</option>
<?php else: ?>
<option value=''>Selecciona un tipo de ticket</option>
<?php foreach ($ticketTypes as $type): ?>
<option value='<?=htmlspecialchars($type['id'])?>' <?=isset($invitado['ticket_type_id']) && $invitado['ticket_type_id'] == $type['id'] ? 'selected' : ''?>><?=htmlspecialchars($type['nombre'])?> (<?=htmlspecialchars($type['precio'])?>)</option>
<?php endforeach; ?>
<?php endif; ?>
</select>
<input name='nombre' placeholder='Nombre' value='<?=htmlspecialchars($invitado['nombre'] ?? '')?>' required style='width:100%;padding:12px;margin:8px 0'>
<input name='apellido' placeholder='Apellido' value='<?=htmlspecialchars($invitado['apellido'] ?? '')?>' style='width:100%;padding:12px;margin:8px 0'>
<input name='dni' placeholder='DNI' value='<?=htmlspecialchars($invitado['dni'] ?? '')?>' style='width:100%;padding:12px;margin:8px 0'>
<input name='email' type='email' placeholder='Email' value='<?=htmlspecialchars($invitado['email'] ?? '')?>' style='width:100%;padding:12px;margin:8px 0'>
<input name='telefono' placeholder='Teléfono' value='<?=htmlspecialchars($invitado['telefono'] ?? '')?>' style='width:100%;padding:12px;margin:8px 0'>
<textarea name='observaciones' placeholder='Observaciones' style='width:100%;padding:12px;margin:8px 0'><?=htmlspecialchars($invitado['observaciones'] ?? '')?></textarea>
<button type='submit'><?=isset($invitado['id']) ? 'Actualizar' : 'Crear'?></button>
</form>
<table border='1' cellpadding='8' style='width:100%'>
<tr><th>ID</th><th>Nombre</th><th>Apellido</th><th>Ticket</th><th>Email</th><th>Teléfono</th><th>Acciones</th></tr>
<?php if (!$invitados): ?>
<tr><td colspan='7'>No hay invitados cargados.</td></tr>
<?php else: foreach ($invitados as $guest): ?>
<tr>
<td><?=htmlspecialchars($guest['id'])?></td>
<td><?=htmlspecialchars($guest['nombre'])?></td>
<td><?=htmlspecialchars($guest['apellido'])?></td>
<td><?=htmlspecialchars($ticketTypeMap[$guest['ticket_type_id']] ?? $guest['ticket_type_id'])?></td>
<td><?=htmlspecialchars($guest['email'])?></td>
<td><?=htmlspecialchars($guest['telefono'])?></td>
<td><div class='actions'>
<a href='invitados.php?evento=<?=$eventoId?>&edit=<?=$guest['id']?>'><button>✏️</button></a>
<a href='invitados.php?evento=<?=$eventoId?>&delete=<?=$guest['id']?>' onclick="return confirm('¿Eliminar invitado?')"><button>🗑️</button></a>
</div></td>
</tr>
<?php endforeach; endif; ?>
</table>
<?php endif; ?>
<p><a href='eventos.php'>Volver</a></p>
</div></body></html>