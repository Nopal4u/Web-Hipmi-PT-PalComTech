<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class Media
{
    /**
     * Ubah path penyimpanan menjadi URL yang bisa dipakai browser.
     * Mendukung URL absolut, data URL, path publik Laravel, dan file storage.
     */
    public static function url(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return '';
        }

        if (preg_match('#^(https?:)?//#i', $path)
            || str_starts_with($path, '/')
            || str_starts_with($path, 'data:')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }

    /** Apakah nilai sudah berupa URL/path yang bisa langsung ditampilkan. */
    public static function isExternal(?string $path): bool
    {
        $path = trim((string) $path);

        return $path !== '' && (
            preg_match('#^(https?:)?//#i', $path)
            || str_starts_with($path, '/')
            || str_starts_with($path, 'data:')
        );
    }
}
