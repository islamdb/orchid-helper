<?php

namespace IslamDB\OrchidHelper\Traits;

use Illuminate\Database\Eloquent\Collection;
use IslamDB\OrchidHelper\Helper;
use Orchid\Screen\Actions\Link;

trait Type
{
    /**
     * @param string $name
     * @param string|null $title
     * @param string $target
     * @return mixed
     */
    public static function url(string $name, string $title = null, string $target = '_blank')
    {
        return static::make($name, $title)
            ->render(function ($model) use ($name, $target) {
                $link = Link::make($model->{$name})
                    ->href($model->{$name});

                return empty($target)
                    ? $link
                    : $link->target($target);
            });
    }

    /**
     * @param string $name
     * @param string|null $title
     * @return mixed
     */
    public static function html(string $name, string $title = null)
    {
        return static::make($name, $title)
            ->render(function ($model) use ($name) {
                return $model->{$name};
            });
    }

    /**
     * @param string $name
     * @param string|null $title
     * @param string $columns
     * @param string $glue
     * @param string $glueColumn
     * @return mixed
     */
    public static function relation(string $name, string $title = null, $columns = 'name', string $glue = ', ', string $glueColumn = ' ')
    {
        $columns = is_array($columns)
            ? $columns
            : [$columns];

        return static::make($name, $title, false, null)
            ->render(function ($model) use ($columns, $name, $glue, $glueColumn) {
                try {
                    $data = $model->{$name};

                    if (!empty($data)) {
                        if (!is_a($data, Collection::class)) {
                            $data = collect([$data]);
                        }

                        $data = $data->map(function ($row) use ($columns, $glueColumn, $model) {
                            $row = $row->toArray();
                            $row['_'] = '';
                            foreach ($columns as $column) {
                                if (isset($row[$column])) {
                                    $row['_'] .= $row[$column].$glueColumn;
                                }
                            }
                            $row['_'] = substr($row['_'], 0, -strlen($glueColumn));

                            return $row;
                        });

                        return $data->pluck('_')->join($glue);
                    }

                    return '';
                } catch (\Throwable $e) {
                    return $e->getMessage();
                }
            });
    }

    /**
     * @param $name
     * @param null $title
     * @param string $locale
     * @param bool $withTime
     * @param bool $withDayName
     * @return mixed
     */
    public static function dateTime($name, $title = null, string $locale = 'id', bool $withTime = true, bool $withDayName = true)
    {
        return static::make($name, $title)
            ->render(function ($model) use ($locale, $name, $withTime, $withDayName){
                return Helper::readableDatetime($model->{$name}, $locale, $withTime, $withDayName);
            });
    }

    /**
     * @param $name
     * @param null $title
     * @param int $decimals
     * @param bool $zeroTrail
     * @param string $decimalSeparator
     * @param string $thousandSeparator
     * @return mixed
     */
    public static function money($name, $title = null, int $decimals = 2, bool $zeroTrail = true, string $decimalSeparator = '.', string $thousandSeparator = ',')
    {
        return static::make($name, $title)
            ->render(function ($model) use ($name, $decimalSeparator, $thousandSeparator, $zeroTrail, $decimals) {
                $numeric = number_format(
                    $model->{$name},
                    $decimals,
                    $decimalSeparator,
                    $thousandSeparator
                );

                if ($zeroTrail) {
                    $numeric = rtrim($numeric, '0');
                    $numeric = rtrim($numeric, $decimalSeparator);
                }

                return $numeric;
            });
    }

}
