<?php
$f='eventos.json';
$ev=file_exists($f)?json_decode(file_get_contents($f),true):[];
if(isset($_GET['delete'])){
$id=(int)$_GET['delete'];
$ev=array_values(array_filter($ev,fn($x)=>$x['id']!=$id));
foreach($ev as $i=>&$r){$r['id']=$i+1;}
file_put_contents($f,json_encode($ev));
header('Location: eventos.php');exit;
}
$q=isset($_GET['q'])?trim($_GET['q']):'';
$q=isset($_GET['q'])?trim($_GET['q']):'';
if($q!==''){
 $ev=array_values(array_filter($ev,function($e)use($q){
   return stripos($e['nombre'],$q)!==false||stripos($e['lugar'],$q)!==false;
 }));
}
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
<td><div class="actions"><a href="nuevo_evento.php?edit=<?=$e['id']?>"><button>✏️</button></a><a href="eventos.php?delete=<?=$e['id']?>" onclick="return confirm('¿Eliminar evento?')"><button>🗑️</button></a><a href='invitados.php?evento=<?=$e['id']?>'><button>👥</button></a><a href='tickets.php?evento=<?=$e['id']?>'><button>🎟️</button></a></div></td>
</tr>
<?php }} ?>
</table>
<p><a href="index.php">Volver</a></p>
</div></body></html>