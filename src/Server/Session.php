<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Server;

use App\Server\Protocol\CreatePlayerTank;
use App\Server\Protocol\CreateTank;
use App\Server\Protocol\Message;
use App\Server\Protocol\SessionEstablish;
use React\Socket\ConnectionInterface;

class Session
{
    /**
     * @var ConnectionInterface[]
     */
    private array $connections;

    /**
     * @var Server
     */
    private Server $server;

    /**
     * @var bool
     */
    private bool $closed = false;

    /**
     * @param Server $server
     * @param array $connections
     */
    public function __construct(Server $server, array $connections)
    {
        $this->server = $server;
        $this->connections = $connections;

        $this->listenConnectionStatuses();
        $this->listenConnections();

        $this->notify(new SessionEstablish([
            'connections' => \count($this->connections)
        ]));

        $this->handshake();
    }

    /**
     * @return void
     */
    private function listenConnectionStatuses(): void
    {
        foreach ($this->connections as $connection) {
            $connection->on('close', function () {
                if ($this->closed === false) {
                    $this->closed = true;
                    $this->server->closeSession($this);
                }
            });
        }
    }

    /**
     * @return void
     */
    private function listenConnections(): void
    {
        foreach ($this->connections as $connection) {
            $this->listenConnection($connection);
        }
    }

    /**
     * @param ConnectionInterface $connection
     */
    private function listenConnection(ConnectionInterface $connection): void
    {
        $connection->on('data', function (string $message) use ($connection) {
            foreach ($this->connections as $another) {
                if ($connection === $another) {
                    continue;
                }

                /** @noinspection DisconnectedForeachInstructionInspection */
                //$this->server->info('Received message', [
                //    'conn'    => $connection->getRemoteAddress(),
                //    'message' => $message,
                //]);

                $another->write($message);
            }
        });
    }

    /**
     * @param string|Message $message
     */
    private function notify($message): void
    {
        foreach ($this->connections as $connection) {
            $connection->write($message);
        }
    }

    /**
     * @throws \Exception
     */
    private function handshake(): void
    {
        $init = new Initializer();

        foreach ($this->connections as $i => $connection) {
            $config = [
                'id'       => $i + 1,
                'gun'      => $init->getGunName(),
                'tank'     => $init->getTankName(),
                'position' => [
                    'x' => \random_int(0, 1920),
                    'y' => \random_int(0, 1080),
                ],
                'rotation' => \random_int(0, 360),
            ];

            $connection->write(new CreatePlayerTank($config));
            $this->notifyExcept(new CreateTank($config), $connection);
        }
    }

    /**
     * @param string|Message $message
     * @param ConnectionInterface $connection
     */
    private function notifyExcept($message, ConnectionInterface $connection): void
    {
        foreach ($this->connections as $needle) {
            if ($needle === $connection) {
                continue;
            }

            $needle->write($message);
        }
    }

    /**
     * @return iterable|ConnectionInterface[]
     */
    public function getConnections(): iterable
    {
        return $this->connections;
    }
}
