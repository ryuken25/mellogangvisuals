<?php
/**
 * Global helpers for I18n.
 *
 * File ini di-autoload oleh composer.json (files key) sehingga
 * `t()` selalu tersedia di semua view + controller.
 */

if (! function_exists('t')) {
    /**
     * Translate a key. Kembalikan key kalau tidak ada di dictionary.
     *
     * @param string               $key
     * @param array<string,string> $params strtr params
     */
    function t(string $key, array $params = []): string
    {
        return \App\Support\I18n::t($key, $params);
    }
}

if (! function_exists('current_lang')) {
    /**
     * Locale aktif ("en" | "id").
     */
    function current_lang(): string
    {
        return \App\Support\I18n::get();
    }
}
