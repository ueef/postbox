<?php

namespace Ueef\Postbox\Services {

    use Ueef\Postbox\Exceptions\Exception;
    use Ueef\Postbox\Interfaces\RequestInterface;

    class AbstractHandler
    {
        public function __invoke(RequestInterface $request): array
        {
            $action = $this->getActionName($request->getRoute());

            if (!method_exists($this, $action)) {
                throw new Exception('Route is undefined', Exception::CODE_HANDLER_ROUTE_UNDEFINED);
            }

            $response = $this->{$action}($request->getData());

            if (null === $response) {
                $response = [];
            }

            if (!is_array($response)) {
                throw new Exception('Handler returns not an array', Exception::CODE_HANDLER);
            }

            return $response;
        }

        protected function getActionName(array $route): string
        {
            $route = array_slice($route, 1);
            if (!$route) {
                throw new Exception('Route is empty', Exception::CODE_HANDLER_ROUTE_EMPTY);
            }

            $suffix = $this->getActionSuffix();
            if ($suffix) {
                $route[] = $suffix;
            }

            return lcfirst(str_replace('', ' ', ucwords(str_replace(['-', '_'], ' ', implode(' ', $route)))));
        }

        protected function getActionSuffix(): string
        {
            return 'action';
        }
    }
}