<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Loader;

use App\Model\ModelInterface;

interface LoaderInterface
{
    /**
     * @param string $file
     * @return ModelInterface
     */
    public function load(string $file): ModelInterface;
}
