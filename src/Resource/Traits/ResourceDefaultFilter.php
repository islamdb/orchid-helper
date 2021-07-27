<?php


namespace IslamDB\OrchidHelper\Resource\Traits;


use Orchid\Crud\Filters\DefaultSorted;

trait ResourceDefaultFilter
{
    /**
     * @var string
     */
    protected $defaultSortedColumn = 'id';

    /**
     * @var string
     */
    protected $defaultSortedOrder = 'desc';

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(): array
    {
        return [
            new DefaultSorted($this->defaultSortedColumn, $this->defaultSortedOrder)
        ];
    }
}
