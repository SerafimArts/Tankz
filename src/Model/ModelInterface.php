<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Model;

interface ModelInterface
{
    /**
     * @param float $delta
     * @param ModelInterface|null $parent
     */
    public function update(float $delta, ModelInterface $parent = null): void;

    /**
     * @return void
     */
    public function render(): void;
}
