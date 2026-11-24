<?php

namespace App\Support;

use RuntimeException;
use ZipArchive;

/**
 * Generador mínimo de archivos .xlsx (sin dependencias externas).
 * Solo usa la extensión zip de PHP. Soporta varias hojas, estilos
 * predefinidos, formatos de número, celdas combinadas, anchos de columna,
 * fórmulas (con valor en caché) y fila de encabezado congelada.
 */
class XlsxBuilder
{
    // Estilos disponibles (índice en cellXfs de styles.xml)
    public const S_DEFAULT = 0;
    public const S_TITULO = 1;
    public const S_SUBTITULO = 2;
    public const S_ENCABEZADO = 3;
    public const S_TEXTO = 4;
    public const S_DINERO = 5;
    public const S_ENTERO = 6;
    public const S_TOTAL_ETIQUETA = 7;
    public const S_TOTAL_DINERO = 8;
    public const S_PORCENTAJE = 9;
    public const S_TOTAL_ENTERO = 10;
    public const S_SECCION = 11;
    public const S_BUENO = 12;
    public const S_MALO = 13;
    public const S_DINERO_NEGRITA = 14;

    /** @var array<int,array> */
    private array $hojas = [];

    public function hoja(string $nombre): int
    {
        $nombre = preg_replace('/[\\\\\/\?\*\[\]:]/', ' ', $nombre);
        $nombre = mb_substr(trim($nombre), 0, 31);
        $this->hojas[] = [
            'nombre' => $nombre !== '' ? $nombre : 'Hoja' . (count($this->hojas) + 1),
            'celdas' => [],
            'anchos' => [],
            'combinadas' => [],
            'congelar' => null,
        ];
        return count($this->hojas) - 1;
    }

    /** Escribe un valor (texto/número). Fila y columna empiezan en 1. */
    public function celda(int $h, int $fila, int $col, $valor, int $estilo = self::S_DEFAULT): void
    {
        $this->hojas[$h]['celdas'][$fila][$col] = ['v' => $valor, 'e' => $estilo];
    }

    /** Escribe una fórmula; $cache es el valor calculado que se muestra en visores sin recalcular. */
    public function formula(int $h, int $fila, int $col, string $formula, $cache, int $estilo = self::S_DEFAULT): void
    {
        $this->hojas[$h]['celdas'][$fila][$col] = ['f' => ltrim($formula, '='), 'v' => $cache, 'e' => $estilo];
    }

    public function fila(int $h, int $fila, array $valores, int $estilo = self::S_TEXTO, int $colInicial = 1): void
    {
        foreach (array_values($valores) as $i => $v) {
            $this->celda($h, $fila, $colInicial + $i, $v, $estilo);
        }
    }

    public function combinar(int $h, string $rango): void
    {
        $this->hojas[$h]['combinadas'][] = $rango;
    }

    public function ancho(int $h, int $col, float $ancho): void
    {
        $this->hojas[$h]['anchos'][$col] = $ancho;
    }

    /** Congela las filas por encima de $fila (p. ej. 5 deja visibles las filas 1–4). */
    public function congelar(int $h, int $fila): void
    {
        $this->hojas[$h]['congelar'] = $fila;
    }

    public static function col(int $n): string
    {
        $s = '';
        while ($n > 0) {
            $m = ($n - 1) % 26;
            $s = chr(65 + $m) . $s;
            $n = intdiv($n - $m - 1, 26);
        }
        return $s;
    }

    /** Guarda el archivo en $ruta. */
    public function guardar(string $ruta): void
    {
        if (!class_exists(ZipArchive::class)) {
            throw new RuntimeException('La extensión zip de PHP no está activa (habilita extension=zip en php.ini).');
        }
        if (!$this->hojas) {
            $this->hoja('Hoja1');
        }

        $zip = new ZipArchive();
        if ($zip->open($ruta, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('No se pudo crear el archivo Excel.');
        }

        $n = count($this->hojas);
        $ct = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
        for ($i = 1; $i <= $n; $i++) {
            $ct .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        $ct .= '</Types>';
        $zip->addFromString('[Content_Types].xml', $ct);

        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');

        $wb = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>';
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
        foreach ($this->hojas as $i => $hoja) {
            $id = $i + 1;
            $wb .= '<sheet name="' . $this->esc($hoja['nombre']) . '" sheetId="' . $id . '" r:id="rId' . $id . '"/>';
            $rels .= '<Relationship Id="rId' . $id . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $id . '.xml"/>';
        }
        $wb .= '</sheets></workbook>';
        $rels .= '<Relationship Id="rId' . ($n + 1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>';
        $zip->addFromString('xl/workbook.xml', $wb);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $rels);
        $zip->addFromString('xl/styles.xml', $this->estilos());

        foreach ($this->hojas as $i => $hoja) {
            $zip->addFromString('xl/worksheets/sheet' . ($i + 1) . '.xml', $this->xmlHoja($hoja));
        }

        if ($zip->close() !== true) {
            throw new RuntimeException('No se pudo guardar el archivo Excel.');
        }
    }

    private function xmlHoja(array $hoja): string
    {
        $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

        $x .= '<sheetViews><sheetView workbookViewId="0" showGridLines="0">';
        if ($hoja['congelar']) {
            $y = $hoja['congelar'];
            $x .= '<pane ySplit="' . ($y - 1) . '" topLeftCell="A' . $y . '" activePane="bottomLeft" state="frozen"/>';
        }
        $x .= '</sheetView></sheetViews><sheetFormatPr defaultRowHeight="15"/>';

        if ($hoja['anchos']) {
            ksort($hoja['anchos']);
            $x .= '<cols>';
            foreach ($hoja['anchos'] as $c => $w) {
                $x .= '<col min="' . $c . '" max="' . $c . '" width="' . $w . '" customWidth="1"/>';
            }
            $x .= '</cols>';
        }

        $x .= '<sheetData>';
        ksort($hoja['celdas']);
        foreach ($hoja['celdas'] as $f => $cols) {
            ksort($cols);
            $x .= '<row r="' . $f . '">';
            foreach ($cols as $c => $d) {
                $ref = self::col($c) . $f;
                $e = (int) $d['e'];
                $v = $d['v'] ?? null;
                if (isset($d['f'])) {
                    $x .= '<c r="' . $ref . '" s="' . $e . '"><f>' . $this->esc($d['f']) . '</f>'
                        . (is_numeric($v) ? '<v>' . $v . '</v>' : '') . '</c>';
                } elseif ($v === null || $v === '') {
                    $x .= '<c r="' . $ref . '" s="' . $e . '"/>';
                } elseif (is_int($v) || is_float($v) || (is_string($v) && is_numeric($v) && !preg_match('/^0\d/', $v) && strlen($v) < 16 && in_array($e, [self::S_DINERO, self::S_ENTERO, self::S_TOTAL_DINERO, self::S_TOTAL_ENTERO, self::S_PORCENTAJE, self::S_DINERO_NEGRITA], true))) {
                    $x .= '<c r="' . $ref . '" s="' . $e . '"><v>' . (0 + $v) . '</v></c>';
                } else {
                    $x .= '<c r="' . $ref . '" s="' . $e . '" t="inlineStr"><is><t xml:space="preserve">' . $this->esc((string) $v) . '</t></is></c>';
                }
            }
            $x .= '</row>';
        }
        $x .= '</sheetData>';

        if ($hoja['combinadas']) {
            $x .= '<mergeCells count="' . count($hoja['combinadas']) . '">';
            foreach ($hoja['combinadas'] as $m) {
                $x .= '<mergeCell ref="' . $m . '"/>';
            }
            $x .= '</mergeCells>';
        }

        return $x . '<pageMargins left="0.5" right="0.5" top="0.75" bottom="0.75" header="0.3" footer="0.3"/></worksheet>';
    }

    private function estilos(): string
    {
        $verde = 'FF2F4F3E';
        $pastel = 'FFEAF2EC';

        $fonts = [
            '<font><sz val="11"/><name val="Calibri"/></font>',                                                    // 0
            '<font><b/><sz val="16"/><color rgb="' . $verde . '"/><name val="Calibri"/></font>',                  // 1 título
            '<font><i/><sz val="10"/><color rgb="FF666666"/><name val="Calibri"/></font>',                        // 2 subtítulo
            '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>',                        // 3 encabezado
            '<font><b/><sz val="11"/><color rgb="' . $verde . '"/><name val="Calibri"/></font>',                  // 4 total / sección
            '<font><b/><sz val="11"/><color rgb="FF1E7B45"/><name val="Calibri"/></font>',                        // 5 bueno
            '<font><b/><sz val="11"/><color rgb="FFB4452F"/><name val="Calibri"/></font>',                        // 6 malo
            '<font><b/><sz val="11"/><name val="Calibri"/></font>',                                               // 7 negrita
        ];
        $fills = [
            '<fill><patternFill patternType="none"/></fill>',
            '<fill><patternFill patternType="gray125"/></fill>',
            '<fill><patternFill patternType="solid"><fgColor rgb="' . $verde . '"/><bgColor indexed="64"/></patternFill></fill>',    // 2
            '<fill><patternFill patternType="solid"><fgColor rgb="' . $pastel . '"/><bgColor indexed="64"/></patternFill></fill>',   // 3
            '<fill><patternFill patternType="solid"><fgColor rgb="FFE3F1E8"/><bgColor indexed="64"/></patternFill></fill>',          // 4 bueno
            '<fill><patternFill patternType="solid"><fgColor rgb="FFFBE9E6"/><bgColor indexed="64"/></patternFill></fill>',          // 5 malo
        ];
        $borde = '<left style="thin"><color rgb="FFD5DDD8"/></left><right style="thin"><color rgb="FFD5DDD8"/></right>'
               . '<top style="thin"><color rgb="FFD5DDD8"/></top><bottom style="thin"><color rgb="FFD5DDD8"/></bottom><diagonal/>';
        $borders = [
            '<border><left/><right/><top/><bottom/><diagonal/></border>',
            '<border>' . $borde . '</border>',
        ];
        // numFmts personalizados
        $numFmts = '<numFmts count="3">'
            . '<numFmt numFmtId="164" formatCode="&quot;C$&quot; #,##0.00;[Red]\-&quot;C$&quot; #,##0.00"/>'
            . '<numFmt numFmtId="165" formatCode="#,##0"/>'
            . '<numFmt numFmtId="166" formatCode="0.0%"/>'
            . '</numFmts>';

        // [numFmt, font, fill, border, alignment]
        $xfs = [
            [0, 0, 0, 0, ''],                                                    // 0 default
            [0, 1, 0, 0, ''],                                                    // 1 título
            [0, 2, 0, 0, ''],                                                    // 2 subtítulo
            [0, 3, 2, 1, '<alignment horizontal="center" vertical="center" wrapText="1"/>'], // 3 encabezado
            [0, 0, 0, 1, '<alignment vertical="center"/>'],                      // 4 texto
            [164, 0, 0, 1, ''],                                                  // 5 dinero
            [165, 0, 0, 1, ''],                                                  // 6 entero
            [0, 4, 3, 1, ''],                                                    // 7 total etiqueta
            [164, 4, 3, 1, ''],                                                  // 8 total dinero
            [166, 0, 0, 1, ''],                                                  // 9 porcentaje
            [165, 4, 3, 1, ''],                                                  // 10 total entero
            [0, 4, 0, 0, ''],                                                    // 11 sección
            [0, 5, 4, 1, '<alignment horizontal="center"/>'],                    // 12 bueno
            [0, 6, 5, 1, '<alignment horizontal="center"/>'],                    // 13 malo
            [164, 7, 0, 1, ''],                                                  // 14 dinero negrita
        ];

        $x = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
           . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . $numFmts
           . '<fonts count="' . count($fonts) . '">' . implode('', $fonts) . '</fonts>'
           . '<fills count="' . count($fills) . '">' . implode('', $fills) . '</fills>'
           . '<borders count="' . count($borders) . '">' . implode('', $borders) . '</borders>'
           . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
           . '<cellXfs count="' . count($xfs) . '">';
        foreach ($xfs as [$nf, $fo, $fi, $bo, $al]) {
            $x .= '<xf numFmtId="' . $nf . '" fontId="' . $fo . '" fillId="' . $fi . '" borderId="' . $bo . '" xfId="0"'
                . ($nf ? ' applyNumberFormat="1"' : '') . ($fo ? ' applyFont="1"' : '')
                . ($fi ? ' applyFill="1"' : '') . ($bo ? ' applyBorder="1"' : '')
                . ($al ? ' applyAlignment="1">' . $al . '</xf>' : '/>');
        }
        return $x . '</cellXfs><cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';
    }

    private function esc(string $s): string
    {
        $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s);
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
