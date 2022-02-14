<?php

namespace IslamDB\OrchidHelper\Traits;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use IslamDB\OrchidHelper\Helper;
use Orchid\Screen\Actions\Link;

trait Type
{
    /**
     * @param string $name
     * @param string|null $title
     * @param int $limit
     * @param string $target
     * @return mixed
     * @throws \Throwable
     */
    public static function url(string $name, string $title = null, $limit = 50, string $target = '_blank')
    {
        return static::make($name, $title)
            ->render(function ($model) use ($name, $target, $limit) {
                $url = $model->{$name} ?? '';

                if (empty($url)) {
                    return '';
                }

                $link = Link::make(Str::of($model->{$name})->limit($limit))
                    ->href($url);

                return empty($target)
                    ? $link->render()->render()
                    : $link->target($target)->render()->render();
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
                $html = $model->{$name} ?? '';

                return $html;
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
                $dateTime = $model->{$name} ?? null;

                if (is_null($dateTime)) {
                    return '';
                }

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
                    $model->{$name} ?? 0,
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

    /**
     * @param string $name
     * @param string|null $title
     * @return mixed
     */
    public static function pre(string $name, string $title = null)
    {
        return static::make($name, $title)
            ->render(function ($model) use ($name) {
                $html = $model->{$name} ?? '';
                $html = "<pre style='white-space: pre-wrap'>$html</pre>";

                return $html;
            });
    }

}
