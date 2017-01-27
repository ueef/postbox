<?php

namespace Ueef\Postbox\Handlers {

    use Ueef\Postbox\Exceptions\Exception;
    use Ueef\Postbox\Interfaces\HandlerInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;

    class AbstractHandler implements HandlerInterface
    {
        public function __invoke(RequestInterface $request)
        {
            $action = $this->getActionName($request->getRoute());

            if (!method_exists($this, $action)) {
                throw new Exception('Route "' . $action . '" is undefined', Exception::HANDLER_ROUTE_UNDEFINED);
            }

            return $this->{$action}($request->getData());
        }

        protected function getActionName(array $route): string
        {
            $route = array_slice($route, 1);
            if (!$route) {
                throw new Exception('Route is empty', Exception::HANDLER_ROUTE_EMPTY);
            }

            return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', implode(' ', $route)))));
        }
    }
}