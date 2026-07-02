<?php
require_once __DIR__ . '/../controllers/ColaboradorController.php';
require_once __DIR__ . '/../models/Evento.php';

$eventoId = isset($_GET['evento']) ? (int)$_GET['evento'] : 0;
$eventoModel = new Evento();
$colaboradorController = new ColaboradorController();
$evento = null;
$errors = [];
$success = '';
$colaborador = null;

if ($eventoId > 0) {
    $evento = $eventoModel->find($eventoId);
    if (!$evento) {
        $errors[] = 'Evento no encontrado.';
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evento) {
    $formData = [
        'nombre' => trim($_POST['nombre'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
        'observaciones' => trim($_POST['observaciones'] ?? ''),
        'activo' => isset($_POST['activo']) ? 1 : 0,
    ];

    $errors = $colaboradorController->validateData($formData);
    if (empty($errors)) {
        try {
            if (isset($_POST['id']) && $_POST['id'] !== '') {
                $colaboradorId = (int)$_POST['id'];
                if ($colaboradorController->update($colaboradorId, $formData)) {
                    header('Location: colaboradores.php?evento=' . $eventoId);
                    exit;
                }
                $errors[] = 'No se pudo actualizar el colaborador.';
            } else {
                $colaboradorController->create($eventoId, $formData);
                header('Location: colaboradores.php?evento=' . $eventoId);
                exit;
            }
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}

if (isset($_GET['delete']) && $evento) {
    $colaboradorId = (int)$_GET['delete'];
    try {
        if ($colaboradorController->delete($colaboradorId)) {
            header('Location: colaboradores.php?evento=' . $eventoId);
            exit;
        }
    } catch (Throwable $e) {
        $errors[] = $e->getMessage();
    }
}

if (isset($_GET['edit']) && $evento) {
    $colaborador = $colaboradorController->find((int)$_GET['edit']);
    if (!$colaborador) {
        $errors[] = 'Colaborador no encontrado.';
    }
}

$colaboradores = $evento ? $colaboradorController->list($eventoId) : [];
?>
<!doctype html><html><head><meta charset='utf-8'><title>Colaboradores</title>
<link rel='stylesheet' href='assets/css/style.css'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head><body><div class='app' style='max-width:900px;text-align:left'>
<button class="back-button" onclick="history.back();" title="Volver">
    <i class="fas fa-chevron-left"></i>
</button>
<h1>COLABORADORES</h1>
<?php if ($evento): ?>
<p>Evento: <?=htmlspecialchars($evento['nombre'])?></p>
<?php else: ?>
<p style='color:#d9534f'>No hay evento seleccionado. Selecciona un evento para ver sus colaboradores.</p>
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
<input type='hidden' name='id' value='<?=htmlspecialchars($colaborador['id'] ?? '')?>'>
<label style='display:block;margin:8px 0'>Nombre (obligatorio)</label>
<input name='nombre' placeholder='Nombre' value='<?=htmlspecialchars($colaborador['nombre'] ?? '')?>' required style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'>
<label style='display:block;margin:8px 0'>Teléfono (opcional)</label>
<input name='telefono' placeholder='Teléfono' value='<?=htmlspecialchars($colaborador['telefono'] ?? '')?>' style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'>
<label style='display:block;margin:8px 0'>Observaciones (opcional)</label>
<textarea name='observaciones' placeholder='Observaciones' style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'><?=htmlspecialchars($colaborador['observaciones'] ?? '')?></textarea>
<label style='display:block;margin:8px 0'>
<input type='checkbox' name='activo' <?=isset($colaborador['activo']) && $colaborador['activo'] ? 'checked' : ''?>>
Activo
</label>
<button type='submit' style='padding:12px 24px;margin-top:12px'><?=isset($colaborador['id']) ? 'Actualizar' : 'Crear'?></button>
</form>
<table border='1' cellpadding='8' style='width:100%'>
<tr><th>ID</th><th>Nombre</th><th>Teléfono</th><th>Invitados</th><th>Estado</th><th>Acciones</th></tr>
<?php if (!$colaboradores): ?>
<tr><td colspan='6'>No hay colaboradores cargados.</td></tr>
<?php else: foreach ($colaboradores as $col): ?>
<tr>
<td><?=htmlspecialchars($col['id'])?></td>
<td><?=htmlspecialchars($col['nombre'])?></td>
<td><?=htmlspecialchars($col['telefono'] ?? '-')?></td>
<td><?=$colaboradorController->countInvitados($col['id'])?></td>
<td><?=$col['activo'] ? 'Activo' : 'Inactivo'?></td>
<td><div class='actions'>
<a href='colaboradores.php?evento=<?=$eventoId?>&edit=<?=$col['id']?>'><button>✏️</button></a>
<a href='colaboradores.php?evento=<?=$eventoId?>&delete=<?=$col['id']?>' onclick="return confirm('¿Eliminar colaborador?')"<?=$colaboradorController->countInvitados($col['id']) > 0 ? ' style="opacity:0.5;cursor:not-allowed" onclick="alert(\'No se puede eliminar este colaborador porque tiene invitados asociados.\'); return false;"' : ''?>><button><?=$colaboradorController->countInvitados($col['id']) > 0 ? '🚫' : '🗑️'?></button></a>
</div></td>
</tr>
<?php endforeach; endif; ?>
</table>
<?php endif; ?>
</div></body></html>
