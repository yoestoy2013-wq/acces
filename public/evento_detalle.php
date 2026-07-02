<?php
require_once __DIR__ . '/../models/Evento.php';

$eventoModel = new Evento();

if (!isset($_GET['id'])) {
    header('Location: eventos.php');
    exit;
}

$id = (int)$_GET['id'];
$evento = $eventoModel->find($id);

if (!$evento) {
    header('Location: eventos.php');
    exit;
}

if (isset($_GET['delete'])) {
    if ($eventoModel->delete($id)) {
        header('Location: eventos.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Detalle del Evento</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="app">
        <h1><?=htmlspecialchars($evento['nombre'])?></h1>
        
        <div class="event-details">
            <p><strong>Fecha:</strong> <?=$evento['fecha']?></p>
            <p><strong>Lugar:</strong> <?=htmlspecialchars($evento['lugar'])?></p>
        </div>

        <div class="event-actions">
            <h2>Gestión del Evento</h2>
            
            <a href='invitados.php?evento=<?=$id?>'><button>👥 Invitados</button></a>
            <a href='tickets.php?evento=<?=$id?>'><button>🎟️ Tipos de Ticket</button></a>
            <a href='colaboradores.php?evento=<?=$id?>'><button>💼 Colaboradores</button></a>
            <a href='checkin.php?evento=<?=$id?>'><button>✅ Check-in</button></a>
            <button disabled>📊 Estadísticas</button>
            
            <h2 style="margin-top: 32px;">Opciones del Evento</h2>
            
            <a href="nuevo_evento.php?edit=<?=$id?>"><button>✏️ Editar Evento</button></a>
            <a href="evento_detalle.php?id=<?=$id?>&delete=1" onclick="return confirm('¿Está seguro de que desea eliminar este evento? Esta acción no se puede deshacer.');"><button style="background: #cc3333; margin-top: 16px;">🗑️ Eliminar Evento</button></a>
        </div>

        <p style="margin-top: 32px;"><a href="eventos.php">← Volver a Eventos</a></p>
    </div>
</body>
</html>
