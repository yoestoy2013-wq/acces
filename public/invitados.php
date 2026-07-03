<?php
require_once __DIR__ . '/../controllers/InvitadoController.php';
require_once __DIR__ . '/../models/Evento.php';

$eventoId = isset($_GET['evento']) ? (int)$_GET['evento'] : 0;
$expandId = isset($_GET['expand']) ? (int)$_GET['expand'] : null;
$filterType = isset($_GET['filter_type']) ? trim($_GET['filter_type']) : null;
$filterValue = isset($_GET['filter_value']) ? trim($_GET['filter_value']) : null;
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
                    $redirectUrl = 'invitados.php?evento=' . $eventoId;
                    if ($expandId) $redirectUrl .= '&expand=' . $expandId;
                    header('Location: ' . $redirectUrl);
                    exit;
                }
                $errors[] = 'No se pudo actualizar el invitado.';
            } else {
                $invitadoController->create($eventoId, $formData);
                $redirectUrl = 'invitados.php?evento=' . $eventoId;
                if ($expandId) $redirectUrl .= '&expand=' . $expandId;
                header('Location: ' . $redirectUrl);
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

$invitados = $evento ? ($filterType && $filterValue ? $invitadoController->filterByEvento($eventoId, $filterType, $filterValue) : $invitadoController->listByEvento($eventoId)) : [];
?>
<!doctype html><html><head><meta charset='utf-8'><title>Invitados</title>
<link rel='stylesheet' href='assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>'>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></head><body><div class='app'>

<div class="header-container">
    <button class="back-button" onclick="history.back();" title="Volver">
        <i class="fas fa-chevron-left"></i>
    </button>
    <h1>INVITADOS</h1>
    <button class="home-button" onclick="location.href='index.php';" title="Inicio">
        <i class="fas fa-home"></i>
    </button>
</div>

<?php if ($evento): ?>
<p class="event-meta"><?=htmlspecialchars($evento['nombre'])?></p>

<!-- Filtro de Invitados -->
<div style='max-width:420px;margin:6px auto;display:flex;flex-direction:column;align-items:center'>
<?php if (!isset($_GET['filter'])): ?>
<a href='invitados.php?evento=<?=$eventoId?>&filter=1' style='text-decoration:none;width:100%'>
<button style='width:350px;padding:12px 24px;background:#6A5AFF;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold;font-size:14px'><i class="fas fa-filter"></i> Filtrar Invitados</button>
</a>
</div>
<?php else: ?>
<!-- Formulario de Filtro -->
<div style='max-width:420px;margin:6px auto;display:flex;flex-direction:column;align-items:center'>
<form method='get' id='filter_form' style='width:100%;max-width:350px' onsubmit="return validateFilterForm()">
<input type='hidden' name='evento' value='<?=$eventoId?>'>
<input type='hidden' name='filter_value' id='filter_value_hidden' value=''>
<label style='display:block;margin:8px 0;font-weight:bold'>Filtrar por:</label>
<select name='filter_type' id='filter_type' onchange="updateFilterUI()" required style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'>
<option value=''>-- Selecciona un tipo de filtro --</option>
<option value='nombre'>Nombre</option>
<option value='ticket'>Tipo de Ticket</option>
<option value='pendiente'>Pendiente (sin ingresar)</option>
<option value='ingresado'>Ingresado</option>
<option value='colaborador'>Colaborador</option>
</select>

<!-- Filtro: Nombre -->
<div id='filter_nombre' style='display:none;width:100%'>
<label style='display:block;margin:8px 0'>Buscar por nombre:</label>
<input type='text' id='nombre_input' placeholder='Ingresa el nombre' style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'>
</div>

<!-- Filtro: Ticket -->
<div id='filter_ticket' style='display:none;width:100%'>
<label style='display:block;margin:8px 0'>Selecciona tipo de ticket:</label>
<select id='filter_value_ticket' style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'>
<option value=''>-- Selecciona un ticket --</option>
<?php foreach ($ticketTypes as $type): ?>
<option value='<?=htmlspecialchars($type['id'])?>'><?=htmlspecialchars($type['nombre'])?> (<?=htmlspecialchars($type['precio'])?>)</option>
<?php endforeach; ?>
</select>
</div>

<!-- Filtro: Colaborador -->
<div id='filter_colaborador' style='display:none;width:100%'>
<label style='display:block;margin:8px 0'>Selecciona colaborador:</label>
<select id='filter_value_colaborador' style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box'>
<option value=''>-- Selecciona un colaborador --</option>
<?php foreach ($colaboradores as $col): ?>
<option value='<?=htmlspecialchars($col['id'])?>'><?=htmlspecialchars($col['nombre'])?></option>
<?php endforeach; ?>
</select>
</div>

<div style='display:flex;gap:6px;width:100%;margin-top:8px'>
<button type='submit' style='flex:1;padding:12px;background:#6A5AFF;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold'>Aplicar Filtro</button>
<a href='invitados.php?evento=<?=$eventoId?>' style='flex:1;text-decoration:none'>
<button type='button' style='width:100%;padding:12px;background:#999;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold'>Cancelar</button>
</a>
</div>
</form>
</div>
<?php endif; ?>

<?php endif; ?>
<?php if ($errors): ?>
<div class='errors' style='background:#ffe6e6;padding:12px;margin:12px 0;border:1px solid #ffb3b3;'>
<?php foreach ($errors as $error): ?>
<p><?=htmlspecialchars($error)?></p>
<?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($evento): ?>

<!-- Lista de Invitados -->
<div class='invitados-container' style='max-width:420px;margin:24px auto;display:flex;flex-direction:column;align-items:center'>
<div style='width:100%;max-width:350px;display:flex;flex-direction:column;gap:2px;max-height:400px;overflow-y:auto'>
<?php if (!$invitados): ?>
<div style='text-align:center;padding:16px'>No hay invitados cargados.</div>
<?php else: foreach ($invitados as $guest): ?>
<button onclick="toggleGuest(this)" data-guest-id="<?=$guest['id']?>" style='background:#2a2a2a;border:1px solid #555;color:#fff;cursor:pointer;padding:8px;border-radius:4px;text-align:left;display:flex;justify-content:space-between;align-items:center;gap:6px;transition:all 0.2s'>
<span style='font-weight:bold;font-size:14px;flex:1'><?=htmlspecialchars($guest['nombre'])?></span>
<span style='background:#FF6A00;color:#1e1e1e;font-size:10px;padding:3px 8px;border-radius:3px;font-weight:bold;white-space:nowrap'><?=htmlspecialchars($ticketTypeMap[$guest['ticket_type_id']] ?? $guest['ticket_type_id'])?></span>
</button>
<div class='guest-actions' style='display:none;gap:3px;flex-direction:column;margin-bottom:2px'>
<a href='invitados.php?evento=<?=$eventoId?>&edit=<?=$guest['id']?>&expand=<?=$guest['id']?>'><button style='background:#444;border:1px solid #666;color:#fff;cursor:pointer;font-size:11px;padding:4px 8px;border-radius:3px;width:100%'>Editar</button></a>
<a href='entrada_digital.php?id=<?=$guest['id']?>' target='_blank'><button style='background:#444;border:1px solid #666;color:#fff;cursor:pointer;font-size:11px;padding:4px 8px;border-radius:3px;width:100%'>Entrada</button></a>
<a href='invitados.php?evento=<?=$eventoId?>&delete=<?=$guest['id']?>&expand=<?=$guest['id']?>' onclick="return confirm('¿Eliminar invitado?')"><button style='background:#4a2a2a;border:1px solid #664444;color:#ff8888;cursor:pointer;font-size:11px;padding:4px 8px;border-radius:3px;width:100%'>Eliminar</button></a>
</div>
<?php endforeach; endif; ?>
</div>
</div>

<script>
let activeGuest = null;

function toggleGuest(button) {
  const actions = button.nextElementSibling;
  if (activeGuest && activeGuest !== button) {
    activeGuest.nextElementSibling.style.display = 'none';
    activeGuest.style.background = '#2a2a2a';
  }
  if (actions.style.display === 'none') {
    actions.style.display = 'flex';
    button.style.background = '#444';
    activeGuest = button;
  } else {
    actions.style.display = 'none';
    button.style.background = '#2a2a2a';
    activeGuest = null;
  }
}

// Auto-expand guest if expand parameter exists
window.addEventListener('DOMContentLoaded', function() {
  const urlParams = new URLSearchParams(window.location.search);
  const expandId = urlParams.get('expand');
  if (expandId) {
    const guestButton = document.querySelector(`button[data-guest-id="${expandId}"]`);
    if (guestButton) {
      toggleGuest(guestButton);
    }
  }
  
  // Initialize filter UI
  const filterType = document.getElementById('filter_type');
  if (filterType) {
    updateFilterUI();
  }
});

function updateFilterUI() {
  const filterType = document.getElementById('filter_type').value;
  
  // Hide all filter UIs
  document.getElementById('filter_nombre').style.display = 'none';
  document.getElementById('filter_ticket').style.display = 'none';
  document.getElementById('filter_colaborador').style.display = 'none';
  
  // Show selected filter UI or auto-submit
  if (filterType === 'nombre') {
    document.getElementById('filter_nombre').style.display = 'block';
    document.getElementById('nombre_input').focus();
  } else if (filterType === 'ticket') {
    document.getElementById('filter_ticket').style.display = 'block';
  } else if (filterType === 'colaborador') {
    document.getElementById('filter_colaborador').style.display = 'block';
  } else if (filterType === 'pendiente' || filterType === 'ingresado') {
    // Auto-submit for status filters (they don't need filter_value input)
    document.getElementById('filter_value_hidden').value = filterType;
    setTimeout(() => document.getElementById('filter_form').submit(), 100);
  }
}

function validateFilterForm() {
  const filterType = document.getElementById('filter_type').value;
  
  if (!filterType) {
    alert('Selecciona un tipo de filtro');
    return false;
  }
  
  let filterValue = '';
  
  if (filterType === 'nombre') {
    filterValue = document.getElementById('nombre_input').value.trim();
    if (!filterValue) {
      alert('Ingresa un nombre para buscar');
      return false;
    }
  } else if (filterType === 'ticket') {
    filterValue = document.getElementById('filter_value_ticket').value;
    if (!filterValue) {
      alert('Selecciona un tipo de ticket');
      return false;
    }
  } else if (filterType === 'colaborador') {
    filterValue = document.getElementById('filter_value_colaborador').value;
    if (!filterValue) {
      alert('Selecciona un colaborador');
      return false;
    }
  } else if (filterType === 'pendiente' || filterType === 'ingresado') {
    filterValue = filterType;
  }
  
  document.getElementById('filter_value_hidden').value = filterValue;
  return true;
}
</script>
</div>

<!-- Botón Crear o Formulario -->
<?php if (!isset($_GET['form']) && !isset($_GET['edit'])): ?>
<!-- Mostrar botón Crear -->
<div style='max-width:420px;margin:6px auto;display:flex;flex-direction:column;align-items:center'>
<a href='invitados.php?evento=<?=$eventoId?>&form=1' style='text-decoration:none'>
<button style='width:350px;padding:12px 24px;background:#FF6A00;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold;font-size:16px'><i class="fas fa-plus"></i> Crear Invitado</button>
</a>
</div>
<?php else: ?>
<!-- Mostrar formulario -->
<div class='form-container' style='max-width:420px;margin:6px auto;display:flex;flex-direction:column;align-items:center'>
<form method='post' style='width:100%;max-width:350px'>
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
<textarea name='observaciones' placeholder='Observaciones' style='width:100%;padding:12px;margin:8px 0;box-sizing:border-box;height:32px;resize:none'><?=htmlspecialchars($invitado['observaciones'] ?? '')?></textarea>
<button type='submit' style='width:100%;padding:12px 24px;margin-top:4px;background:#FF6A00;color:#fff;border:none;border-radius:4px;cursor:pointer;font-weight:bold'><?=isset($invitado['id']) ? 'Actualizar' : 'Crear'?></button>
</form>
</div>
<?php endif; ?>
<?php endif; ?>
</div></body></html>