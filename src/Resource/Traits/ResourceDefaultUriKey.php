<?php


namespace IslamDB\OrchidHelper\Resource\Traits;


use Illuminate\Support\Str;
use ReflectionClass;

trait ResourceDefaultUriKey
{
    /**
     * Remove this from uri
     *
     * @var string
     */
    static $uriToReplace = '-resource';

    /**
     * Get the URI key for the resource.
     *
     * @return string
     */
    public static function uriKey(): string
    {
        $name = (new ReflectionClass(static::class))->getShortName();
        $name = Str::snake($name, '-');
        $name = Str::replace(static::$uriToReplace, '', $name);
        $name = __($name);

        return $name;
    }
}
