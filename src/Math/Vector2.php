<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Math;

class Vector2
{
    /**
     * @var float
     */
    public float $x;

    /**
     * @var float
     */
    public float $y;

    /**
     * @param float $x
     * @param float $y
     */
    public function __construct(float $x = 0, float $y = 0)
    {
        $this->x = $x;
        $this->y = $y;
    }
}
