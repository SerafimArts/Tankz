<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\System;

use App\Application;
use FFI\CData;
use Serafim\SDL\RendererPtr;
use Serafim\SDL\SDL;

class Renderer
{
    /**
     * @var SDL
     */
    private SDL $sdl;

    /**
     * @var CData|RendererPtr
     */
    public CData $ptr;

    /**
     * @var int
     */
    private int $flags = SDL::SDL_RENDERER_ACCELERATED;

    public function __construct()
    {
        $app = Application::getInstance();

        $this->sdl = $app->sdl;
        $this->ptr = $app->sdl->SDL_CreateRenderer($app->window->ptr, -1, $this->flags);

        $app->sdl->SDL_SetRenderDrawColor($this->ptr, 0, 0, 0, 0xff);
    }

    public function present(): void
    {
        $this->sdl->SDL_RenderPresent($this->ptr);
    }

    public function clear(): void
    {
        $this->sdl->SDL_RenderClear($this->ptr);
    }

    public function __destruct()
    {
        $this->sdl->SDL_DestroyRenderer($this->ptr);
    }
}
