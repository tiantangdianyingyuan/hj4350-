<?php

namespace CityService\Drivers\Wechat\Response;

use CityService\ResponseInterface;

class WechatResponse implements ResponseInterface
{
    private $result;

    public function __construct(array $result = [])
    {
        $this->result = $result;
    }

    public function getCode()
    {
        return isset($this->result['errcode']) ? $this->result['errcode'] : $this->result['resultcode'];
    }

    public function getOriginalData()
    {
        return $this->result;
    }

    public function isSuccessful(): bool
    {
        return !is_null($this->getCode()) && $this->getCode() === 0;
    }

    public function getMessage():  ? string
    {
        return isset($this->result['errmsg']) ? $this->result['errmsg'] : $this->result['resultmsg'];
    }
}
