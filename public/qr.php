<?php
require_once __DIR__ . '/../helpers/QRCodeGenerator.php';
require_once __DIR__ . '/../models/Invitado.php';

header('Content-Type: image/png');

$invitadoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($invitadoId <= 0) {
    http_response_code(400);
    exit;
}

$invitadoModel = new Invitado();
$invitado = $invitadoModel->find($invitadoId);

if (!$invitado) {
    http_response_code(404);
    exit;
}

// Generate QR with unique ID
$data = $invitado['unique_id'] ?? 'INV-' . $invitadoId;
$imageUrl = QRCodeGenerator::generateQRUrl($data, 300);

// Fetch and output the image
$imageData = file_get_contents($imageUrl);
if ($imageData === false) {
    http_response_code(500);
    exit;
}

echo $imageData;
