<?php
require_once __DIR__ . '/../controllers/TicketTypeController.php';
require_once __DIR__ . '/../models/Evento.php';

$eventoId = isset($_GET['evento']) ? (int)$_GET['evento'] : 0;
$eventoModel = new Evento();
$ticketController = new TicketTypeController();
$evento = $eventoModel->find($eventoId);
$errors = [];
$success = '';
$ticket = null;

if (!$evento) {
    $errors[] = 'Evento no encontrado. Vuelve a la lista de eventos.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evento) {
    $formData = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'precio' => trim($_POST['precio'] ?? ''),
        'color' => trim($_POST['color'] ?? ''),
        'activo' => isset($_POST['activo']) ? 1 : 0,
    ];

    if (isset($_POST['id']) && $_POST['id'] !== '') {
        $ticketId = (int)$_POST['id'];
        $errors = $ticketController->validateData($formData);
        if (empty($errors)) {
            if ($ticketController->update($ticketId, $formData)) {
                header('Location: tickets.php?evento=' . $eventoId);
                exit;
            }
            $errors[] = 'No se pudo actualizar el tipo de ticket.';
        }
    } else {
        $errors = $ticketController->validateData($formData);
        if (empty($errors)) {
            try {
                $ticketController->create($eventoId, $formData);
                header('Location: tickets.php?evento=' . $eventoId);
                exit;
            } catch (Throwable $e) {
                $errors[] = $e->getMessage();
            }
        }
    }
}

if ($evento && isset($_GET['delete'])) {
    $ticketId = (int)$_GET['delete'];
    if ($ticketController->delete($ticketId)) {
        header('Location: tickets.php?evento=' . $eventoId);
        exit;
    }
    $errors[] = 'No se pudo eliminar el tipo de ticket.';
}

if ($evento && isset($_GET['edit'])) {
    $ticket = $ticketController->find((int)$_GET['edit']);
    if (!$ticket) {
        $errors[] = 'Tipo de ticket no encontrado.';
    }
}

$ticketTypes = $evento ? $ticketController->listByEvento($eventoId) : [];
?>
<!doctype html><html><head><meta charset='utf-8'><title>Tickets</title>
<link rel='stylesheet' href='assets/css/style.css'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head><body><div class='app' style='max-width:900px;text-align:left'>
<button class="back-button" onclick="history.back();" title="Volver">
    <i class="fas fa-chevron-left"></i>
</button>
<h1>TIPOS DE TICKET</h1>
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
<?php if ($evento): ?>
<form method='post' style='margin-bottom:24px'>
<input type='hidden' name='id' value='<?=htmlspecialchars($ticket['id'] ?? '')?>'>
<input name='nombre' placeholder='Nombre del ticket' value='<?=htmlspecialchars($ticket['nombre'] ?? '')?>' required style='width:100%;padding:12px;margin:8px 0'>
<input name='precio' type='number' step='0.01' placeholder='Precio' value='<?=htmlspecialchars($ticket['precio'] ?? '')?>' required style='width:100%;padding:12px;margin:8px 0'>
<input name='color' placeholder='Color (opcional)' value='<?=htmlspecialchars($ticket['color'] ?? '')?>' style='width:100%;padding:12px;margin:8px 0'>
<label style='display:block;margin:8px 0'><input type='checkbox' name='activo' value='1' <?=isset($ticket['activo']) && $ticket['activo'] ? 'checked' : ''?>> Activo</label>
<button type='submit'><?=isset($ticket['id']) ? 'Actualizar' : 'Crear'?></button>
</form>
<table border='1' cellpadding='8' style='width:100%'>
<tr><th>ID</th><th>Nombre</th><th>Precio</th><th>Color</th><th>Activo</th><th>Acciones</th></tr>
<?php if (!$ticketTypes): ?>
<tr><td colspan='6'>No hay tipos de tickets cargados.</td></tr>
<?php else: foreach ($ticketTypes as $type): ?>
<tr>
<td><?=htmlspecialchars($type['id'])?></td>
<td><?=htmlspecialchars($type['nombre'])?></td>
<td><?=htmlspecialchars($type['precio'])?></td>
<td><?=htmlspecialchars($type['color'] ?? '')?></td>
<td><?= $type['activo'] ? 'Sí' : 'No' ?></td>
<td><div class='actions'>
<a href='tickets.php?evento=<?=$eventoId?>&edit=<?=$type['id']?>'><button>✏️</button></a>
<a href='tickets.php?evento=<?=$eventoId?>&delete=<?=$type['id']?>' onclick="return confirm('¿Eliminar tipo de ticket?')"><button>🗑️</button></a>
</div></td>
</tr>
<?php endforeach; endif; ?>
</table>
<?php endif; ?>
</div></body></html>