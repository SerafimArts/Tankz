<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Ui;

use App\Model\Tank;
use App\System\Kernel;
use App\System\Texture;
use FFI\CData;
use Serafim\SDL\FRect;
use Serafim\SDL\Kernel\Video\RendererFlip;
use Serafim\SDL\Rect;
use Serafim\SDL\SDL;

class Map
{
    use Kernel;

    /**
     * @var string
     */
    private const ROOT = __DIR__ . '/../../resources/ui/map';

    /**
     * @var int
     */
    private const RELOADING_MAX = 114;

    private Texture $bg;
    private Texture $overlay;
    private Texture $user;
    private Texture $enemy;
    private Texture $reloading;
    private CData $uiDest;
    private CData $mapDest;
    private CData $userDest;
    private CData $userCenter;
    private CData $reloadingDest;

    /**
     * @var Tank
     */
    private Tank $tank;

    /**
     * @param Tank $tank
     */
    public function __construct(Tank $tank)
    {
        $this->bootKernel();

        $this->tank = $tank;
        $this->bg = Texture::fromPathname(self::ROOT . '/bg.png');
        $this->overlay = Texture::fromPathname(self::ROOT . '/overlay.png');
        $this->user = Texture::fromPathname(self::ROOT . '/player.png');
        $this->enemy = Texture::fromPathname(self::ROOT . '/enemy.png');
        $this->reloading = Texture::fromPathname(self::ROOT . '/reloading.png');

        $this->sdl->SDL_SetTextureAlphaMod($this->bg->ptr, 130);
        $this->sdl->SDL_SetTextureAlphaMod($this->overlay->ptr, 230);

        $this->uiDest = $this->sdl->new(FRect::class);
        $this->uiDest->w = 476;
        $this->uiDest->h = 140;
        $this->uiDest->x = 20;
        $this->uiDest->y = 920;

        $this->reloadingDest = $this->sdl->new(FRect::class);
        $this->reloadingDest->w = 0;
        $this->reloadingDest->h = 5;
        $this->reloadingDest->x = 364;
        $this->reloadingDest->y = 1018;

        $this->mapDest = $this->sdl->new(FRect::class);
        $this->mapDest->w = 140;
        $this->mapDest->h = 140;
        $this->mapDest->x = 20;
        $this->mapDest->y = 920;

        $this->userCenter = $this->sdl->new('SDL_FPoint');
        $this->userCenter->x = 9;
        $this->userCenter->y = 9;
        $this->userDest = $this->sdl->new(FRect::class);

        $this->userDest->w = 18;
        $this->userDest->h = 18;
        $this->userDest->x = 30;
        $this->userDest->y = 30;
    }

    public function update(): void
    {
        $this->userDest->x = $this->tank->dest->x / 1920 * 110 + 10 + $this->uiDest->x;
        $this->userDest->y = $this->tank->dest->y / 1080 * 110 + 10 + $this->uiDest->y;

        //
        $delta = $this->tank->gun->shot->reloading / $this->tank->gun->shot->speed;

        $this->reloadingDest->w = self::RELOADING_MAX - $delta * self::RELOADING_MAX;
    }

    public function render(): void
    {
        $this->sdl->SDL_RenderCopyF(
            $this->renderer->ptr,
            $this->bg->ptr,
            null,
            SDL::addr($this->vp->transform($this->mapDest, false))
        );

        $this->sdl->SDL_RenderCopyExF(
            $this->renderer->ptr,
            $this->user->ptr,
            null,
            SDL::addr($this->vp->transform($this->userDest, false)),
            $this->tank->rotation->angle,
            SDL::addr($this->userCenter),
            RendererFlip::SDL_FLIP_NONE
        );

        $this->sdl->SDL_RenderCopyF(
            $this->renderer->ptr,
            $this->overlay->ptr,
            null,
            SDL::addr($this->vp->transform($this->uiDest, false))
        );

        $this->sdl->SDL_RenderCopyF(
            $this->renderer->ptr,
            $this->reloading->ptr,
            null,
            SDL::addr($this->vp->transform($this->reloadingDest, false))
        );
    }
}
