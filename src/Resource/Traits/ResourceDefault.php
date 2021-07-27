<?php


namespace IslamDB\OrchidHelper\Resource\Traits;


use Orchid\Crud\Filters\DefaultSorted;

trait ResourceDefault
{
    use ResourceDefaultFilter,
        ResourceDefaultLabel,
        ResourceDefaultSortingByFilename,
        ResourceDefaultUriKey,
        ResourceDeleteAction,
        ResourceOnSave;
}
