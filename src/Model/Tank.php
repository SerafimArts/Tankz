<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Model;

use App\Model\Tank\GunPosition;
use App\Model\Tank\Health;
use App\Model\Tank\Rotation;
use App\Model\Tank\State;
use App\Model\Tank\Velocity;
use App\System\Texture;
use FFI\CData;
use Serafim\SDL\FRect;
use Serafim\SDL\Rect;
use Serafim\SDL\SDL;

class Tank extends Model
{
    /**
     * @var CData|FRect|Rect|object
     */
    public CData $dest;

    /**
     * @var Velocity
     */
    public Velocity $velocity;

    /**
     * @var float
     */
    public float $friction = 1.01;

    /**
     * @var float|int
     */
    public float $speed = 300;

    /**
     * @var Health
     */
    public Health $health;

    /**
     * @var int
     */
    public int $state = 0;

    /**
     * @var Rotation
     */
    public Rotation $rotation;

    /**
     * @var GunPosition
     */
    public GunPosition $gunPosition;

    /**
     * @var Gun
     */
    public Gun $gun;

    /**
     * @var Texture
     */
    private Texture $texture;

    /**
     * @param Texture $texture
     * @param Gun $gun
     */
    public function __construct(Texture $texture, Gun $gun)
    {
        parent::__construct();

        $this->gun = $gun;
        $this->texture = $texture;
        $this->velocity = new Velocity();
        $this->rotation = new Rotation();
        $this->health = new Health();
        $this->gunPosition = new GunPosition();
        $this->dest = $this->sdl->new(FRect::class);

        $this->rotation->center->x = $this->rotation->center->y = 40;
        $this->dest->w = $this->dest->h = 80;

        $this->rotation->angle = 90;
        $this->dest->y = 1080 / 2;
        $this->dest->x = 50;
    }

    /**
     * @param float $x
     * @param float $y
     */
    public function aimAt(float $x, float $y): void
    {
        $this->gun->aimAt($x, $y);
    }

    /**
     * @param float $x
     * @param float $y
     */
    public function limit(float $x, float $y): void
    {
        if ($this->dest->x < 0) {
            $this->dest->x = 0;
        }

        if ($this->dest->y < 0) {
            $this->dest->y = 0;
        }

        if ($this->dest->x > $x - $this->dest->w) {
            $this->dest->x = $x - $this->dest->w;
        }

        if ($this->dest->y > $y - $this->dest->h) {
            $this->dest->y = $y - $this->dest->h;
        }
    }

    /**
     * @param float $delta
     * @param ModelInterface|null $parent
     */
    public function update(float $delta, ModelInterface $parent = null): void
    {
        $this->updateRotation($delta);
        $this->updatePosition($delta);

        if ($this->state & State::STATE_SHOOT) {
            $this->gun->shot();
        }

        if (!($this->state & State::STATE_ROTATED)) {
            $this->updateRotationFriction($delta);
        }

        if (!($this->state & State::STATE_MOVED)) {
            $this->updatePositionFriction($delta);
        }

        $this->gun->update($delta, $this);
    }

    /**
     * @param float $delta
     */
    private function updateRotation(float $delta): void
    {
        $this->rotation->angle += $this->rotation->velocity * $delta;

        if ($this->rotation->angle > 360) {
            $this->rotation->angle -= 360;
        }

        if ($this->rotation->angle < -360) {
            $this->rotation->angle += 360;
        }
    }

    /**
     * @param float $delta
     */
    private function updatePosition(float $delta): void
    {
        $this->dest->x += \sin(-$this->rotation->angle * \M_PI / 180) * $this->velocity->x * $delta;
        $this->dest->y += \cos(-$this->rotation->angle * \M_PI / 180) * $this->velocity->y * $delta;
    }

    /**
     * @param float $delta
     */
    private function updateRotationFriction(float $delta): void
    {
        $this->rotation->velocity /= $this->rotation->friction + $delta;

        if ($this->rotation->velocity < 1 && $this->rotation->velocity > -1) {
            $this->rotation->velocity = 0;
        }
    }

    /**
     * @param float $delta
     */
    private function updatePositionFriction(float $delta): void
    {
        $this->velocity->x /= $this->friction + $delta;
        $this->velocity->y /= $this->friction + $delta;

        if ($this->velocity->x < 1 && $this->velocity->x > -1) {
            $this->velocity->x = 0;
        }

        if ($this->velocity->y < 1 && $this->velocity->y > -1) {
            $this->velocity->y = 0;
        }
    }

    /**
     * @return void
     */
    public function render(): void
    {
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

        $this->gun->render();
    }

    /**
     * @return void
     */
    public function shot(): void
    {
        $this->state |= State::STATE_SHOOT;
    }

    /**
     * @return void
     */
    public function forward(): void
    {
        $this->state |= State::STATE_MOVED;
        $this->velocity->y = -$this->speed;
        $this->velocity->x = -$this->speed;
    }

    /**
     * @return void
     */
    public function backward(): void
    {
        $this->state |= State::STATE_MOVED;
        $this->velocity->y = $this->speed;
        $this->velocity->x = $this->speed;
    }

    /**
     * @return void
     */
    public function toRight(): void
    {
        $this->state |= State::STATE_ROTATED;
        $this->rotation->velocity = $this->rotation->speed;
    }

    /**
     * @return void
     */
    public function toLeft(): void
    {
        $this->state |= State::STATE_ROTATED;
        $this->rotation->velocity = -$this->rotation->speed;
    }
}
