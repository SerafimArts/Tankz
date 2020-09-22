<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Model;

use App\Math\Utils;
use App\System\Texture;
use FFI\CData;
use Serafim\SDL\FRect;
use Serafim\SDL\Kernel\Video\RendererFlip;
use Serafim\SDL\SDL;

class Bullet extends Model
{
    /**
     * @var Texture
     */
    private Texture $texture;

    /**
     * @var CData
     */
    private CData $center;

    /**
     * @var CData
     */
    public CData $dest;

    /**
     * @var float|int
     */
    public float $angle = 0;

    /**
     * @var float|int
     */
    public float $speed = 600;

    public function __construct(Texture $texture, Gun $gun)
    {
        parent::__construct();

        $this->texture = $texture;

        $this->dest = $this->sdl->new(FRect::class);
        $this->dest->w = 16;
        $this->dest->h = 36;

        $this->center = $this->sdl->new('SDL_FPoint');
        $this->center->x = $this->dest->w / 2;
        $this->center->y = $this->dest->h / 2;

        $this->angle = $gun->rotation->angle;

        [$x, $y] = Utils::rotate(
            0, $gun->dest->h + 10,
            0, 0,
            $this->angle
        );

        $this->dest->x = $x + $gun->dest->x;
        $this->dest->y = $y + $gun->dest->y;
    }

    /**
     * @param float $delta
     * @param ModelInterface|null $parent
     */
    public function update(float $delta, ModelInterface $parent = null): void
    {
        $this->dest->x += \sin(-$this->angle * \M_PI / 180) * $this->speed * $delta;
        $this->dest->y += \cos(-$this->angle * \M_PI / 180) * $this->speed * $delta;
    }

    /**
     * @return void
     */
    public function render(): void
    {
        $this->sdl->SDL_RenderCopyExF(
            $this->renderer->ptr,
            $this->texture->ptr,
            null,
            SDL::addr($this->dest),
            $this->angle,
            SDL::addr($this->center),
            RendererFlip::SDL_FLIP_VERTICAL
        );
    }
}
