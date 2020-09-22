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

class Connection
{
    /**
     * @var int
     */
    private const DEFAULT_STREAM_FLAGS = \STREAM_CLIENT_CONNECT | \STREAM_CLIENT_ASYNC_CONNECT;

    /**
     * @var resource
     */
    private $connection;

    /**
     * @var array|\Closure[]
     */
    private array $handlers = [];

    /**
     * @var Buffer
     */
    private Buffer $buffer;

    /**
     * @param string $uri
     * @param array $ctx
     */
    public function __construct(string $uri, array $ctx = [])
    {
        $this->buffer = new Buffer(function (Message $data) {
            foreach ($this->handlers as $handler) {
                $handler($data);
            }
        });

        $this->connection = $this->connect($uri, \stream_context_create([
            'socket' => $ctx
        ]));

        \stream_set_blocking($this->connection, false);
    }

    /**
     * @param $message
     */
    public function write($message): void
    {
        \error_clear_last();
        \fwrite($this->connection, (string)$message);

        if (($error = \error_get_last()) !== null) {
            throw new \RuntimeException($error['message']);
        }
    }

    /**
     * @param \Closure $then
     */
    public function onMessage(\Closure $then): void
    {
        $this->handlers[] = $then;
    }

    /**
     * @return void
     */
    public function tick(): void
    {
        $readable = [$this->connection];
        $writable = [];
        $except = [];

        \stream_select($readable, $writable, $except, 0, 0);

        if ($readable) {
            foreach ($readable as $stream) {
                $result = \fread($stream, 1024);

                if (! \is_string($result)) {
                    continue;
                }

                $this->buffer->write($result);
            }
        }
    }

    /**
     * @param string $uri
     * @param $ctx
     * @return false|resource
     */
    private function connect(string $uri, $ctx)
    {
        $uri = 'tcp://' . $uri;
        $flags = self::DEFAULT_STREAM_FLAGS;

        $socket = @\stream_socket_client($uri, $code, $error, 0, $flags, $ctx);

        if ($error !== '') {
            throw new \RuntimeException($error);
        }

        return $socket;
    }
}
