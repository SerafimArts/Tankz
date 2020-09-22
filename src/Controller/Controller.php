<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Controller;

use App\System\Kernel;
use FFI\CData;
use Serafim\SDL\Event;

abstract class Controller
{
    use Kernel;

    public function __construct()
    {
        $this->bootKernel();
    }

    /**
     * @param float $delta
     */
    public function update(float $delta): void
    {
    }

    /**
     * @return void
     */
    public function render(): void
    {
    }

    /**
     * @param CData|Event $event
     */
    public function handle(CData $event): void
    {
    }
}
