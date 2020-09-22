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
use App\Model\Shot\Rotation;
use App\System\Texture;
use FFI\CData;
use Serafim\SDL\FRect;
use Serafim\SDL\Kernel\Video\BlendMode;
use Serafim\SDL\Rect;
use Serafim\SDL\SDL;

class Shot extends Model
{
    /**
     * @var float
     */
    public const ANIMATION_SPEED = 0.05;

    /**
     * @var float
     */
    public float $speed = .3;

    /**
     * @var float
     */
    public float $reloading = 0;

    /**
     * @var float
     */
    public float $animation = 0;

    /**
     * @var Texture
     */
    private Texture $texture;

    /**
     * @var CData|Rect|FRect
     */
    public CData $dest;

    /**
     * @var CData|Rect|FRect
     */
    private CData $destModified;

    /**
     * @var Rotation
     */
    public Rotation $rotation;

    /**
     * @param Texture $texture
     */
    public function __construct(Texture $texture)
    {
        parent::__construct();

        $this->texture = $texture;

        $this->dest = $this->sdl->new(FRect::class);
        $this->destModified = $this->sdl->new(FRect::class);
        $this->rotation = new Rotation();
    }

    /**
     * @return void
     */
    public function shot(): void
    {
        if ($this->reloading > 0) {
            return;
        }

        $this->reloading = $this->speed;
        $this->animation = self::ANIMATION_SPEED;
    }

    /**
     * @param float $delta
     * @param ModelInterface|null $parent
     */
    public function update(float $delta, ModelInterface $parent = null): void
    {
        \assert($parent instanceof Gun);

        $this->rotation->center->x = $this->dest->w / 2;
        $this->rotation->center->y = 0;

        $this->updateAnimation($delta);
        $this->updateReloading($delta);

        [$x, $y] = Utils::rotate(
            // Вращение
            0,
            52,
            // Вокруг
            0,
            12,
            //
            $parent->rotation->angle,
        );

        $this->dest->x = $x + $parent->dest->x;
        $this->dest->y = $y + $parent->dest->y;

        $this->rotation->angle = $parent->rotation->angle;
    }

    /**
     * @param float $delta
     */
    private function updateAnimation(float $delta): void
    {
        if ($this->animation > 0) {
            $this->animation -= $delta;
        }

        if ($this->animation < 0) {
            $this->animation = 0;
        }
    }

    /**
     * @param float $delta
     */
    private function updateReloading(float $delta): void
    {
        if ($this->reloading > 0) {
            $this->reloading -= $delta;
        }

        if ($this->reloading < 0) {
            $this->reloading = 0;
        }
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function render(): void
    {
        if ($this->animation <= 0) {
            return;
        }

        $deltaByte = $this->animation / self::ANIMATION_SPEED * 255;

        $this->destModified->x = $this->dest->x;
        $this->destModified->y = $this->dest->y;
        $this->destModified->w = $this->dest->w;
        $this->destModified->h = $this->dest->h + $deltaByte / 5;

        $target = $this->vp->transform($this->destModified, false);

        $this->sdl->SDL_SetTextureBlendMode($this->texture->ptr, BlendMode::SDL_BLENDMODE_ADD);
        $this->sdl->SDL_SetTextureAlphaMod($this->texture->ptr, $deltaByte);
        $this->sdl->SDL_SetTextureColorMod($this->texture->ptr, 255, (int)$deltaByte, (int)$deltaByte);

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
