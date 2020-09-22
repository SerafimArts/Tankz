<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Math;

class Utils
{
    /**
     * @var float
     */
    public const PI = \M_PI;

    /**
     * @var float
     */
    public const DEG_2_RAD = self::PI / 180;

    /**
     * @var float
     */
    public const RAD_2_DEG = 180 / self::PI;

    /**
     * Rotate point vec2(x1, y1) around point vec2(x2, y2)
     *
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param float $angle
     * @return float[]|int[]
     */
    public static function rotate(float $x1, float $y1, float $x2, float $y2, float $angle): array
    {
        $rad = self::DEG_2_RAD * $angle;

        return self::rotateRad($x1, $y1, $x2, $y2, $rad);
    }

    /**
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @param float $radians
     * @return float[]|int[]
     */
    public static function rotateRad(float $x1, float $y1, float $x2, float $y2, float $radians): array
    {
        return [
            $x2 + ($x1 - $x2) * \cos($radians) - ($y1 - $y2) * \sin($radians),
            $y2 + ($y1 - $y2) * \cos($radians) + ($x1 - $x2) * \sin($radians)
        ];
    }
}
