<?php
$file='eventos.json';
$data=file_exists($file)?json_decode(file_get_contents($file),true):[];
$edit=null;
if(isset($_GET['edit'])){foreach($data as $d){if($d['id']==(int)$_GET['edit'])$edit=$d;}}
if($_SERVER['REQUEST_METHOD']==='POST'){
$file='eventos.json';
$data=file_exists($file)?json_decode(file_get_contents($file),true):[];
if(isset($_POST['id']) && $_POST['id']!==''){
foreach($data as &$r){if($r['id']==(int)$_POST['id']){$r['nombre']=$_POST['nombre'];$r['fecha']=$_POST['fecha'];$r['lugar']=$_POST['lugar'];}}
}else{$data[]=['id'=>count($data)+1,'nombre'=>$_POST['nombre'],'fecha'=>$_POST['fecha'],'lugar'=>$_POST['lugar']];}
file_put_contents($file,json_encode($data));
header('Location: eventos.php');exit;
}
?><!doctype html><html><head><meta charset='utf-8'><link rel='stylesheet' href='assets/css/style.css'><title>Nuevo Evento</title></head><body><div class='app'><h1>Nuevo Evento</h1><form method='post'><input type='hidden' name='id' value='<?php echo $edit['id']??'';?>'>
<input name='nombre' placeholder='Nombre del evento' value='<?php echo htmlspecialchars($edit['nombre']??'');?>' required style='width:100%;padding:12px;margin:8px 0'>
<input name='fecha' type='date' value='<?php echo $edit['fecha']??'';?>' required style='width:100%;padding:12px;margin:8px 0'>
<input name='lugar' placeholder='Lugar' value='<?php echo htmlspecialchars($edit['lugar']??'');?>' required style='width:100%;padding:12px;margin:8px 0'>
<button type='submit'>GUARDAR</button></form><button onclick="history.back()">VOLVER</button></div></body></html>