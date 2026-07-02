<?php
require_once __DIR__ . '/../models/Evento.php';

$eventoModel = new Evento();
$edit = null;
if (isset($_GET['edit'])) {
    $edit = $eventoModel->find((int)$_GET['edit']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'nombre' => $_POST['nombre'],
        'fecha' => $_POST['fecha'],
        'lugar' => $_POST['lugar'],
    ];

    if (isset($_POST['id']) && $_POST['id'] !== '') {
        $eventoModel->update((int)$_POST['id'], $data);
    } else {
        $eventoModel->create($data);
    }

    header('Location: eventos.php');
    exit;
}
?>
<!doctype html>
<html>
<head>
    <meta charset='utf-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <link rel='stylesheet' href='assets/css/style.css'>
    <title><?php echo isset($edit) ? 'Editar Evento' : 'Nuevo Evento'; ?></title>
</head>
<body>
    <div class='app'>
        <h1><?php echo isset($edit) ? 'EDITAR EVENTO' : 'NUEVO EVENTO'; ?></h1>
        <form method='post'>
            <input type='hidden' name='id' value='<?php echo $edit['id']??'';?>'>
            
            <label for='nombre'>Nombre del evento *</label>
            <input id='nombre' name='nombre' type='text' placeholder='Ingresa el nombre del evento' value='<?php echo htmlspecialchars($edit['nombre']??'');?>' required>
            
            <label for='fecha'>Fecha del evento *</label>
            <input id='fecha' name='fecha' type='date' value='<?php echo $edit['fecha']??'';?>' required>
            
            <label for='lugar'>Lugar *</label>
            <input id='lugar' name='lugar' type='text' placeholder='Ingresa el lugar del evento' value='<?php echo htmlspecialchars($edit['lugar']??'');?>' required>
            
            <button type='submit'>GUARDAR</button>
        </form>
        <button onclick="history.back()">← VOLVER</button>
    </div>
</body>
</html>