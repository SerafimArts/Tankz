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
use Illuminate\Support\Arr;

class GunLoader extends Loader
{
    /**
     * @param string $file
     * @return Gun
     */
    public function load(string $file): Gun
    {
        $data = $this->read($file, ['texture', 'shot.texture']);

        $gun = new Gun(
            $this->texture($file, $data['texture']),
            $this->loadShot($file, $data['shot'])
        );

        $gun->dest->w = Arr::get($data, 'size.width', 100);
        $gun->dest->h = Arr::get($data, 'size.height', 100);

        $gun->rotation->center->x = Arr::get($data, 'center.x', 0);
        $gun->rotation->center->y = Arr::get($data, 'center.y', 0);

        return $gun;
    }

    /**
     * @param string $file
     * @param array $data
     * @return Shot
     */
    private function loadShot(string $file, array $data): Shot
    {
        $shot = new Shot($this->texture($file, $data['texture']));

        $shot->dest->w = Arr::get($data, 'size.width', 100);
        $shot->dest->h = Arr::get($data, 'size.height', 100);

        $shot->rotation->center->x = $shot->dest->w / 2;

        return $shot;
    }
}
