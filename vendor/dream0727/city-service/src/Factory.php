<?php
namespace CityService;

use CityService\Exceptions\CityServiceException;

/**
 * Class Factory
 *
 * @package CityService
 */
class Factory
{
    /**
     * @param string $driver
     * @param array  $config
     *
     * @return CityServiceInterface
     * @throws CityServiceException
     */
    public static function getInstance($driver, array $config = []): CityServiceInterface
    {
        try {
            $driver = ucwords($driver);
            $class = new \ReflectionClass(__NAMESPACE__. "\\Drivers\\{$driver}\\{$driver}");
            return $class->newInstanceArgs([$config]);
        }
        catch (\ReflectionException $e)
        {
            throw new CityServiceException($e->getMessage());
        }
    }
}