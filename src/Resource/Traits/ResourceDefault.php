<?php


namespace IslamDB\OrchidHelper\Resource\Traits;



trait ResourceDefault
{
    use ResourceDefaultFilter,
        ResourceDefaultLabel,
        ResourceDefaultSortingByFilename,
        ResourceDefaultUriKey,
        ResourceDeleteAction,
        ResourceOnSave;
}
