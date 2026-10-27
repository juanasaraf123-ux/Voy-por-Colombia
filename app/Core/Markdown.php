<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Markdown seguro y reducido para el contenido editorial.
 * Escapa todo el HTML antes de aplicar formato, así un editor no puede inyectar scripts.
 * Soporta: ## títulos, **negrita**, *cursiva*, [enlaces](https://...), listas, > citas y párrafos.
 */
final class Markdown
{
    public static function render(string $text): string
    {
        $text = str_replace(["\r\n", "\r"], "\n", trim($text));
        if ($text === '') {
            return '';
        }
        $blocks = preg_split("/\n{2,}/", $text) ?: [];
        $html = '';
        foreach ($blocks as $block) {
            $lines = explode("\n", $block);
            if (preg_match('/^(#{2,4})\s+(.+)$/', $lines[0], $m)) {
                // Un subtítulo puede ir seguido de texto sin línea en blanco.
                $level = strlen($m[1]);
                $html .= "<h$level>" . self::inline($m[2]) . "</h$level>\n";
                array_shift($lines);
                if ($lines) {
                    $html .= self::render(implode("\n", $lines));
                }
            } elseif (self::allMatch($lines, '/^\s*[-*]\s+/')) {
                $html .= '<ul>' . implode('', array_map(static fn ($l) => '<li>' . self::inline((string) preg_replace('/^\s*[-*]\s+/', '', $l)) . '</li>', $lines)) . "</ul>\n";
            } elseif (self::allMatch($lines, '/^\s*\d+[.)]\s+/')) {
                $html .= '<ol>' . implode('', array_map(static fn ($l) => '<li>' . self::inline((string) preg_replace('/^\s*\d+[.)]\s+/', '', $l)) . '</li>', $lines)) . "</ol>\n";
            } elseif (self::allMatch($lines, '/^>\s?/')) {
                $html .= '<blockquote><p>' . implode('<br>', array_map(static fn ($l) => self::inline((string) preg_replace('/^>\s?/', '', $l)), $lines)) . "</p></blockquote>\n";
            } else {
                $html .= '<p>' . implode('<br>', array_map([self::class, 'inline'], $lines)) . "</p>\n";
            }
        }
        return $html;
    }

    private static function allMatch(array $lines, string $regex): bool
    {
        foreach ($lines as $l) {
            if (!preg_match($regex, $l)) {
                return false;
            }
        }
        return true;
    }

    public static function inline(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $text = (string) preg_replace_callback('/\[([^\]]+)\]\((https?:\/\/[^\s)]+|\/(?!\/)[^\s)]*)\)/', static function ($m) {
            $external = str_starts_with($m[2], 'http');
            return '<a href="' . $m[2] . '"' . ($external ? ' target="_blank" rel="noopener noreferrer"' : '') . '>' . $m[1] . '</a>';
        }, $text);
        $text = (string) preg_replace('/\*\*(.+?)\*\*/', '<strong>$1</strong>', $text);
        $text = (string) preg_replace('/(?<![*\w])\*(?!\s)(.+?)(?<!\s)\*(?![*\w])/', '<em>$1</em>', $text);
        return $text;
    }

    /** Texto plano (para resúmenes y búsqueda). */
    public static function plain(string $text): string
    {
        return trim((string) preg_replace(['/[#>*_`]+/', '/\[([^\]]+)\]\([^)]+\)/'], ['', '$1'], $text));
    }
}
