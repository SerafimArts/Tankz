<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\Controller;

use App\Loader\GunLoader;
use App\Loader\TankLoader;
use App\Model\Tank;
use App\Model\Tank\State;
use App\Server\Connection;
use App\Server\Protocol\CreatePlayerTank;
use App\Server\Protocol\CreateTank;
use App\Server\Protocol\Message;
use App\Server\Protocol\SessionEstablish;
use App\Server\Protocol\Shot;
use App\Server\Protocol\UpdateTank;
use App\System\Texture;
use App\Ui\UserInterface;
use FFI\CData;
use Serafim\SDL\Event;
use Serafim\SDL\Kernel\Keyboard\ScanCode;
use Serafim\SDL\KeyboardEvent;
use Serafim\SDL\MouseMotionEvent;
use Serafim\SDL\SDL;

class GameController extends Controller
{
    /**
     * @var string
     */
    private const RESOURCES_DIR = __DIR__ . '/../../resources';

    /**
     * @var TankLoader
     */
    private TankLoader $tanks;

    /**
     * @var GunLoader
     */
    private GunLoader $guns;

    /**
     * @var Texture
     */
    private Texture $bg;

    /**
     * @var UserInterface|null
     */
    private ?UserInterface $map = null;

    /**
     * @var Connection|null
     */
    private ?Connection $connection = null;

    /**
     * @var array|Tank[]
     */
    private array $objects = [];

    /**
     * @var Tank|null
     */
    private ?Tank $tank = null;

    /**
     * @var int|null
     */
    private ?int $id = null;

    /**
     * @param string $uri
     */
    public function __construct(string $uri)
    {
        $this->tanks = new TankLoader(self::RESOURCES_DIR);
        $this->guns = new GunLoader(self::RESOURCES_DIR);
        $this->bg = Texture::fromPathname(self::RESOURCES_DIR . '/bg.png');
        $this->map = new UserInterface();

        parent::__construct();

        //$this->connection = $this->connect($uri);
        $this->offline();
    }

    /**
     * @return void
     */
    private function offline(): void
    {
        $this->tank = $this->createTank(
            'tank/black/tank.json',
            'gun/1/gun.json'
        );

        $this->map->addPlayerTank($this->tank);

        $this->objects[] = $this->tank;
    }

    /**
     * @param $message
     */
    private function send($message): void
    {
        if ($this->connection) {
            $this->connection->write($message);
        }
    }

    /**
     * @param string $tank
     * @param string $gun
     * @return Tank
     */
    private function createTank(string $tank, string $gun): Tank
    {
        return $this->tanks->load($tank, $this->guns->load($gun));
    }

    /**
     * @param string $uri
     * @return Connection
     */
    private function connect(string $uri): Connection
    {
        $connection = new Connection($uri);
        $connection->onMessage(function (Message $message) {
            switch (true) {
                case $message instanceof UpdateTank:
                    $this->onUpdateTank($message);
                    break;

                case $message instanceof CreateTank:
                    $this->onCreateTank($message);
                    break;

                case $message instanceof Shot:
                    $this->onShot($message);
                    break;

                case $message instanceof SessionEstablish:
                    break;
            }
        });

        return $connection;
    }

    /**
     * @param UpdateTank $cmd
     */
    private function onUpdateTank(UpdateTank $cmd): void
    {
        $tank = $this->objects[$cmd['id']];
        $tank->dest->x = $cmd['pos-x'];
        $tank->dest->y = $cmd['pos-y'];
        $tank->velocity->x = $cmd['vel-x'];
        $tank->velocity->y = $cmd['vel-y'];
        $tank->rotation->angle = (float)$cmd['angle'];
        $tank->gun->rotation->angle = $tank->gun->rotation->target
            = (float)$cmd['gun'];
    }

    /**
     * @param CreateTank $cmd
     */
    private function onCreateTank(CreateTank $cmd): void
    {
        $tank = $this->createTank($cmd['tank'], $cmd['gun']);

        if ($cmd instanceof CreatePlayerTank) {
            $this->id = $cmd['id'];
            $this->tank = $tank;
            $this->map->addPlayerTank($tank);
        } else {
            $this->map->addEnemyTank($tank);
        }

        $tank->dest->x = $cmd['position.x'];
        $tank->dest->y = $cmd['position.y'];
        $tank->rotation->angle = $cmd['rotation'];

        $this->objects[$cmd['id']] = $tank;
    }

    /**
     * @param CData|Event $event
     */
    public function handle(CData $event): void
    {
        switch ($event->type) {
            case SDL::SDL_MOUSEMOTION:
                $this->onMouseMove($event->motion);
                break;

            case SDL::SDL_KEYDOWN:
                $this->onKeyDown($event->key);
                break;

            case SDL::SDL_KEYUP:
                $this->onKeyUp($event->key);
                break;

            case SDL::SDL_QUIT:
                $this->loop->stop();
                break;
        }
    }

    /**
     * @param CData|MouseMotionEvent $event
     */
    public function onMouseMove(CData $event): void
    {
        if ($this->tank) {
            $this->tank->aimAt($event->x, $event->y);
        }
    }

    /**
     * @return void
     */
    private function shoot(): void
    {
        $this->tank->shot();

        $this->send(new Shot(['id' => $this->id]));
    }

    /**
     * @param CData|KeyboardEvent $event
     */
    private function onKeyDown(CData $event): void
    {
        if (!$this->tank) {
            return;
        }

        switch ($event->keysym->scancode) {
            case ScanCode::SDL_SCANCODE_SPACE:
                $this->shoot();
                break;

            case ScanCode::SDL_SCANCODE_DOWN:
            case ScanCode::SDL_SCANCODE_S:
                $this->tank->backward();
                break;

            case ScanCode::SDL_SCANCODE_UP:
            case ScanCode::SDL_SCANCODE_W:
                $this->tank->forward();
                break;

            case ScanCode::SDL_SCANCODE_RIGHT:
            case ScanCode::SDL_SCANCODE_D:
                $this->tank->toRight();
                break;

            case ScanCode::SDL_SCANCODE_LEFT:
            case ScanCode::SDL_SCANCODE_A:
                $this->tank->toLeft();
                break;
        }
    }

    /**
     * @param CData|KeyboardEvent $event
     */
    private function onKeyUp(CData $event): void
    {
        if (!$this->tank) {
            return;
        }

        switch ($event->keysym->scancode) {
            case ScanCode::SDL_SCANCODE_SPACE:
                $this->tank->state &= ~State::STATE_SHOOT;
                break;

            case ScanCode::SDL_SCANCODE_D:
            case ScanCode::SDL_SCANCODE_RIGHT:

            case ScanCode::SDL_SCANCODE_A:
            case ScanCode::SDL_SCANCODE_LEFT:
                $this->tank->state &= ~State::STATE_ROTATED;
                break;

            case ScanCode::SDL_SCANCODE_W:
            case ScanCode::SDL_SCANCODE_UP:

            case ScanCode::SDL_SCANCODE_S:
            case ScanCode::SDL_SCANCODE_DOWN:
                $this->tank->state &= ~State::STATE_MOVED;
                break;
        }
    }

    /**
     * @param float $delta
     */
    public function update(float $delta): void
    {
        if ($this->connection) {
            $this->connection->tick();
        }

        foreach ($this->objects as $tank) {
            $tank->update($delta);
            $tank->limit(1920, 1080);
        }

        $this->map->update();

        if (! $this->tank) {
            return;
        }

        $message = new UpdateTank([
            'id'    => $this->id,
            'pos-x' => (float)$this->tank->dest->x,
            'pos-y' => (float)$this->tank->dest->y,
            'vel-x' => (float)$this->tank->velocity->x,
            'vel-y' => (float)$this->tank->velocity->x,
            'angle' => (float)$this->tank->rotation->angle,
            'gun'   => (float)$this->tank->gun->rotation->angle,
        ]);

        $this->send($message);
    }

    /**
     * @return void
     */
    public function render(): void
    {
        $this->sdl->SDL_RenderCopy(
            $this->renderer->ptr,
            $this->bg->ptr,
            null,
            null
        );

        foreach ($this->objects as $tank) {
            $tank->render();
        }

        if ($this->map) {
            $this->map->render();
        }
    }

    /**
     * @param Shot $message
     */
    private function onShot(Shot $message): void
    {
        $tank = $this->objects[$message['id']];
        $tank->shot();
    }
}
