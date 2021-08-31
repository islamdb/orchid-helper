<?php


namespace IslamDB\OrchidHelper;


use Illuminate\Support\Str;
use IslamDB\OrchidHelper\Traits\Type;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\TD;

class Column
{
    use Type {
        dateTime as _dateTime;
        money as _money;
    }

    /**
     * Use this function to generate url table column
     * example :
     * 1. Column::url('social_media_url', null, null)
     * 2. Column::url('social_media_url', null, '_blank')
     * output :
     * 1. (clickable and go to address in current tab)
     * 2. (clickable and go to address in new tab)
     *
     * @param string $name
     * @param string|null $title
     * @param string $target
     * @return \Orchid\Screen\Cell|TD
     */

    /**
     * This function will help you to print html
     * example :
     * Column::html('body')
     * output :
     * <b>Bold Text</b> (in html)
     *
     * @param string $name
     * @param string|null $title
     * @return \Orchid\Screen\Cell|TD
     */

    /**
     * This function will help you to print out the relation fields
     * example :
     * assume that you want to get users with their roles
     * and roles are (super admin and administrator)
     * Column::relation('roles', null, ['name', 'slug'], ', ', ' - ')
     * output :
     * Super Admin - super-admin, Administrator - administrator
     *
     * @param string $name
     * @param string|null $title
     * @param string $columns
     * @param string $glue
     * @param string $glueColumn
     * @return \Orchid\Screen\Cell|TD
     */

    /**
     * This function will help you to print out the boolean value
     * example :
     * Column::boolean('enabled', 'Is Active', [true => 'Yes', false => 'No'])
     * output :
     * Yes/No (depand on your value)
     *
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
     * This function will help you to print out datetime/timestamp
     * example :
     * Column::dateTime('updated_at', 'Last Edit', 'en', true, true)
     * output :
     * Tuesday, August 31st 2021, 09:05:38 (depand on your value)
     *
     * @param $name
     * @param null $title
     * @param string $locale
     * @param bool $withTime
     * @param bool $withDayName
     * @return TD
     */
    public static function dateTime($name, $title = null, string $locale = 'id', bool $withTime = true, bool $withDayName = true): TD
    {
        return static::_dateTime($name, $title, $locale, $withTime, $withDayName)
            ->filter(TD::FILTER_DATE);
    }

    /**
     * This function will help you to print numeric/money value
     * example :
     * assume total = 250000.23
     * 1. Column::make('total', null, 4, true)
     * 2. Column::make('total', null, 4, false)
     * output :
     * 1. 250,000.23
     * 2. 250,000.2300
     *
     * @param $name
     * @param null $title
     * @param int $decimals
     * @param bool $zeroTrail
     * @param string $decimalSeparator
     * @param string $thousandSeparator
     * @return TD
     */
    public static function money($name, $title = null, int $decimals = 2, bool $zeroTrail = true, string $decimalSeparator = '.', string $thousandSeparator = ','): TD
    {
        return static::_money($name, $title, $decimals, $zeroTrail, $decimalSeparator, $thousandSeparator)
            ->filter(TD::FILTER_NUMERIC);
    }

    /**
     * This function will help you to make column with default title, sorting and filter
     * example :
     * Column::make('full_name')
     * output :
     * (column with "Full Name" column name and filter)
     *
     * @param $name
     * @param null $title
     * @param bool $sorting
     * @param string $filter
     * @return \Orchid\Screen\Cell|TD
     */
    public static function make($name, $title = null, bool $sorting = true, string $filter = TD::FILTER_TEXT)
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
     * This function will help you to view/edit resource
     * example :
     * 1. Column::shortcut('name', null, 'platform.resource.view')
     * 2. Column::shortcut('name', null, 'platform.resource.edit')
     * output :
     * 1. (clickable, and go to view page)
     * 2. (clickable, and go to edit page)
     *
     * @param $name
     * @param null $title
     * @param string $route
     * @param int $deep
     * @return \Orchid\Screen\Cell|TD
     */
    public static function shortcut($name, $title = null, string $route = 'platform.resource.view', int $deep = 2)
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

    /**
     * Timestamp array
     *
     * @return array
     */
    public static function timestamps(): array
    {
        return [
            static::dateTime('created_at'),
            static::dateTime('updated_at')
        ];
    }

    /**
     * Set column with timestamp
     *
     * @param array $views
     * @return array
     */
    public static function withTimestamps(array $views): array
    {
        return array_merge($views, static::timestamps());
    }
}
