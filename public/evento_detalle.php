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
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app">
        <div class="event-header">
            <button class="back-button" onclick="history.back();" title="Volver">
                <i class="fas fa-chevron-left"></i>
            </button>
            <h1><?=htmlspecialchars($evento['nombre'])?></h1>
            <button class="home-button" onclick="location.href='index.php';" title="Inicio">
                <i class="fas fa-home"></i>
            </button>
        </div>
        <p class="event-meta"><?=$evento['fecha']?> · <?=htmlspecialchars($evento['lugar'])?></p>

        <div class="event-actions">
            <h2>Gestión del Evento</h2>
            
            <a href='invitados.php?evento=<?=$id?>'><button class="action-button"><i class="fas fa-users"></i> Invitados</button></a>
            <a href='tickets.php?evento=<?=$id?>'><button class="action-button"><i class="fas fa-ticket"></i> Tipos de Ticket</button></a>
            <a href='colaboradores.php?evento=<?=$id?>'><button class="action-button"><i class="fas fa-briefcase"></i> Colaboradores</button></a>
            <a href='checkin.php?evento=<?=$id?>'><button class="action-button"><i class="fas fa-check-circle"></i> Check-in</button></a>
            <button class="action-button" disabled><i class="fas fa-chart-bar"></i> Estadísticas</button>
            
            <h2 style="margin-top: 24px;">Opciones del Evento</h2>
            
            <a href="nuevo_evento.php?edit=<?=$id?>"><button class="action-button"><i class="fas fa-edit"></i> Editar Evento</button></a>
            <a href="evento_detalle.php?id=<?=$id?>&delete=1" onclick="return confirm('¿Está seguro de que desea eliminar este evento? Esta acción no se puede deshacer.');"><button class="action-button delete-button"><i class="fas fa-trash"></i> Eliminar Evento</button></a>
        </div>
    </div>
</body>
</html>
