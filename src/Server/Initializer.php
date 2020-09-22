<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Server;

class Initializer
{
    /**
     * @var array|string[]
     */
    private array $guns = [
        'gun/1/gun.json',
        'gun/2/gun.json'
    ];

    /**
     * @var array|string[]
     */
    private array $tanks = [
        'tank/black/tank.json'
    ];

    /**
     * @return string
     * @throws \Exception
     */
    public function getGunName(): string
    {
         return $this->guns[
             \random_int(0, \count($this->guns) - 1)
         ];
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getTankName(): string
    {
        return $this->tanks[
            \random_int(0, \count($this->tanks) - 1)
        ];
    }
}
