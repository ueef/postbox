<?php

namespace Ueef\Postbox\Encoders {

    use Ueef\Postbox\Exceptions\EncoderException;
    use Ueef\Postbox\Exceptions\Exception;
    use Ueef\Postbox\Interfaces\EncoderInterface;

    class JSON implements EncoderInterface
    {
        public function encode(array $message): string
        {
            return json_encode($message, JSON_UNESCAPED_UNICODE);
        }

        public function decode(string $message): array
        {
            $message = json_decode($message, true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new EncoderException('Json error: ' . json_last_error_msg(), EncoderException::FORMAT);
            }

            return $message;
        }
    }
}