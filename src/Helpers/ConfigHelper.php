<?php

namespace Bl\FatooraZatca\Helpers;

use Exception;

class ConfigHelper
{
    /**
     * get the environment value.
     *
     * @return string
     */
    public static function environment()
    {
        return self::get('zatca.app.environment') ?? null;
    }

    /**
     * determine if environment is production or local for testing.
     *
     * @return bool
     */
    public static function isProduction(): bool
    {
        return self::environment() === 'production' ?? false;
    }

    /**
     * get the portal based on environment.
     *
     * @return string
     */
    public static function portal(): string
    {
        $portal = self::isProduction() ? self::get('zatca.portals.production') : self::get('zatca.portals.local');

        if(! $portal) {
            throw new Exception('You must set the portal configuration !');
        }

        return $portal;
    }

    /**
     * must allow specific environment to continue.
     *
     * @param  mixed $environment
     * @return void
     */
    public static function mustAllow(string $environment): void
    {
        if(self::environment() !== $environment) {
            throw new Exception("Your configuration must be {$environment}!");
        }
    }

    /**
     * get key from config file
     *
     * @param  string $key
     * @return mixed|null
     */
    protected static function get(string $key)
    {
        if(function_exists('config')) {
            return config($key);
        }
        elseif(function_exists('config_item')) {
            return config_item($key);
        }
        else {
            throw new Exception("Unhandeled config identifier!");
        }
    }
}
