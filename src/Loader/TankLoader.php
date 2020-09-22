<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Loader;

use App\Model\Gun;
use App\Model\Shot;
use App\Model\Tank;
use App\System\Texture;
use Illuminate\Support\Arr;

class TankLoader extends Loader
{
    /**
     * @param string $file
     * @param Gun|null $gun
     * @return Tank
     */
    public function load(string $file, Gun $gun = null): Tank
    {
        $data = $this->read($file, ['texture']);

        $tank = new Tank($this->texture($file, $data['texture']), $this->gun($gun), $this->world);
        $tank->dest->w = Arr::get($data, 'width', 100);
        $tank->dest->h = Arr::get($data, 'height', 100);

        $tank->rotation->center->x = Arr::get($data, 'center.x', $tank->dest->w / 2);
        $tank->rotation->center->y = Arr::get($data, 'center.y', $tank->dest->h / 2);

        $tank->gunPosition->x = Arr::get($data, 'gun.x', $tank->dest->w / 2);
        $tank->gunPosition->y = Arr::get($data, 'gun.y', $tank->dest->y / 2);

        return $tank;
    }

    /**
     * @param Gun|null $gun
     * @return Gun
     */
    private function gun(?Gun $gun): Gun
    {
        return $gun ?? new Gun($this->empty(), new Shot($this->empty()));
    }
}
