<?php


namespace IslamDB\OrchidHelper\Resource\Traits;


use Illuminate\Support\Str;
use IslamDB\OrchidHelper\Actions\DeleteAction;
use ReflectionClass;

trait ResourceDeleteAction
{
    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(): array
    {
        return [
            DeleteAction::class
        ];
    }
}
