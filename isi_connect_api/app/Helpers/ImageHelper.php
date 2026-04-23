<?php

namespace App\Helpers;

class ImageHelper
{
    public static function compress(string $storagePath, int $maxW = 1280, int $maxH = 1280, int $quality = 82): void
    {
        $fullPath = storage_path('app/public/' . $storagePath);
        $script   = base_path('compress_image.py');

        if (!file_exists($fullPath) || !file_exists($script)) return;

        $cmd = sprintf('python "%s" "%s" %d %d %d 2>&1', $script, $fullPath, $maxW, $maxH, $quality);
        exec($cmd);
    }
}
