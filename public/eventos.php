<?php
require_once __DIR__ . '/../models/Evento.php';

$eventoModel = new Evento();
$q = isset($_GET['q']) ? trim($_GET['q']) : '';
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $eventoModel->delete($id);
    header('Location: eventos.php');
    exit;
}
$ev = $eventoModel->all($q);
?>
<!DOCTYPE html><html><head><meta charset="utf-8"><title>Eventos</title>
<link rel="stylesheet" href="assets/css/style.css"></head><body>
<div class="app" style="max-width:900px;text-align:left">
<h1>EVENTOS</h1>
<div class="toolbar">
<input type="text" name="q" form="busca" value="<?php echo htmlspecialchars($q);?>" placeholder="Buscar evento...">
<button form="busca" type="submit">Buscar</button>
<a href="nuevo_evento.php"><button type="button">+ Nuevo</button></a>
</div>
<form id="busca" method="get"></form>
<table border="1" cellpadding="8" style="width:100%">
<tr><th>ID</th><th>Nombre</th><th>Fecha</th><th>Lugar</th><th>Acciones</th></tr>

<?php if(!$ev){ ?>
<tr><td colspan="5">No hay eventos cargados.</td></tr>
<?php } else {
foreach($ev as $e){ ?>
<tr>
<td><?=$e['id']?></td><td><?=$e['nombre']?></td><td><?=$e['fecha']?></td><td><?=$e['lugar']?></td>
<td><div class="actions"><a href="nuevo_evento.php?edit=<?=$e['id']?>"><button>✏️</button></a><a href="eventos.php?delete=<?=$e['id']?>" onclick="return confirm('¿Eliminar evento?')"><button>🗑️</button></a><a href='invitados.php?evento=<?=$e['id']?>'><button>👥</button></a><a href='tickets.php?evento=<?=$e['id']?>'><button>🎟️</button></a><a href='checkin.php?evento=<?=$e['id']?>'><button>✅</button></a></div></td>
</tr>
<?php }} ?>
</table>
<p><a href="index.php">Volver</a></p>
</div></body></html>