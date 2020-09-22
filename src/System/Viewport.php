<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\System;

use FFI\CData;
use Serafim\SDL\FRect;
use Serafim\SDL\Rect;
use Serafim\SDL\SDL;

class Viewport
{
    /**
     * @var CData|Rect
     */
    private CData $rectInt;

    /**
     * @var CData|FRect|Rect
     */
    private CData $rectFloat;

    /**
     * @var float
     */
    private float $x1;

    /**
     * @var float
     */
    private float $y1;

    /**
     * @var float
     */
    private float $x2;

    /**
     * @var float
     */
    private float $y2;

    /**
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     */
    public function __construct(float $x1, float $y1, float $x2, float $y2)
    {
        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->x2 = $x2;
        $this->y2 = $y2;

        $this->rectInt = SDL::getInstance()
            ->new(Rect::class)
        ;

        $this->rectFloat = SDL::getInstance()
            ->new(FRect::class)
        ;

    }

    /**
     * @param float $x
     * @return float
     */
    public function x(float $x): float
    {
        return $this->x1 / $this->x2 * $x;
    }

    /**
     * @param float $y
     * @return float
     */
    public function y(float $y): float
    {
        return $this->y1 / $this->y2 * $y;
    }

    /**
     * @param CData|Rect $rect
     * @param bool       $int
     * @return CData|Rect
     */
    public function transform(CData $rect, bool $int = true): CData
    {
        $result = $int ? $this->rectInt : $this->rectFloat;

        $result->x = $this->x1 / $this->x2 * $rect->x;
        $result->y = $this->y1 / $this->y2 * $rect->y;
        $result->w = $this->x1 / $this->x2 * $rect->w;
        $result->h = $this->y1 / $this->y2 * $rect->h;

        return $result;
    }
}
