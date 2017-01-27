<?php

namespace Ueef\Postbox\Exceptions {

    class Exception extends \Exception
    {
        const NONE = 0;
        const UNKNOWN = 100;
        const ENCODER = 200;
        const ENCODER_EMPTY = 210;
        const ENCODER_FORMAT = 220;
        const REQUEST = 300;
        const REQUEST_EMPTY = 310;
        const REQUEST_FORMAT = 320;
        const RESPONSE = 400;
        const RESPONSE_EMPTY = 410;
        const RESPONSE_FORMAT = 420;
        const HANDLER = 500;
        const HANDLER_ROUTE = 510;
        const HANDLER_ROUTE_EMPTY = 511;
        const HANDLER_ROUTE_UNDEFINED = 512;
    }
}

