<?php

namespace Ueef\Postbox\Handlers {

    use Ueef\Postbox\Exceptions\HandlerException;
    use Ueef\Postbox\Interfaces\HandlerInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;

    class AbstractHandler implements HandlerInterface
    {
        public function __invoke(RequestInterface $request)
        {
            $action = $this->getActionName($request->getRoute());

            if (!method_exists($this, $action)) {
                throw new HandlerException('Route "' . $action . '" is undefined');
            }

            return $this->{$action}($request->getData());
        }

        protected function getActionName(array $route): string
        {
            $route = array_slice($route, 1);
            if (!$route) {
                throw new HandlerException('Route is empty');
            }

            return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', implode(' ', $route)))));
        }
    }
}