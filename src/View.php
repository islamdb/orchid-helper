<?php


namespace IslamDB\OrchidHelper;


use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Orchid\Screen\Sight;

class View
{
    /**
     * @param string $name
     * @param string|null $title
     * @return \Orchid\Screen\Cell|Sight
     */
    public static function html(string $name, string $title = null)
    {
        return View::make($name, $title)
            ->render(function ($model) use ($name) {
                return $model->{$name};
            });
    }

    /**
     * @param string $name
     * @param string|null $title
     * @param string $column
     * @return \Orchid\Screen\Cell|Sight
     */
    public static function relation(string $name, string $title = null, $column = 'name')
    {
        return View::make($name, $title)
            ->render(function ($model) use ($column, $name) {
                $data = $model->{$name};
                if (!is_a($data, Collection::class)) {
                    $data = collect([$data]);
                }

                return $data->pluck($column)->join(', ');
            });
    }

    /**
     * @param string $name
     * @param string|null $title
     * @return \Orchid\Screen\Cell|Sight
     */
    public static function make(string $name, string $title = null)
    {
        $title = $title ?? Str::title(Str::replace('_', ' ', $name));

        return Sight::make($name, __($title));
    }

    /**
     * @param $name
     * @param null $title
     * @param array|null $labels
     * @return \Orchid\Screen\Cell|Sight
     */
    public static function boolean($name, $title = null, array $labels = null)
    {
        $labels = empty($labels)
            ? [true => __('Yes'), false => __('No')]
            : $labels;

        return static::make($name, $title)
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
     * @return \Orchid\Screen\Cell|Sight
     */
    public static function dateTime($name, $title = null, string $locale = 'id', $withTime = true, $withDayName = true)
    {
        return static::make($name, $title)
            ->render(function ($model) use ($locale, $name, $withTime, $withDayName){
                return readable_datetime($model->{$name}, $locale, $withTime, $withDayName);
            });
    }

    /**
     * @param $name
     * @param null $title
     * @return \Orchid\Screen\Cell|Sight
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
            });
    }

    /**
     * @return array
     */
    public static function meta()
    {
        return [
            static::make('meta_title'),
            static::make('meta_keywords'),
            static::make('meta_description')
        ];
    }

    /**
     * @return array
     */
    public static function timestamps()
    {
        return [
            static::dateTime('created_at'),
            static::dateTime('updated_at')
        ];
    }

    /**
     * @param array $views
     * @return array|\Orchid\Screen\Cell[]|Sight[]
     */
    public static function withMeta(array $views)
    {
        return array_merge($views, static::meta());
    }

    /**
     * @param array $views
     * @return array|\Orchid\Screen\Cell[]|Sight[]
     */
    public static function withTimestamps(array $views)
    {
        return array_merge($views, static::timestamps());
    }

    /**
     * @param array $view
     * @return array
     */
    public static function withMetaAndTimestamps(array $view)
    {
        return array_merge($view, static::meta(), static::timestamps());
    }
}
