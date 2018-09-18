<?php
declare(strict_types=1);

namespace Ueef\Postbox;

use Ueef\Postbox\Interfaces\RequestInterface;
use Ueef\Postbox\Interfaces\ResponseInterface;

class Response implements ResponseInterface
{
    /** @var array */
    private $data = [];

    /** @var RequestInterface */
    private $request = null;

    /** @var string */
    private $error_code = 0;

    /** @var string */
    private $error_message = '';


    public function __construct(array $data = [], ?RequestInterface $request = null, int $errorCode = 0, string $errorMessage = '')
    {
        $this->data = $data;
        $this->request = $request;
        $this->error_code = $errorCode;
        $this->error_message = $errorMessage;
    }

    public function pack(): array
    {
        return [
            'data' => $this->data,
            'request' => $this->request ? $this->request->pack() : null,
            'error_code' => $this->error_code,
            'error_message' => $this->error_message,
        ];
    }

    public function assign(array $parameters): void
    {
        foreach ($parameters as $key => $value) {
            switch ($key) {
                case 'data':
                    if (is_array($value)) {
                        $this->{$key} = $value;
                    }
                    break;
                case 'request':
                    $this->request = new Request();
                    $this->request->assign($value);
                    break;
                case 'error_code':
                case 'error_message':
                    $this->{$key} = (string) $value;
                    break;
            }
        }
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getErrorCode(): string
    {
        return $this->error_code;
    }

    public function getErrorMessage(): string
    {
        return $this->error_message;
    }
}