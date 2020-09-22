<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Server;

use App\Server\Protocol\Message;

class Buffer
{
    /**
     * @var string
     */
    private string $data = '';

    /**
     * @var int
     */
    private int $depth = 0;

    /**
     * @var \Closure
     */
    private \Closure $handler;

    /**
     * @param \Closure $handler
     */
    public function __construct(\Closure $handler)
    {
        $this->handler = $handler;
    }

    /**
     * @param string $chunk
     */
    public function write(string $chunk): void
    {
        $length = \strlen($chunk);

        for ($i = 0; $i < $length; ++$i) {
            $char = $chunk[$i];
            $this->data .= $char;

            switch ($char) {
                case '{':
                    $this->depth++;
                    break;

                case '}':
                    $this->depth--;
                    break;
            }

            if ($this->depth === 0) {
                try {
                    $this->emit($this->data);
                } finally {
                    $this->data = '';
                }
            }
        }
    }

    /**
     * @param string $message
     * @throws \JsonException
     */
    private function emit(string $message): void
    {
        $data = \json_decode($message, true, 10, \JSON_THROW_ON_ERROR);

        if (! isset($data['id'], $data['type'], $data['data'])) {
            throw new \RuntimeException('Bad message type');
        }

        $isValidType = \class_exists($data['type']) &&
            \is_subclass_of($data['type'], Message::class)
        ;

        if (! $isValidType) {
            throw new \RuntimeException('Invalid message type');
        }

        $object = new $data['type']($data['data']);
        $object->id = (int)$data['id'];

        ($this->handler)($object);
    }
}
