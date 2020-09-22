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
use Serafim\SDL\SDL;
use Serafim\SDL\SurfacePtr;

class Surface
{
    /**
     * @var SDL
     */
    private SDL $sdl;

    /**
     * @var CData|SurfacePtr
     */
    public CData $ptr;

    /**
     * @param CData $surface
     */
    private function __construct(CData $surface)
    {
        $app = Application::getInstance();

        $this->sdl = $app->sdl;
        $this->ptr = $surface;
    }

    /**
     * @param string $path
     * @return static
     */
    public static function fromPathname(string $path): self
    {
        $app = Application::getInstance();

        return new Surface($app->img->load($path));
    }

    public function __destruct()
    {
        $this->sdl->SDL_FreeSurface($this->ptr);
    }
}
