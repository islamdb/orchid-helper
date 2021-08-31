<?php


namespace IslamDB\OrchidHelper\Resource\Traits;



trait ResourceDefaultAllowedSortsAndFilters
{
    /**
     * Name of columns to which http sorting can be applied
     *
     * @var array
     */
    protected $allowedSorts = [];

    /**
     * Name of columns to which http filtering can be applied
     *
     * @var array
     */
    protected $allowedFilters = [];

    /**
     * Merge allowed sorts and filters
     *
     * @param string[] $columnAdditions
     */
    protected function setAllowedSortsAndFilters(array $columnAdditions = ['created_at', 'updated_at'])
    {
        $this->allowedSorts = array_merge(
            $this->fillable,
            $columnAdditions
        );

        $this->allowedFilters = array_merge(
            $this->fillable,
            $columnAdditions
        );
    }

    /**
     * Autoload when class loaded
     *
     * ResourceDefaultAllowedSortsAndFilters constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setAllowedSortsAndFilters();
    }
}
