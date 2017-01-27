<?php

namespace Ueef\Postbox\Exceptions {

    class RequestException extends Exception
    {
        const UNKNOWN = 300;
        const FORMAT = 301;
        const EMPTY = 302;
    }
}