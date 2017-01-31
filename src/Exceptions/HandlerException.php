<?php

namespace Ueef\Postbox\Exceptions {

    class HandlerException extends Exception
    {
        const UNKNOWN = 500;
        const FORMAT = 501;
    }
}