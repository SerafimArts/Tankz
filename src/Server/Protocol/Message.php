<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Server\Protocol;

use Illuminate\Support\Arr;

abstract class Message implements \ArrayAccess
{
    /**
     * @var int
     */
    private static int $lastId = 0;

    /**
     * @var int
     */
    public int $id;

    /**
     * @var array
     */
    private array $data;

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        if (self::$lastId >= \PHP_INT_MAX) {
            self::$lastId = 0;
        }

        $this->id = ++self::$lastId;
        $this->data = $data;
    }

    public function offsetExists($offset)
    {
        return Arr::has($this->data, $offset);
    }

    public function offsetGet($offset)
    {
        return Arr::get($this->data, $offset);
    }

    public function offsetSet($offset, $value)
    {
        throw new \LogicException('Can not update data');
    }

    public function offsetUnset($offset)
    {
        throw new \LogicException('Can not update data');
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $options = \JSON_THROW_ON_ERROR;

        return \json_encode([
            'id'   => $this->id,
            'type' => static::class,
            'data' => $this->data,
        ], $options);
    }
}
