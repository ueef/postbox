<?php
declare(strict_types=1);

namespace Ueef\Postbox\Handlers {

    use Ueef\Postbox\Interfaces\RequestInterface;
    use Ueef\Postbox\Interfaces\HandlerInterface;
    use Ueef\Postbox\Exceptions\HandlerException;

    class RouterHandler implements HandlerInterface
    {
        /** @var HandlerInterface[] */
        private $handlers = [];

        /** @var HandlerInterface[] */
        private $default_handler = [];


        public function __construct(array $handlers, HandlerInterface $defaultHandler = null)
        {
            $this->handlers = $handlers;
            $this->default_handler = $defaultHandler;
        }

        public function __invoke(RequestInterface $request)
        {
            $route = array_slice($request->getRoute(), 1);

            if (!$route) {
                throw new HandlerException('unexpected end of route');
            }

            $handlerKey = array_shift($route);

            if (key_exists($handlerKey, $this->handlers)) {
                $handler = $this->handlers[$handlerKey];
            } elseif ($this->default_handler) {
                $handler = $this->default_handler;
            } else {
                throw new HandlerException(['"%s" handler is undefined', $handlerKey]);
            }

            $request = clone $request;
            $request->assign([
                'route' => $route,
            ]);

            return $handler($request);
        }
    }
}