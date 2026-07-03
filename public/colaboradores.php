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
<!doctype html><html><head><meta charset='utf-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>Colaboradores</title>
<link rel='stylesheet' href='assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head><body><div class='app'>
<div class="header-container">
    <button class="back-button" onclick="history.back();" title="Volver">
        <i class="fas fa-chevron-left"></i>
    </button>
    <h1>COLABORADORES</h1>
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
<?php if ($evento): ?>

<!-- Lista de Colaboradores -->
<div class='actions-container'>
<div style='width:350px;max-width:100%;display:flex;flex-direction:column;gap:2px'>
<?php if (!$colaboradores): ?>
<div style='text-align:center;padding:16px'>No hay colaboradores cargados.</div>
<?php else: foreach ($colaboradores as $col): ?>
<button onclick="toggleColaborador(this)" data-col-id="<?=$col['id']?>" class='action-button' style='width:100%;justify-content:space-between;padding:0 16px;text-align:left'>
<span style='font-weight:600;font-size:16px;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap'><?=htmlspecialchars($col['nombre'])?></span>
<span style='background:#FF6A00;color:#1e1e1e;font-size:12px;padding:3px 10px;border-radius:6px;font-weight:bold;white-space:nowrap;margin-left:8px'><?=$colaboradorController->countInvitados($col['id'])?> invitados</span>
</button>
<div class='col-actions' style='display:none;gap:4px;flex-direction:column;width:350px;max-width:100%;margin-bottom:4px'>
<a href='colaboradores.php?evento=<?=$eventoId?>&edit=<?=$col['id']?>&expand=<?=$col['id']?>' style='text-decoration:none'><button class='action-button' style='width:100%'>Editar</button></a>
<a href='colaborador_detalle.php?evento=<?=$eventoId?>&id=<?=$col['id']?>' style='text-decoration:none'><button class='action-button' style='width:100%'>Ver detalles</button></a>
<a href='colaboradores.php?evento=<?=$eventoId?>&delete=<?=$col['id']?>&expand=<?=$col['id']?>' onclick="return confirm('¿Eliminar colaborador?')" style='text-decoration:none'><button class='action-button delete-button' style='width:100%'>Eliminar</button></a>
</div>
<?php endforeach; endif; ?>
</div>
</div>

<script>
let activeCol = null;

function toggleColaborador(button) {
  const actions = button.nextElementSibling;
  if (activeCol && activeCol !== button) {
    activeCol.nextElementSibling.style.display = 'none';
    activeCol.style.background = '#2a2a2a';
  }
  if (actions.style.display === 'none') {
    actions.style.display = 'flex';
    button.style.background = '#444';
    activeCol = button;
  } else {
    actions.style.display = 'none';
    button.style.background = '#2a2a2a';
    activeCol = null;
    // If an edit form is open, close it by removing edit/expand params
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('edit')) {
      urlParams.delete('edit');
      urlParams.delete('expand');
      window.location.search = urlParams.toString();
    }
  }
}

// Auto-expand colaborador if expand parameter exists
window.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const expandId = urlParams.get('expand');
  if (expandId) {
    const colButton = document.querySelector(`button[data-col-id="${expandId}"]`);
    if (colButton) {
      toggleColaborador(colButton);
    }
  }
});
</script>

<!-- Botón Crear o Formulario -->
<?php if (!isset($_GET['form']) && !isset($_GET['edit'])): ?>
<!-- Mostrar botón Crear -->
<div class='actions-container'>
<a href='colaboradores.php?evento=<?=$eventoId?>&form=1' style='text-decoration:none'>
<button class='action-button' style='background:#FF6A00;border-color:#FF6A00'><i class="fas fa-plus"></i> Crear Colaborador</button>
</a>
</div>
<?php else: ?>
<!-- Mostrar formulario -->
<div class='actions-container'>
<form method='post' style='width:350px;max-width:100%'>
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
<div style='display:flex;gap:6px;width:100%;margin-top:12px'>
<button type='submit' style='flex:1;padding:14px;background:#4caf50;color:#fff;border:1px solid #388e3c;border-radius:12px;cursor:pointer;font-weight:600;font-size:16px'><?=isset($colaborador['id']) ? 'Actualizar' : 'Crear'?></button>
<a href='colaboradores.php?evento=<?=$eventoId?>' style='flex:1;text-decoration:none'>
<button type='button' style='width:100%;padding:14px;background:#555;color:#fff;border:1px solid #666;border-radius:12px;cursor:pointer;font-weight:600;font-size:16px'>Cancelar</button>
</a>
</div>
</form>
</div>
<?php endif; ?>

<?php endif; ?>
</div></body></html>
