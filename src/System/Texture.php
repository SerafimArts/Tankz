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
use Serafim\SDL\Kernel\Video\ScaleMode;
use Serafim\SDL\SDL;
use Serafim\SDL\TexturePtr;

class Texture
{
    /**
     * @var CData|TexturePtr
     */
    public CData $ptr;

    /**
     * @param CData $texture
     */
    private function __construct(CData $texture)
    {
        $this->ptr = $texture;
    }

    /**
     * @param Surface $surface
     * @return static
     */
    public static function fromSurface(Surface $surface): self
    {
        $app = Application::getInstance();

        $texture = $app->sdl->SDL_CreateTextureFromSurface($app->renderer->ptr, $surface->ptr);

        return new Texture($texture);
    }

    /**
     * @param string $path
     * @return static
     */
    public static function fromPathname(string $path): self
    {
        $sdl = SDL::getInstance();

        $result = self::fromSurface(
            Surface::fromPathname($path)
        );

        if (\version_compare($sdl->info->version, '2.0.12') >= 0) {
            $sdl->SDL_SetTextureScaleMode($result->ptr, ScaleMode::SDL_SCALE_MODE_LINEAR);
        }

        return $result;
    }
}
