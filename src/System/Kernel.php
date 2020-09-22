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
use App\EventLoop\LoopInterface;
use Serafim\SDL\Image\Image;
use Serafim\SDL\SDL;

trait Kernel
{
    protected Application $app;
    protected LoopInterface $loop;
    protected SDL $sdl;
    protected Image $img;
    protected Window $window;
    protected Renderer $renderer;
    protected Viewport $vp;

    protected function bootKernel(): void
    {
        $this->app = Application::getInstance();
        $this->sdl = SDL::getInstance();
        $this->img = Image::getInstance();

        $this->loop = $this->app->loop;
        $this->window = $this->app->window;
        $this->renderer = $this->app->renderer;
        $this->vp = $this->app->vp;
    }
}
