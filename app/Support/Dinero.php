<?php

namespace App\Support;

/**
 * Formato de montos: hasta 4 decimales de precisión (necesarios para
 * costos como 2.6087), pero sin rellenar con ceros innecesarios y con
 * un mínimo de 2 decimales. Ejemplos: 7 -> "7.00", 2.6 -> "2.60",
 * 2.6087 -> "2.6087", 1234.5 -> "1,234.50".
 */
class Dinero
{
    public static function fmt($valor, int $min = 2, int $max = 4): string
    {
        return self::formatear($valor, $min, $max, ',');
    }

    /** Para el value de un <input type="number">: sin separador de miles. */
    public static function plano($valor, int $min = 2, int $max = 4): string
    {
        return self::formatear($valor, $min, $max, '');
    }

    private static function formatear($valor, int $min, int $max, string $miles): string
    {
        $s = number_format(round((float) $valor, $max), $max, '.', $miles);

        if ($max > $min && str_contains($s, '.')) {
            [$entero, $dec] = explode('.', $s, 2);
            $dec = rtrim($dec, '0');
            $dec = str_pad($dec, $min, '0');
            $s = $entero . ($dec !== '' ? '.' . $dec : '');
        }

        return $s;
    }
}
