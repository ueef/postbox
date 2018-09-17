<?php
declare(strict_types=1);

namespace Ueef\Postbox\Handlers {

    use Ueef\Postbox\Exceptions\HandlerException;
    use Ueef\Postbox\Interfaces\HandlerInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;

    class AbstractHandler implements HandlerInterface
    {
        public function handle(RequestInterface $request): array
        {
            $route = $request->getRoute();
            if (!$route) {
                throw new HandlerException("route is empty");
            }

            $action = $this->getActionName($request->getRoute());
            if (!method_exists($this, $action)) {
                throw new HandlerException(["action '%s' is undefined", $action]);
            }

            return $this->{$action}($request->getData());
        }

        protected function getActionName(array $route): string
        {
            return lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', implode(' ', $route)))));
        }
    }
}