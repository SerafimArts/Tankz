<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Model\Shot;

use FFI\CData;
use Serafim\SDL\Point;
use Serafim\SDL\SDL;

class Rotation
{
    /**
     * @var float
     */
    public float $angle = 0;

    /**
     * @var CData|Point
     */
    public CData $center;

    public function __construct()
    {
        $this->center = SDL::getInstance()
            ->new('SDL_FPoint')
        ;
    }

    /**
     * @return void
     */
    public function __destruct()
    {
        \FFI::free(SDL::addr($this->center));
    }
}
