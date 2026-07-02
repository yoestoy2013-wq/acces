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
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Eventos</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app">
        <h1>EVENTOS</h1>
        <div class="toolbar">
            <input type="text" name="q" form="busca" value="<?php echo htmlspecialchars($q);?>" placeholder="Buscar evento...">
            <button form="busca" type="submit">Buscar</button>
            <a href="nuevo_evento.php"><button type="button">+ Nuevo</button></a>
        </div>
        <form id="busca" method="get"></form>
        <table border="1" cellpadding="8">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Fecha</th>
                    <th>Lugar</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if(!$ev){ ?>
                <tr><td colspan="5" data-label="Resultado">No hay eventos cargados.</td></tr>
                <?php } else {
                foreach($ev as $e){ ?>
                <tr>
                    <td data-label="ID"><?=$e['id']?></td>
                    <td data-label="Nombre"><?=$e['nombre']?></td>
                    <td data-label="Fecha"><?=$e['fecha']?></td>
                    <td data-label="Lugar"><?=$e['lugar']?></td>
                    <td data-label="Acciones">
                        <div class="actions">
                            <a href="nuevo_evento.php?edit=<?=$e['id']?>"><button>✏️</button></a>
                            <a href="eventos.php?delete=<?=$e['id']?>" onclick="return confirm('¿Eliminar evento?')"><button>🗑️</button></a>
                            <a href='invitados.php?evento=<?=$e['id']?>'><button>👥</button></a>
                            <a href='tickets.php?evento=<?=$e['id']?>'><button>🎟️</button></a>
                            <a href='checkin.php?evento=<?=$e['id']?>'><button>✅</button></a>
                            <a href='colaboradores.php?evento=<?=$e['id']?>'><button>💼</button></a>
                        </div>
                    </td>
                </tr>
                <?php }} ?>
            </tbody>
        </table>
        <p><a href="index.php">← Volver</a></p>
    </div>
</body>
</html>