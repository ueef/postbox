<?php

namespace Ueef\Postbox {

    use Ueef\Postbox\Exceptions\Exception;
    use Ueef\Postbox\Exceptions\RequestException;
    use Ueef\Postbox\Exceptions\ResponseException;
    use Ueef\Postbox\Interfaces\EncoderInterface;
    use Ueef\Postbox\Interfaces\RequestInterface;
    use Ueef\Postbox\Interfaces\ResponseInterface;
    use Ueef\Postbox\Interfaces\EnvelopeInterface;
    use Ueef\Assignable\Traits\AssignableTrait;
    use Ueef\Assignable\Interfaces\AssignableInterface;

    class Envelope implements AssignableInterface, EnvelopeInterface
    {
        use AssignableTrait;

        /**
         * @var EncoderInterface
         */
        private $encoder;


        public function makeRequest(RequestInterface $request): string
        {
            return $this->encoder->encode([
                'request' => implode(':', $request->getRoute()),
                'data' => $request->getData(),
                'trace_id' => $request->getTraceId(),
                'span_id' => $request->getSpanId(),
                'parent_span_id' => $request->getParentSpanId()
            ]);
        }

        public function parseRequest(string $message): RequestInterface
        {
            $message = $this->encoder->decode($message);

            if (!$message) {
                throw new RequestException('Request is empty', RequestException::EMPTY);
            }

            if (!$this->validateRequest($message)) {
                throw new RequestException('Wrong request format', RequestException::FORMAT);
            }

            $route = explode(':', $message['request']);

            return new Request([
                'route' => $route,
                'queue' => reset($route),
                'data' => $message['data'],
                'traceId' => $message['trace_id'],
                'spanId' => $message['span_id'],
                'parentSpanId' => $message['parent_span_id']
            ]);
        }

        public function makeResponse(ResponseInterface $response): string
        {
            $data = $response->getData();

            // в ответе поле request всегда должно быть объектом
            if (!$data) {
                $data = (object) [];
            }

            return $this->encoder->encode([
                'request' => implode(':', $response->getRoute()),
                'response' => $data,
                'error' => [
                    'code' => $response->getErrorCode(),
                    'message' => $response->getErrorMessage(),
                ],
            ]);
        }

        public function parseResponse(string $message): ResponseInterface
        {
            $message = $this->encoder->decode($message);

            if (!$message) {
                throw new ResponseException('Response is empty', ResponseException::EMPTY);
            }

            if (!$this->validateResponse($message)) {
                throw new ResponseException('Wrong response format', ResponseException::FORMAT);
            }

            $route = explode(':', $message['request']);

            return new Response([
                'route' => $route,
                'data' => $message['response'],
                'error_code' => $message['error']['code'],
                'error_message' => $message['error']['message'],
            ]);
        }

        private function validateRequest(array $message)
        {
            return !array_diff(['request', 'data'], array_keys($message));
        }

        private function validateResponse(array $message)
        {
            return !array_diff(['request', 'response', 'error'], array_keys($message));
        }
    }
}

