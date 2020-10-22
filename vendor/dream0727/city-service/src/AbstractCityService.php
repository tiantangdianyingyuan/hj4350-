<?php

namespace CityService;

use CityService\Exceptions\CityServiceException;

abstract class AbstractCityService
{
    /**
     * configs
     * @var array
     */
    protected $config = [];

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    /**
     * get config
     *
     * @param      $key
     * @param null $default
     *
     * @return string|null
     */
    protected function getConfig($key)
    {
        if(isset($this->config[$key])) {
            return $this->config[$key];
        }
        throw new CityServiceException(sprintf("config::%s is not exists.", $key));
    }

}