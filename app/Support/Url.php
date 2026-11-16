<?php

namespace App\Support;

class Url
{
    /**
     * Normalisasi tautan eksternal (LinkedIn, CV, portofolio, dsb).
     *
     * - Kosong → null.
     * - Sudah http(s):// → dipakai apa adanya.
     * - Tanpa skema tapi mirip domain (mis. "linkedin.com/in/x") → ditambah "https://".
     * - Skema berbahaya (mis. "javascript:") atau bentuk tak dikenal → null.
     */
    public static function external(?string $value): ?string
    {
        $text = trim((string) $value);

        if ($text === '') {
            return null;
        }

        if (preg_match('/^https?:\/\/\S+$/i', $text)) {
            return $text;
        }

        // Tolak skema selain http(s), mis. javascript: atau data:.
        if (preg_match('/^[a-z][a-z0-9+.-]*:/i', $text)) {
            return null;
        }

        return preg_match('/^[\w-]+(\.[\w-]+)+([\/?#]\S*)?$/', $text)
            ? 'https://'.$text
            : null;
    }
}
