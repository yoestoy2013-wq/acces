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
$colaboradores = [];
$colaboradorMap = [];

if (!$evento) {
    $errors[] = 'Evento no encontrado. Vuelve a la lista de eventos.';
}

if ($evento) {
    $ticketTypes = $invitadoController->listTicketTypes($eventoId);
    $ticketTypeMap = [];
    foreach ($ticketTypes as $type) {
        $ticketTypeMap[$type['id']] = $type['nombre'];
    }
    
    $colaboradores = $invitadoController->listColaboradores($eventoId);
    $colaboradorMap = [];
    foreach ($colaboradores as $col) {
        $colaboradorMap[$col['id']] = $col['nombre'];
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evento) {
    $isEdit = isset($_POST['id']) && $_POST['id'] !== '';
    if ($isEdit) {
        $existingInvitado = $invitadoController->find((int)$_POST['id']);
    }
    
    $formData = [
        'ticket_type_id' => isset($_POST['ticket_type_id']) ? (int)$_POST['ticket_type_id'] : 0,
        'colaborador_id' => isset($_POST['colaborador_id']) && $_POST['colaborador_id'] !== '' ? (int)$_POST['colaborador_id'] : null,
        'nombre' => trim($_POST['nombre'] ?? ''),
        'apellido' => '',
        'dni' => $isEdit ? ($existingInvitado['dni'] ?? '') : '',
        'email' => $isEdit ? ($existingInvitado['email'] ?? '') : '',
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
<label style='display:block;margin:8px 0'>Nombre y apellido (obligatorio)</label>
<input name='nombre' placeholder='Nombre y apellido' value='<?=htmlspecialchars($invitado['nombre'] ?? '')?>' required style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'>
<label style='display:block;margin:8px 0'>Tipo de ticket (obligatorio)</label>
<select name='ticket_type_id' required style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'>
<?php if (!$ticketTypes): ?>
<option value=''>No hay tipos de ticket disponibles</option>
<?php else: ?>
<option value=''>Selecciona un tipo de ticket</option>
<?php foreach ($ticketTypes as $type): ?>
<option value='<?=htmlspecialchars($type['id'])?>' <?=isset($invitado['ticket_type_id']) && $invitado['ticket_type_id'] == $type['id'] ? 'selected' : ''?>><?=htmlspecialchars($type['nombre'])?> (<?=htmlspecialchars($type['precio'])?>)</option>
<?php endforeach; ?>
<?php endif; ?>
</select>
<label style='display:block;margin:8px 0'>Colaborador (opcional)</label>
<select name='colaborador_id' style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'>
<?php if (!$colaboradores): ?>
<option value=''>No hay colaboradores disponibles</option>
<?php else: ?>
<option value=''>-- Sin colaborador --</option>
<?php foreach ($colaboradores as $col): ?>
<option value='<?=htmlspecialchars($col['id'])?>' <?=isset($invitado['colaborador_id']) && $invitado['colaborador_id'] == $col['id'] ? 'selected' : ''?>><?=htmlspecialchars($col['nombre'])?></option>
<?php endforeach; ?>
<?php endif; ?>
</select>
<label style='display:block;margin:8px 0'>Teléfono (opcional)</label>
<input name='telefono' placeholder='Teléfono' value='<?=htmlspecialchars($invitado['telefono'] ?? '')?>' style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'>
<label style='display:block;margin:8px 0'>Observaciones (opcional)</label>
<textarea name='observaciones' placeholder='Observaciones' style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'><?=htmlspecialchars($invitado['observaciones'] ?? '')?></textarea>
<button type='submit' style='padding:12px 24px;margin-top:12px'><?=isset($invitado['id']) ? 'Actualizar' : 'Crear'?></button>
</form>
<table border='1' cellpadding='8' style='width:100%'>
<tr><th>ID</th><th>Código</th><th>Nombre</th><th>Ticket</th><th>Colaborador</th><th>Teléfono</th><th>Acciones</th></tr>
<?php if (!$invitados): ?>
<tr><td colspan='7'>No hay invitados cargados.</td></tr>
<?php else: foreach ($invitados as $guest): ?>
<tr>
<td><?=htmlspecialchars($guest['id'])?></td>
<td style='font-family:monospace;font-size:12px'><?=htmlspecialchars($guest['unique_id'] ?? 'N/A')?></td>
<td><?=htmlspecialchars($guest['nombre'])?></td>
<td><?=htmlspecialchars($ticketTypeMap[$guest['ticket_type_id']] ?? $guest['ticket_type_id'])?></td>
<td><?=htmlspecialchars($colaboradorMap[$guest['colaborador_id']] ?? '-')?></td>
<td><?=htmlspecialchars($guest['telefono'])?></td>
<td><div class='actions'>
<a href='entrada_digital.php?id=<?=$guest['id']?>' target='_blank' title='Ver Entrada'><button>👁 Ver Entrada</button></a>
<a href='invitados.php?evento=<?=$eventoId?>&edit=<?=$guest['id']?>'><button>✏️</button></a>
<a href='invitados.php?evento=<?=$eventoId?>&delete=<?=$guest['id']?>' onclick="return confirm('¿Eliminar invitado?')"><button>🗑️</button></a>
</div></td>
</tr>
<?php endforeach; endif; ?>
</table>
<?php endif; ?>
<p><a href='eventos.php'>Volver</a></p>
</div></body></html>