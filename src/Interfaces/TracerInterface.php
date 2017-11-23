<?php

namespace Ueef\Postbox\Interfaces {

    interface TracerInterface
    {
        const TYPE_SENDING = 1;
        const TYPE_HANDLING = 2;
        const TYPE_REQUESTING = 3;

        public function spanStart(int $type, RequestInterface &$request);
        public function spanFinish(?ResponseInterface $response = null);
    }
}

