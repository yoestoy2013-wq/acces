<!doctype html><html><head><meta charset='utf-8'><title>Tickets</title>
<link rel='stylesheet' href='../assets/css/style.css'></head><body><div class='app'>
<h1>Tipos de Ticket</h1>
<p>Evento: <?=htmlspecialchars($_GET['evento']??'')?></p>
<form><input placeholder='Nombre del ticket'><br><br>
<input placeholder='Valor en pesos'><br><br>
<button>Guardar (próximamente)</button></form>
<p><a href='eventos.php'>Volver</a></p></div></body></html>