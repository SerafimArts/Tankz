<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Math;

class Rect
{
    private float $x1;
    private float $y1;
    private float $x2;
    private float $y2;

    public function __construct(float $x1, float $y1, float $x2, float $y2)
    {
        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->x2 = $x2;
        $this->y2 = $y2;
    }

    /**
     * @param float $x
     * @param float $y
     * @return bool
     */
    public function contains(float $x, float $y): bool
    {
        return
            $x >= $this->x1 && $x <= $this->x2 &&
            $y >= $this->y1 && $y <= $this->y2
        ;
    }
}
