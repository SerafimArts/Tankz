<?php

/**
 * This file is part of Tankz package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App;

use App\Controller\GameController;
use App\EventLoop\LoopInterface;
use App\EventLoop\OrderedEventLoop;
use App\EventLoop\WorkerInterface;
use App\System\Renderer;
use App\System\Viewport;
use App\System\Window;
use FFI\CData;
use Serafim\SDL\Image\Image;
use Serafim\SDL\SDL;
use Serafim\SDL\Support\SingletonTrait;

class Application implements WorkerInterface
{
    use SingletonTrait;

    /**
     * @var SDL
     */
    public SDL $sdl;

    /**
     * @var Image
     */
    public Image $img;

    /**
     * @var Window
     */
    public Window $window;

    /**
     * @var LoopInterface
     */
    public LoopInterface $loop;

    /**
     * @var Renderer
     */
    public Renderer $renderer;

    /**
     * @var Viewport
     */
    public Viewport $vp;

    /**
     * @var GameController|null
     */
    private ?GameController $controller = null;

    public function __construct(string $title, int $w, int $h)
    {
        self::$instance = $this;

        $this->sdl = SDL::getInstance();
        $this->sdl->SDL_Init(SDL::SDL_INIT_EVERYTHING);

        $this->img = Image::getInstance();
        $this->img->init(Image::IMG_INIT_PNG);

        $this->loop = new OrderedEventLoop();
        $this->window = new Window($title, $w, $h);
        $this->renderer = new Renderer();
        $this->vp = new Viewport($w, $h, 1920, 1080);
    }

    /**
     * @param string $uri
     * @return void
     */
    public function run(string $uri): void
    {
        $this->loop->use($this);
        $this->controller = new GameController($uri);

        $this->window->show();
        $this->loop->run(60, 60);
    }

    /**
     * @param float $delta
     */
    public function onUpdate(float $delta): void
    {
        $this->controller->update($delta);
    }

    /**
     * @param float $delta
     */
    public function onRender(float $delta): void
    {
        $this->renderer->clear();
        $this->controller->render();
        $this->renderer->present();
    }

    /**
     * @param CData $event
     */
    public function onEvent(CData $event): void
    {
        $this->controller->handle($event);
    }

    public function onPause(): void
    {
        throw new \LogicException(__METHOD__ . ' not implemented yet');
    }

    public function onResume(): void
    {
        throw new \LogicException(__METHOD__ . ' not implemented yet');
    }

    public function __destruct()
    {
        $this->sdl->SDL_Quit();
    }
}
