<?php
declare(strict_types=1);

namespace Ueef\Postbox\Exceptions {

    class HandlerException extends Exception
    {
        const UNKNOWN = 500;
        const FORMAT = 501;
    }
}