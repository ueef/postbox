<?php

namespace Ueef\Postbox {

    use Ueef\Postbox\Exceptions\Exception;
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
                'request' => implode(':', $request->getAddress()),
                'data' => $request->getData(),
            ]);
        }

        public function parseRequest(string $message): RequestInterface
        {
            $message = $this->encoder->decode($message);

            if (!$message) {
                throw new Exception('Request is empty', Exception::CODE_REQUEST_EMPTY);
            }

            if ($this->validateRequest($message)) {
                throw new Exception('Wrong request format', Exception::CODE_REQUEST_FORMAT);
            }

            $address = explode(':', $message['request']);

            return new Request([
                'address' => $address,
                'service' => reset($address),
                'data' => $message['data'],
            ]);
        }

        public function makeResponse(ResponseInterface $response): string
        {
            return $this->encoder->encode([
                'request' => implode(':', $response->getAddress()),
                'response' => $response->getData(),
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
                throw new Exception('Response is empty', Exception::CODE_RESPONSE_EMPTY);
            }

            if (!$this->validateResponse($message)) {
                throw new Exception('Wrong response format', Exception::CODE_RESPONSE_FORMAT);
            }

            $address = explode(':', $message['request']);

            return new Response([
                'address' => $address,
                'service' => reset($address),
                'data' => $message['request'],
                'error_code' => $message['error']['code'],
                'error_message' => $message['error']['message'],
            ]);
        }

        private function validateRequest(array $message)
        {
            return array_diff(['request', 'data'], array_keys($message));
        }

        private function validateResponse(array $message)
        {
            return array_diff(['request', 'response', 'error'], array_keys($message));
        }
    }
}

