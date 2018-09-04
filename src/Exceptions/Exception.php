<?php
declare(strict_types=1);

namespace Ueef\Postbox\Exceptions {

    use Throwable;

    class Exception extends \Exception
    {
        const NONE = 0;
        const UNKNOWN = 100;

        public function __construct($message = "", $code = null, Throwable $previous = null)
        {
            if (!$code) {
                $code = static::UNKNOWN;
            }

            parent::__construct($message, $code, $previous);
        }
    }
}

