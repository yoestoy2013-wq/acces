<?php

class QRCodeGenerator
{
    /**
     * Generate QR code URL using QR Server API (free, no dependencies)
     * 
     * @param string $data The data to encode in the QR code
     * @param int $size Size of the QR code in pixels (default: 200)
     * @return string URL to the QR code image
     */
    public static function generateQRUrl(string $data, int $size = 200): string
    {
        $encodedData = urlencode($data);
        return "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data={$encodedData}";
    }

    /**
     * Generate QR code as base64 embedded image
     * Useful for embedding in HTML without external calls
     * 
     * @param string $data The data to encode in the QR code
     * @param int $size Size of the QR code in pixels (default: 200)
     * @return string Base64 data URI of the QR code
     */
    public static function generateQRBase64(string $data, int $size = 200): string
    {
        $url = self::generateQRUrl($data, $size);
        $imageData = @file_get_contents($url);
        
        if ($imageData === false) {
            // Fallback: return placeholder if API fails
            return "data:image/svg+xml;base64," . base64_encode(
                '<svg xmlns="http://www.w3.org/2000/svg" width="' . $size . '" height="' . $size . '"><rect fill="white" width="' . $size . '" height="' . $size . '"/><text x="50%" y="50%" text-anchor="middle" dy=".3em" font-size="12" fill="black">QR Error</text></svg>'
            );
        }
        
        return "data:image/png;base64," . base64_encode($imageData);
    }
}
