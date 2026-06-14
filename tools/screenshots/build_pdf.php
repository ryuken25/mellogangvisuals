<?php
/**
 * Generate PDF dokumentasi pages dari README + screenshot.
 * Usage: php tools/screenshots/build_pdf.php
 *
 * Output: pages/MellogangVisuals-Pages.pdf
 */

$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';

$readmePath = $root . '/pages/README.md';
$pagesDir   = $root . '/pages';
$outPath    = $root . '/pages/MellogangVisuals-Pages.pdf';

if (! is_file($readmePath)) {
    fwrite(STDERR, "README not found: $readmePath\n");
    exit(1);
}

$md = file_get_contents($readmePath);

// ============ Minimal markdown to HTML ============

// Escape dulu, tapi gambar sintaks ![..](path) kita proses manual
$lines = explode("\n", $md);
$html  = [];
$inList = false;
$inTable = false;
$inCode = false;
$codeBuf = [];

$flushList = function() use (&$html, &$inList) {
    if ($inList) { $html[] = '</ul>'; $inList = false; }
};
$flushTable = function() use (&$html, &$inTable) {
    if ($inTable) { $html[] = '</tbody></table>'; $inTable = false; }
};
$flushCode = function() use (&$html, &$inCode, &$codeBuf) {
    if ($inCode) {
        $html[] = '<pre style="background:#f3f6f8;padding:10px;border-radius:8px;font-size:11px;">'
            . htmlspecialchars(implode("\n", $codeBuf), ENT_QUOTES, 'UTF-8')
            . '</pre>';
        $inCode = false;
        $codeBuf = [];
    }
};

$absImg = function(string $rel) use ($pagesDir): string {
    // image relative to pages/README.md → pages/...
    // Untuk Dompdf, embed sebagai data URI base64 (no file access needed).
    $rel = ltrim($rel, './');
    $abs = realpath($pagesDir . '/' . $rel);
    if (! $abs || ! is_file($abs)) {
        return '';
    }
    $data = file_get_contents($abs);
    $ext  = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
    $mime = match ($ext) {
        'png'  => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'webp' => 'image/webp',
        default => 'image/png',
    };
    return 'data:' . $mime . ';base64,' . base64_encode($data);
};

foreach ($lines as $line) {
    if (preg_match('/^```/', $line)) {
        if ($inCode) { $flushCode(); }
        else { $flushList(); $flushTable(); $inCode = true; }
        continue;
    }
    if ($inCode) { $codeBuf[] = $line; continue; }

    // Tables
    if (preg_match('/^\s*\|.*\|\s*$/', $line)) {
        $flushList();
        $cells = array_map('trim', explode('|', trim($line, '| ')));
        // Baris pemisah |---|---| -> skip
        if (preg_match('/^\s*\|[-:\s|]+\|\s*$/', $line)) {
            continue;
        }
        // Proses image di dalam sel tabel juga
        $cells = array_map(function ($c) use ($absImg) {
            return preg_replace_callback('/!\[([^\]]*)\]\(([^)]+)\)/', function ($m) use ($absImg) {
                $alt = htmlspecialchars($m[1]);
                $src = $absImg($m[2]);
                if ($src === '') {
                    return htmlspecialchars($m[0]);
                }
                return '<img src="' . htmlspecialchars($src) . '" alt="' . $alt . '" style="max-width:100%;border:1px solid #20302C;border-radius:6px;">';
            }, $c);
        }, $cells);
        if (! $inTable) {
            $html[] = '<table style="width:100%;border-collapse:collapse;margin:8px 0;">';
            $html[] = '<thead><tr>';
            foreach ($cells as $c) {
                $html[] = '<th style="background:#0E1413;color:#E8F1EE;padding:6px 8px;text-align:left;font-size:11px;">' . htmlspecialchars($c) . '</th>';
            }
            $html[] = '</tr></thead><tbody>';
            $inTable = true;
        } else {
            $html[] = '<tr>';
            foreach ($cells as $c) {
                $html[] = '<td style="border-bottom:1px solid #20302C;padding:6px 8px;font-size:12px;vertical-align:top;">' . $c . '</td>';
            }
            $html[] = '</tr>';
        }
        continue;
    } else {
        $flushTable();
    }

    // Headers
    if (preg_match('/^(#{1,6})\s+(.+)$/', $line, $m)) {
        $flushList();
        $lvl = strlen($m[1]);
        $txt = $m[2];
        $sz = [1=>26, 2=>20, 3=>16, 4=>14, 5=>13, 6=>12][$lvl] ?? 12;
        $html[] = "<h{$lvl} style=\"font-family:Helvetica,Arial,sans-serif;font-size:{$sz}px;color:#0A0E0D;margin:14px 0 6px;\">" . htmlspecialchars($txt) . "</h{$lvl}>";
        continue;
    }

    // Images: ![alt](path) — convert to <img> with absolute path for dompdf
    if (preg_match('/!\[([^\]]*)\]\(([^)]+)\)/', $line, $m)) {
        $flushList();
        $alt = htmlspecialchars($m[1]);
        $src = $absImg($m[2]);
        $html[] = '<div style="margin:8px 0;text-align:center;">'
            . '<img src="' . htmlspecialchars($src) . '" alt="' . $alt . '" style="max-width:100%;border:1px solid #20302C;border-radius:8px;">'
            . '</div>';
        continue;
    }

    // Lists
    if (preg_match('/^[\-\*]\s+(.+)$/', $line, $m)) {
        if (! $inList) { $html[] = '<ul style="margin:6px 0;padding-left:20px;">'; $inList = true; }
        $html[] = '<li style="font-size:13px;line-height:1.5;margin:2px 0;">' . $m[1] . '</li>';
        continue;
    } else {
        $flushList();
    }

    // Blockquote
    if (preg_match('/^>\s?(.*)$/', $line, $m)) {
        $html[] = '<blockquote style="border-left:3px solid #00B98B;margin:8px 0;padding:4px 12px;color:#475569;background:#f3f6f8;font-size:13px;">' . $m[1] . '</blockquote>';
        continue;
    }

    // Horizontal rule
    if (preg_match('/^---+\s*$/', $line)) {
        $html[] = '<hr style="border:0;border-top:1px solid #20302C;margin:14px 0;">';
        continue;
    }

    // Paragraph (kalau bukan baris kosong)
    if (trim($line) !== '') {
        $html[] = '<p style="font-size:13px;line-height:1.55;margin:6px 0;">' . $line . '</p>';
    } else {
        $html[] = '';
    }
}
$flushList(); $flushTable(); $flushCode();

$body = implode("\n", $html);

// Dompdf
$options = new \Dompdf\Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', false);
$options->set('defaultFont', 'Helvetica');

$dompdf = new \Dompdf\Dompdf($options);
$dompdf->loadHtml('<html><head><meta charset="utf-8"><title>MellogangVisuals — Pages</title></head><body style="font-family:Helvetica,Arial,sans-serif;color:#0A0E0D;">' . $body . '</body></html>');
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

file_put_contents($outPath, $dompdf->output());
echo "PDF generated: $outPath\n";
echo "Size: " . number_format(filesize($outPath)) . " bytes\n";
