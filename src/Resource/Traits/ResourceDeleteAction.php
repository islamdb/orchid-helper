<?php


namespace IslamDB\OrchidHelper\Resource\Traits;


use IslamDB\OrchidHelper\Resource\Actions\DeleteAction;

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
