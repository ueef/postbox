<?php

namespace Ueef\Postbox\Exceptions {

    class ResponseException extends Exception
    {
        const UNKNOWN = 400;
        const FORMAT = 401;
        const EMPTY = 402;
    }
}