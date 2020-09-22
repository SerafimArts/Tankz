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
use App\Model\Gun\Rotation;
use App\System\Texture;
use FFI\CData;
use Serafim\SDL\FRect;
use Serafim\SDL\Rect;
use Serafim\SDL\SDL;

class Gun extends Model
{
    /**
     * @var Texture
     */
    public Texture $texture;

    /**
     * @var Rotation
     */
    public Rotation $rotation;

    /**
     * @var CData|Rect|FRect
     */
    public CData $dest;

    /**
     * @var Shot
     */
    public Shot $shot;

    /**
     * @param Texture $texture
     * @param Shot $shot
     */
    public function __construct(Texture $texture, Shot $shot)
    {
        parent::__construct();

        $this->shot = $shot;
        $this->texture = $texture;
        $this->rotation = new Rotation();
        $this->dest = $this->sdl->new(FRect::class);
    }

    /**
     * @return bool
     */
    public function isReloaded(): bool
    {
        return $this->shot->reloading === 0.0;
    }

    /**
     * @param float $x
     * @param float $y
     */
    public function aimAt(float $x, float $y): void
    {
        $this->rotation->x = $x;
        $this->rotation->y = $y;
    }

    /**
     * @param float $delta
     * @param Tank $tank
     */
    private function updateRotation(float $delta, Tank $tank): void
    {
        $this->rotation->target = \atan2(
            $this->rotation->y - $this->dest->y,
            $this->rotation->x - $this->dest->x,
        ) * 180 / \M_PI - 90;

        if ($this->rotation->target > $this->rotation->angle) {
            $this->rotation->angle += $this->rotation->speed * $delta;
        }

        if ($this->rotation->target < $this->rotation->angle) {
            $this->rotation->angle -= $this->rotation->speed * $delta;
        }
    }

    /**
     * @param float $delta
     * @param ModelInterface|null $parent
     */
    public function update(float $delta, ModelInterface $parent = null): void
    {
        \assert($parent instanceof Tank);

        $this->shot->update($delta, $this);

        // По формуле матрицы вращения
        [$dx, $dy] = Utils::rotate(
            // Позиция пушки
            $parent->gunPosition->x,
            $parent->gunPosition->y,
            // Вращается вокруг точки
            $parent->rotation->center->x,
            $parent->rotation->center->y,
            // С углом в радиусах
            $parent->rotation->angle
        );

        // Перемещение в заданные координаты
        $this->dest->x = $dx + $parent->dest->x - $this->rotation->center->x;
        $this->dest->y = $dy + $parent->dest->y - $this->rotation->center->y;

        $this->updateRotation($delta, $parent);
    }

    /**
     * @return void
     */
    public function shot(): void
    {
        $this->shot->shot();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function render(): void
    {
        $this->shot->render();

        $target = $this->vp->transform($this->dest, false);

        $this->sdl->SDL_RenderCopyExF(
            $this->renderer->ptr,
            $this->texture->ptr,
            null,
            SDL::addr($target),
            $this->rotation->angle,
            SDL::addr($this->rotation->center),
            SDL::SDL_FLIP_NONE
        );
    }
}
