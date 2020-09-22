<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Model\Tank;

class State
{
    public const STATE_MOVED = 0x02;
    public const STATE_ROTATED = 0x04;
    public const STATE_SHOOT = 0x08;

    /**
     * @param int $state
     * @return string
     */
    public static function toString(int $state): string
    {
        $states = [];

        if ($state & self::STATE_MOVED) {
            $states[] = 'moved';
        }

        if ($state & self::STATE_ROTATED) {
            $states[] = 'rotated';
        }

        if ($state & self::STATE_SHOOT) {
            $states[] = 'shoot';
        }

        return \implode(' + ', $states);
    }
}
