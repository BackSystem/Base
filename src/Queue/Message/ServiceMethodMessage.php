<?php

namespace BackSystem\Base\Queue\Message;

class ServiceMethodMessage
{
    /**
     * @param array<int, mixed> $params
     */
    public function __construct(private readonly string $serviceName, private readonly string $method, private readonly array $params = [])
    {
    }

    public function getServiceName(): string
    {
        return $this->serviceName;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<int, mixed>
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
