<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Server;

use App\Server\Protocol\SessionClosed;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;
use React\EventLoop\LoopInterface;
use React\Socket\ConnectionInterface;
use React\Socket\TcpServer;

class Server implements LoggerInterface
{
    use LoggerAwareTrait;
    use LoggerTrait;

    /**
     * @var LoopInterface
     */
    private LoopInterface $loop;

    /**
     * @var \SplObjectStorage|ConnectionInterface[]
     */
    private \SplObjectStorage $connections;

    /**
     * @var \SplObjectStorage|Session[]
     */
    private \SplObjectStorage $sessions;

    /**
     * @param LoopInterface $loop
     * @param LoggerInterface $logger
     */
    public function __construct(LoopInterface $loop, LoggerInterface $logger)
    {
        $this->loop = $loop;
        $this->logger = $logger;
        $this->connections = new \SplObjectStorage();
        $this->sessions = new \SplObjectStorage();
    }

    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    /**
     * @param string|int $uri
     */
    public function run($uri): void
    {
        $server = new TcpServer($uri, $this->loop);

        $server->on('connection', function (ConnectionInterface $connection) {
            $this->logger->info('New connection ' . $connection->getRemoteAddress());

            $this->connections->attach($connection);

            $this->check();

            $connection->on('close', $this->idle($connection));
        });

        $this->loop->run();
    }

    /**
     * @param ConnectionInterface $connection
     * @return \Closure
     */
    private function idle(ConnectionInterface $connection): \Closure
    {
        return function () use ($connection) {
            $this->closeConnection($connection);
        };
    }

    /**
     * @return void
     */
    private function check(): void
    {
        if ($this->connections->count() < 2) {
            return;
        }

        $session = [];

        while ($this->connections->count()) {
            /** @var ConnectionInterface $current */
            $session[] = $current = $this->connections->current();

            $this->connections->detach($current);

            $current->removeAllListeners();
        }

        $this->logger->info('New session', [
            'clients' => \array_map(static fn(ConnectionInterface $c) => $c->getRemoteAddress(), $session),
        ]);

        $this->sessions->attach(new Session($this, $session));
    }

    /**
     * @param ConnectionInterface $connection
     */
    public function closeConnection(ConnectionInterface $connection): void
    {
        $this->logger->info('Close connection ' . $connection->getRemoteAddress());

        try {
            $connection->close();
        } finally {
            $this->connections->detach($connection);
        }
    }

    /**
     * @param Session $session
     */
    public function closeSession(Session $session): void
    {
        $this->logger->info('Close session ', $session->getConnections());

        foreach ($session->getConnections() as $connection) {
            if ($connection->isWritable()) {
                $connection->end(new SessionClosed());

                $this->closeConnection($connection);
            }
        }

        $this->sessions->detach($session);
    }
}
