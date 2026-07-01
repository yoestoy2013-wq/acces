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
?><!doctype html><html><head><meta charset='utf-8'><link rel='stylesheet' href='assets/css/style.css'><title>Nuevo Evento</title></head><body><div class='app'><h1>Nuevo Evento</h1><form method='post'><input type='hidden' name='id' value='<?php echo $edit['id']??'';?>'>
<input name='nombre' placeholder='Nombre del evento' value='<?php echo htmlspecialchars($edit['nombre']??'');?>' required style='width:100%;padding:12px;margin:8px 0'>
<input name='fecha' type='date' value='<?php echo $edit['fecha']??'';?>' required style='width:100%;padding:12px;margin:8px 0'>
<input name='lugar' placeholder='Lugar' value='<?php echo htmlspecialchars($edit['lugar']??'');?>' required style='width:100%;padding:12px;margin:8px 0'>
<button type='submit'>GUARDAR</button></form><button onclick="history.back()">VOLVER</button></div></body></html>