<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Model;

use App\System\Kernel;

abstract class Model implements ModelInterface
{
    use Kernel;

    /**
     * @return void
     */
    public function __construct()
    {
        $this->bootKernel();
    }
}
