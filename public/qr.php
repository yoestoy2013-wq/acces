<?php

require_once __DIR__ . '/../controllers/InvitadoController.php';
require_once __DIR__ . '/../models/QRGenerator.php';
require_once __DIR__ . '/../models/Evento.php';
require_once __DIR__ . '/../models/TicketType.php';

$invitadoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$download = isset($_GET['download']) && $_GET['download'] === '1';
$controller = new InvitadoController();
$guest = $controller->find($invitadoId);

if (!$guest) {
    http_response_code(404);
    echo 'Invitado no encontrado.';
    exit;
}

$eventoModel = new Evento();
$ticketTypeModel = new TicketType();
$evento = $eventoModel->find($guest['evento_id']);
$ticketType = $ticketTypeModel->find($guest['ticket_type_id']);

if ($download) {
    $png = QRGenerator::generatePng($guest['uuid'], 6, 4);
    header('Content-Type: image/png');
    header('Content-Disposition: attachment; filename="qr_' . $guest['uuid'] . '.png"');
    echo $png;
    exit;
}

$qrDataUri = QRGenerator::generateDataUri($guest['uuid'], 6, 4);
$guestName = trim($guest['nombre'] . ' ' . $guest['apellido']);
$eventName = $evento['nombre'] ?? 'Evento';
$ticketTypeName = strtoupper($ticketType['nombre'] ?? 'GENERAL');
$shortUuid = strtoupper(substr(str_replace('-', '', $guest['uuid']), 0, 8));
$location = trim($evento['lugar'] ?? 'Sin dirección');
$dateLabel = 'Sin fecha';
$timeLabel = 'Sin hora';

if (!empty($evento['fecha'])) {
    try {
        $date = new DateTime($evento['fecha']);
        $dateLabel = $date->format('d/m/Y');
        $timeLabel = $date->format('H:i');
    } catch (Exception $e) {
    }
}

$logoText = 'VIP';
if (!empty($evento['nombre'])) {
    preg_match_all('/\p{L}/u', $evento['nombre'], $matches);
    if (!empty($matches[0])) {
        $initials = array_slice($matches[0], 0, 2);
        $logoText = mb_strtoupper(implode('', $initials));
    }
}

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function sanitizeFileName(string $value): string
{
    $result = preg_replace('/[^A-Za-z0-9_\-]/', '_', $value);
    return substr(preg_replace('/_+/', '_', $result), 0, 64);
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket VIP - <?=escapeHtml($guestName)?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
    <style>
        :root {
            color-scheme: dark;
            color: #ffffff;
            background: #090909;
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; font-family: 'Inter', sans-serif; background: linear-gradient(180deg, #080808 0%, #121212 100%); color: #ffffff; display: flex; align-items: center; justify-content: center; padding: 28px; }
        body, button { -webkit-font-smoothing: antialiased; }
        .page { width: 100%; max-width: 920px; }
        .ticket-card { background: #151515; border: 1px solid rgba(255, 106, 0, 0.16); border-radius: 32px; box-shadow: 0 32px 90px rgba(0, 0, 0, 0.40); overflow: hidden; }
        .ticket-header { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 32px; }
        .ticket-brand { display: inline-flex; align-items: center; justify-content: center; width: 78px; height: 78px; border-radius: 22px; background: linear-gradient(135deg, #ff6a00, #ff8d3a); color: #111; font-size: 26px; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; }
        .ticket-event { flex: 1; min-width: 0; }
        .ticket-event .event-label { display: inline-flex; align-items: center; justify-content: center; padding: 8px 16px; border-radius: 999px; background: rgba(255, 106, 0, 0.12); color: #ffb584; letter-spacing: 0.18em; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 12px; }
        .ticket-event h1 { margin: 0; font-size: clamp(2rem, 2.8vw, 3.4rem); line-height: 1.02; letter-spacing: -0.04em; }
        .ticket-type-chip { display: inline-flex; padding: 12px 22px; border-radius: 999px; background: rgba(255, 106, 0, 0.14); color: #ffb784; text-transform: uppercase; letter-spacing: 0.18em; font-weight: 700; font-size: 0.85rem; margin: 0 32px 0 32px; }
        .ticket-info-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; padding: 0 32px 24px; }
        .info-card { background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 24px; padding: 22px; min-height: 116px; }
        .info-icon { display: block; font-size: 26px; margin-bottom: 10px; }
        .info-label { display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.18em; color: #ffb783; margin-bottom: 6px; }
        .info-value { font-size: 1rem; line-height: 1.5; font-weight: 600; color: #f9f9f9; }
        .guest-block { margin: 0 32px 24px; padding: 26px 28px; border-radius: 28px; background: linear-gradient(180deg, rgba(255, 106, 0, 0.08), rgba(255, 255, 255, 0.02)); border: 1px solid rgba(255, 255, 255, 0.08); }
        .guest-title { margin: 0 0 8px; font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.18em; color: #ffb684; }
        .guest-name { margin: 0; font-size: clamp(1.8rem, 2.4vw, 2.6rem); font-weight: 800; line-height: 1.05; }
        .qr-section { padding: 0 32px 32px; }
        .qr-block { display: flex; flex-direction: column; align-items: center; gap: 20px; padding: 28px; border-radius: 32px; background: rgba(255, 255, 255, 0.04); border: 1px dashed rgba(255, 255, 255, 0.12); }
        .qr-block img { width: min(340px, 100%); aspect-ratio: 1; object-fit: contain; background: #ffffff; padding: 22px; border-radius: 28px; }
        .uuid-text { text-align: center; display: grid; gap: 6px; }
        .uuid-label { font-size: 0.80rem; text-transform: uppercase; letter-spacing: 0.16em; color: #ffb67a; }
        .uuid-value { font-size: 1.2rem; font-weight: 800; letter-spacing: 0.18em; color: #ffffff; }
        .ticket-footer { padding: 24px 32px 32px; border-top: 1px solid rgba(255, 255, 255, 0.08); text-align: center; color: rgba(255, 255, 255, 0.72); font-size: 0.95rem; }
        .ticket-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 16px; margin-top: 20px; }
        .ticket-actions button, .ticket-actions a.button-link { min-width: 190px; border: none; border-radius: 999px; padding: 16px 22px; color: #111; font-weight: 700; font-size: 0.98rem; letter-spacing: 0.02em; cursor: pointer; transition: transform 0.18s ease, box-shadow 0.18s ease, opacity 0.18s ease; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
        .ticket-actions button.primary, .ticket-actions a.button-link.primary { background: #ff6a00; }
        .ticket-actions button.secondary, .ticket-actions a.button-link.secondary { background: rgba(255, 255, 255, 0.08); color: #fff; border: 1px solid rgba(255, 255, 255, 0.12); }
        .ticket-actions button:hover, .ticket-actions a.button-link:hover { transform: translateY(-1px); box-shadow: 0 18px 28px rgba(255, 106, 0, 0.24); }
        .ticket-actions button:disabled { opacity: 0.65; cursor: not-allowed; }
        @media (max-width: 820px) {
            .ticket-header { flex-direction: column; align-items: flex-start; }
            .ticket-actions { flex-direction: column; }
            .ticket-info-grid { grid-template-columns: 1fr; }
            .ticket-type-chip { margin: 0 32px 0 32px; }
        }
        @media (max-width: 520px) {
            body { padding: 16px; }
            .ticket-header, .ticket-info-grid, .guest-block, .qr-section, .ticket-footer { padding-left: 20px; padding-right: 20px; }
            .ticket-logo { width: 64px; height: 64px; font-size: 22px; }
            .ticket-actions button, .ticket-actions a.button-link { width: 100%; min-width: 0; }
        }
        @media print {
            body { background: #151515; padding: 0; }
            .ticket-actions, .screen-only { display: none !important; }
            .ticket-card { box-shadow: none; border: none; margin: 0; }
        }
    </style>
</head>
<body>
<div class="page">
    <div class="ticket-card" id="ticket-card">
        <div class="ticket-header">
            <div class="ticket-brand"><?=escapeHtml($logoText)?></div>
            <div class="ticket-event">
                <div class="event-label">LOGO DEL EVENTO</div>
                <h1><?=escapeHtml($eventName)?></h1>
            </div>
        </div>
        <div class="ticket-type-chip"><?=escapeHtml($ticketTypeName)?></div>
        <div class="ticket-info-grid">
            <div class="info-card">
                <span class="info-icon">📅</span>
                <span class="info-label">Fecha</span>
                <span class="info-value"><?=escapeHtml($dateLabel)?></span>
            </div>
            <div class="info-card">
                <span class="info-icon">🕒</span>
                <span class="info-label">Hora</span>
                <span class="info-value"><?=escapeHtml($timeLabel)?></span>
            </div>
            <div class="info-card">
                <span class="info-icon">📍</span>
                <span class="info-label">Dirección</span>
                <span class="info-value"><?=escapeHtml($location)?></span>
            </div>
        </div>
        <div class="guest-block">
            <div class="guest-title">INVITADO</div>
            <div class="guest-name"><?=escapeHtml($guestName)?></div>
        </div>
        <div class="qr-section">
            <div class="qr-block">
                <img src="<?=escapeHtml($qrDataUri)?>" alt="Código QR de <?=escapeHtml($guestName)?>">
                <div class="uuid-text">
                    <span class="uuid-label">ID</span>
                    <span class="uuid-value"><?=escapeHtml($shortUuid)?></span>
                </div>
            </div>
        </div>
        <div class="ticket-footer">Presentar este código al ingresar.</div>
    </div>
    <div class="ticket-actions screen-only">
        <button type="button" class="primary" onclick="downloadTicketPng()">⬇️ Descargar PNG</button>
        <button type="button" class="primary" onclick="printTicket()">🖨️ Imprimir</button>
        <a href="invitados.php?evento=<?=escapeHtml($guest['evento_id'])?>" class="button-link secondary">↩️ Volver</a>
    </div>
</div>
<script>
    function printTicket() {
        window.print();
    }

    function sanitizeFileName(value) {
        return value.replace(/[^A-Za-z0-9_-]+/g, '_').replace(/^_+|_+$/g, '').slice(0, 64);
    }

    function downloadTicketPng() {
        if (typeof html2canvas !== 'function') {
            alert('No se pudo cargar html2canvas. Recarga la página e intenta de nuevo.');
            return;
        }

        const ticket = document.getElementById('ticket-card');
        html2canvas(ticket, {
            backgroundColor: '#151515',
            scale: window.devicePixelRatio || 2,
            useCORS: true,
        }).then(function(canvas) {
            const link = document.createElement('a');
            const fileName = 'Ticket_' + sanitizeFileName('<?=escapeHtml($guestName)?>') + '_' + sanitizeFileName('<?=escapeHtml($eventName)?>') + '.png';
            link.href = canvas.toDataURL('image/png');
            link.download = fileName;
            link.click();
        }).catch(function() {
            alert('No se pudo generar la imagen. Intenta nuevamente.');
        });
    }
</script>
</body>
</html>
