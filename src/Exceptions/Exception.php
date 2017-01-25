<?php

namespace Ueef\Postbox\Exceptions {

    class Exception extends \Exception
    {
        const CODE_NONE = 0;
        const CODE_UNKNOWN = 10;
        const CODE_ENCODER = 20;
        const CODE_ENCODER_EMPTY = 21;
        const CODE_ENCODER_FORMAT = 22;
        const CODE_REQUEST = 30;
        const CODE_REQUEST_EMPTY = 31;
        const CODE_REQUEST_FORMAT = 32;
        const CODE_RESPONSE = 40;
        const CODE_RESPONSE_EMPTY = 41;
        const CODE_RESPONSE_FORMAT = 42;
        const CODE_SERVICE = 50;
        const CODE_SERVICE_ACTION_UNDEFINED = 51;
    }
}

