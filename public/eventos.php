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
    <link rel="stylesheet" href="assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="app">
        <h1>
            <button class="back-button" onclick="history.back();" title="Volver">
                <i class="fas fa-chevron-left"></i>
            </button>
            EVENTOS
        </h1>
        
        <div class="toolbar-simple">
            <a href="nuevo_evento.php"><button class="new-event-btn"><i class="fas fa-plus"></i> Nuevo Evento</button></a>
        </div>

        <div class="events-container">
            <?php if(!$ev){ ?>
                <p style="text-align: center; margin-top: 32px;">No hay eventos cargados.</p>
            <?php } else {
                foreach($ev as $e){ ?>
                    <a href="evento_detalle.php?id=<?=$e['id']?>" class="event-button">
                        <i class="fas fa-calendar"></i>
                        <?=htmlspecialchars($e['nombre'])?>
                    </a>
                <?php }} ?>
        </div>
    </div>
</body>
</html>