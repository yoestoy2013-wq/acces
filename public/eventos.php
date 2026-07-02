<?php
require_once __DIR__ . '/../models/Evento.php';

$eventoModel = new Evento();
$ev = $eventoModel->all();
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
        
        <div class="toolbar-simple">
            <a href="nuevo_evento.php"><button>+ Nuevo Evento</button></a>
        </div>

        <div class="events-grid">
            <?php if(!$ev){ ?>
                <p style="text-align: center; grid-column: 1 / -1;">No hay eventos cargados.</p>
            <?php } else {
                foreach($ev as $e){ ?>
                    <a href="evento_detalle.php?id=<?=$e['id']?>" class="event-card">
                        <div class="event-card-content">
                            <h3><?=htmlspecialchars($e['nombre'])?></h3>
                            <p class="event-lugar">📍 <?=htmlspecialchars($e['lugar'])?></p>
                        </div>
                    </a>
                <?php }} ?>
        </div>

        <p style="margin-top: 32px;"><a href="index.php">← Volver</a></p>
    </div>
</body>
</html>