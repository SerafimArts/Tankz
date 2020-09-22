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
use Serafim\SDL\SDL;
use Serafim\SDL\WindowPtr;

class Window
{
    /**
     * @var CData|WindowPtr
     */
    public CData $ptr;

    /**
     * @var SDL
     */
    private SDL $sdl;

    /**
     * @var int
     */
    private int $left = SDL::SDL_WINDOWPOS_CENTERED;

    /**
     * @var int
     */
    private int $top = SDL::SDL_WINDOWPOS_CENTERED;

    /**
     * @var int
     */
    private int $flags = SDL::SDL_WINDOW_SHOWN;

    /**
     * @param string $title
     * @param int    $w
     * @param int    $h
     */
    public function __construct(string $title, int $w, int $h)
    {
        $this->sdl = SDL::getInstance();

        $this->ptr = $this->sdl->SDL_CreateWindow($title, $this->left, $this->top, $w, $h, $this->flags);
    }

    /**
     * @return void
     */
    public function show(): void
    {
        $this->sdl->SDL_ShowWindow($this->ptr);
    }

    /**
     * @return void
     */
    public function hide(): void
    {
        $this->sdl->SDL_HideWindow($this->ptr);
    }

    public function update(): void
    {
        $this->sdl->SDL_UpdateWindowSurface($this->ptr);
    }

    public function __destruct()
    {
        $this->sdl->SDL_DestroyWindow($this->ptr);
    }
}
