<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Model\Tank;

class Health
{
    public float $max = 100;
    public float $current = 100;

    public function reset(): void
    {
        $this->current = $this->max;
    }
}
