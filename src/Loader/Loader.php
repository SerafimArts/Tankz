<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Loader;

use App\System\Texture;
use Illuminate\Support\Arr;

abstract class Loader implements LoaderInterface
{
    /**
     * @var string
     */
    private string $resources;

    /**
     * @param string $resources
     */
    public function __construct(string $resources)
    {
        $this->resources = \realpath($resources);
    }

    /**
     * @return array|string[]
     */
    protected function required(): array
    {
        return [];
    }

    /**
     * @return Texture
     */
    protected function empty(): Texture
    {
        return Texture::fromPathname($this->pathname('missing.png'));
    }

    /**
     * @param string $file
     * @param string $resource
     * @return Texture
     */
    protected function texture(string $file, string $resource): Texture
    {
        return Texture::fromPathname($this->resource($file, $resource));
    }

    /**
     * @param string $file
     * @return string
     */
    protected function pathname(string $file): string
    {
        return $this->resources . '/' . $file;
    }

    /**
     * @param string $file
     * @param string $resource
     * @return string
     */
    protected function resource(string $file, string $resource): string
    {
        $result = \dirname($this->pathname($file)) . '/' . $resource;

        if (!\is_file($result)) {
            throw new \RuntimeException('Resource file "' . $result . '" not found');
        }

        return $result;
    }

    /**
     * @param string $file
     * @param array $required
     * @return array
     */
    protected function read(string $file, array $required = []): array
    {
        $file = $this->resources . '/' . $file;

        if (!\is_file($file)) {
            throw new \RuntimeException('File "' . $file . '" not found');
        }

        $result = \file_get_contents($file);

        try {
            $result = (array)\json_decode($result, true, 512, \JSON_THROW_ON_ERROR);

            if (!Arr::has($result, $required)) {
                throw new \RuntimeException('Broken resource file: One of required fields is missing');
            }

            return $result;
        } catch (\JsonException $e) {
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
