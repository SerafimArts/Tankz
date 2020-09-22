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
use Serafim\SDL\Kernel\Video\BlendMode;
use Serafim\SDL\Kernel\Video\RendererFlip;
use Serafim\SDL\SDL;

class UserInterface
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
    private Texture $health;
    private Texture $healthEnd;

    private CData $uiDest;
    private CData $mapDest;
    private CData $userDest;
    private CData $userCenter;
    private CData $reloadingDest;
    private CData $healthDest;
    private CData $healthEndDest;

    /**
     * @var Tank|null
     */
    private ?Tank $tank = null;

    /**
     * @var Tank[]
     */
    private array $enemyTanks = [];

    public function __construct()
    {
        $this->bootKernel();

        $this->bg = Texture::fromPathname(self::ROOT . '/bg.png');
        $this->overlay = Texture::fromPathname(self::ROOT . '/overlay.png');
        $this->user = Texture::fromPathname(self::ROOT . '/player.png');
        $this->enemy = Texture::fromPathname(self::ROOT . '/enemy.png');
        $this->reloading = Texture::fromPathname(self::ROOT . '/reloading.png');
        $this->health = Texture::fromPathname(self::ROOT . '/hp-line.png');
        $this->healthEnd = Texture::fromPathname(self::ROOT . '/hp-right.png');

        $this->sdl->SDL_SetTextureAlphaMod($this->bg->ptr, 130);
        $this->sdl->SDL_SetTextureAlphaMod($this->overlay->ptr, 230);

        $this->uiDest = $this->sdl->new(FRect::class);
        $this->uiDest->w = 476;
        $this->uiDest->h = 140;
        $this->uiDest->x = 20;
        $this->uiDest->y = 920;

        $this->healthDest = $this->sdl->new(FRect::class);
        $this->healthDest->w = 1;
        $this->healthDest->h = 9;
        $this->healthDest->x = 174;
        $this->healthDest->y = 1027;

        $this->healthEndDest = $this->sdl->new(FRect::class);
        $this->healthEndDest->w = 3;
        $this->healthEndDest->h = 9;
        $this->healthEndDest->x = 175;
        $this->healthEndDest->y = 1027;

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

    /**
     * @param Tank $tank
     */
    public function addEnemyTank(Tank $tank): void
    {
        $this->enemyTanks[] = $tank;
    }

    public function addPlayerTank(Tank $tank): void
    {
        $this->tank = $tank;
    }

    public function update(): void
    {
        if ($this->tank) {
            // Reloading
            $delta = $this->tank->gun->shot->reloading / $this->tank->gun->shot->speed;

            $this->reloadingDest->w = self::RELOADING_MAX - $delta * self::RELOADING_MAX;

            // Health
            $delta = $this->tank->health->current / $this->tank->health->max;
            $this->healthDest->w = $delta * 301;

            $this->healthEndDest->x = $this->healthDest->x + $this->healthDest->w;
        }
    }

    public function render(): void
    {
        $this->sdl->SDL_RenderCopyF(
            $this->renderer->ptr,
            $this->bg->ptr,
            null,
            SDL::addr($this->vp->transform($this->mapDest, false))
        );

        if ($this->tank !== null) {
            $this->userDest->x = $this->tank->dest->x / 1920 * 110 + 10 + $this->uiDest->x;
            $this->userDest->y = $this->tank->dest->y / 1080 * 110 + 10 + $this->uiDest->y;

            $this->sdl->SDL_RenderCopyExF(
                $this->renderer->ptr,
                $this->user->ptr,
                null,
                SDL::addr($this->vp->transform($this->userDest, false)),
                $this->tank->rotation->angle,
                SDL::addr($this->userCenter),
                RendererFlip::SDL_FLIP_NONE
            );
        }

        foreach ($this->enemyTanks as $enemy) {
            $this->userDest->x = $enemy->dest->x / 1920 * 110 + 10 + $this->uiDest->x;
            $this->userDest->y = $enemy->dest->y / 1080 * 110 + 10 + $this->uiDest->y;

            $this->sdl->SDL_RenderCopyExF(
                $this->renderer->ptr,
                $this->enemy->ptr,
                null,
                SDL::addr($this->vp->transform($this->userDest, false)),
                $enemy->rotation->angle,
                SDL::addr($this->userCenter),
                RendererFlip::SDL_FLIP_NONE
            );
        }

        $this->sdl->SDL_RenderCopyF(
            $this->renderer->ptr,
            $this->overlay->ptr,
            null,
            SDL::addr($this->vp->transform($this->uiDest, false))
        );

        // Reloading
        $this->sdl->SDL_RenderCopyF(
            $this->renderer->ptr,
            $this->reloading->ptr,
            null,
            SDL::addr($this->vp->transform($this->reloadingDest, false))
        );

        // Health
        $this->sdl->SDL_RenderCopyF(
            $this->renderer->ptr,
            $this->health->ptr,
            null,
            SDL::addr($this->vp->transform($this->healthDest, false))
        );

        $this->sdl->SDL_RenderCopyF(
            $this->renderer->ptr,
            $this->healthEnd->ptr,
            null,
            SDL::addr($this->vp->transform($this->healthEndDest, false))
        );
    }
}
