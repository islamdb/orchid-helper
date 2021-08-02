<?php


namespace IslamDB\OrchidHelper;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\TD;

class Column
{
    /**
     * @param string $name
     * @param string|null $title
     * @return \Orchid\Screen\Cell|TD
     */
    public static function url(string $name, string $title = null)
    {
        return static::make($name, $title)
            ->render(function ($model) use ($name) {
                return Link::make($model->{$name})
                    ->href($model->{$name})->target('_blank');
            });
    }

    /**
     * @param string $name
     * @param string|null $title
     * @param string $columns
     * @param string $glue
     * @param string $glueColumn
     * @return \Orchid\Screen\Cell|TD
     */
    public static function relation(string $name, string $title = null, $columns = 'name', string $glue = ', ', string $glueColumn = ' ')
    {
        $columns = is_array($columns)
            ? $columns
            : [$columns];

        return static::make($name, $title, false, null)
            ->render(function ($model) use ($columns, $name, $glue, $glueColumn) {
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
            });
    }

    /**
     * @param $name
     * @param null $title
     * @param array $labels
     * @return \Orchid\Screen\Cell|TD
     */
    public static function boolean($name, $title = null, array $labels = null)
    {
        $title = is_null($title)
            ? Str::title(str_replace('_', ' ', $name))
            : $title;

        $labels = empty($labels)
            ? [true => __('Yes'), false => __('No')]
            : $labels;

        return TD::make($name, $title)
            ->sort()
            ->render(function ($model) use ($labels, $name) {
                return $labels[$model->{$name}];
            });
    }

    /**
     * @param $name
     * @param null $title
     * @param string $locale
     * @param bool $withTime
     * @param bool $withDayName
     * @return TD
     */
    public static function dateTime($name, $title = null, string $locale = 'id', $withTime = true, $withDayName = true)
    {
        return static::make($name, $title)
            ->render(function ($model) use ($locale, $name, $withTime, $withDayName){
                return Helper::readableDatetime($model->{$name}, $locale, $withTime, $withDayName);
            })
            ->filter(TD::FILTER_DATE);
    }

    /**
     * @param $name
     * @param null $title
     * @return \Orchid\Screen\Cell|TD
     */
    public static function money($name, $title = null)
    {
        return static::make($name, $title)
            ->render(function ($model) use ($name) {
                return number_format(
                    $model->{$name},
                    2,
                    ',',
                    '.'
                );
            })
            ->filter(TD::FILTER_NUMERIC);
    }

    /**
     * @param $name
     * @param null $title
     * @param bool $sorting
     * @param string $filter
     * @return \Orchid\Screen\Cell|TD
     */
    public static function make($name, $title = null, bool $sorting = true, $filter = TD::FILTER_TEXT)
    {
        $title = is_null($title)
            ? Str::title(str_replace('_', ' ', $name))
            : $title;

        $td = TD::make($name, $title);
        $td = $sorting
            ? $td->sort()
            : $td;
        $td = empty($filter)
            ? $td
            : $td->filter($filter);

        return $td;
    }

    /**
     * @param $name
     * @param null $title
     * @param int $deep
     * @return \Orchid\Screen\Cell|TD
     * @throws \ReflectionException
     */
    public static function shortcut($name, $title = null, $route = 'platform.resource.view', $deep = 2)
    {
        $resource = debug_backtrace(
            DEBUG_BACKTRACE_PROVIDE_OBJECT,
            $deep
        )[$deep - 1]['object'];

        return self::make($name, $title)
            ->render(function ($model) use ($resource, $name, $route) {
                return Link::make($model->{$name})
                    ->route($route, [
                        'resource' => $resource::uriKey(),
                        'id' => $model->id ?? $model->key
                    ]);
            });
    }
}
