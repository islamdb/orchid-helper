<?php


namespace IslamDB\OrchidHelper;


use Illuminate\Support\Str;
use IslamDB\OrchidHelper\Traits\Type;
use Orchid\Screen\Fields\Code;
use Orchid\Screen\Sight;

class View
{
    use Type;

    /**
     * Use this function to generate url in view page
     * example :
     * 1. View::url('social_media_url', null, null)
     * 2. View::url('social_media_url', null, '_blank')
     * output :
     * 1. (clickable and go to address in current tab)
     * 2. (clickable and go to address in new tab)
     *
     * @param string $name
     * @param string|null $title
     * @param string $target
     * @return \Orchid\Screen\Cell|Sight
     */

    /**
     * This function will help you to print html
     * example :
     * View::html('body')
     * output :
     * <b>Bold Text</b> (in html)
     *
     * @param string $name
     * @param string|null $title
     * @return \Orchid\Screen\Cell|Sight
     */

    /**
     * This function will help you to print out the relation fields
     * example :
     * assume that you want to get users with their roles
     * and roles are (super admin and administrator)
     * View::relation('roles', null, ['name', 'slug'], ', ', ' - ')
     * output :
     * Super Admin - super-admin, Administrator - administrator
     *
     * @param string $name
     * @param string|null $title
     * @param string $columns
     * @param string $glue
     * @param string $glueColumn
     * @return \Orchid\Screen\Cell|Sight
     */

    /**
     * This function will help you to make column with default title
     * example :
     * View::make('full_name')
     * output :
     * (view with "Full Name")
     *
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
     * This function will help you to print out the boolean value
     * example :
     * View::boolean('enabled', 'Is Active', [true => 'Yes', false => 'No'])
     * output :
     * Yes/No (depand on your value)
     *
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
                $index = $model->{$name} ?? '';

                if (empty($index)) {
                    return '';
                }

                return $labels[$model->{$name}];
            });
    }

    /**
     * This function will help you to print out the code/json
     * example :
     * View::json('attrs')
     * output :
     * Code Field with its value
     *
     * @param $name
     * @param null $title
     * @param array|null $labels
     * @return \Orchid\Screen\Cell|Sight
     */
    public static function code(string $name, string $title = null, $language = Code::JS)
    {
        return static::make($name, $title)
            ->render(function ($model) use ($language) {
                $json = $model->attrs ?? null;
                $json = is_string($json)
                    ? json_decode($json)
                    : $json;

                return Code::make()
                    ->language(Code::JS)
                    ->lineNumbers()
                    ->readonly()
                    ->value(json_encode($json, JSON_PRETTY_PRINT))
                    ->render()
                    ->render();
            });
    }

    public static function map(string $name, string $title = null)
    {
        return static::make($name, $title)
            ->render(function ($model) {
                $lat = $model->location['lat'] ?? 0;
                $lng = $model->location['lng'] ?? 0;

                return "<iframe width='100%' height='100%' id='mapcanvas' src='https://maps.google.com/maps?q=$lat,$lng&amp;t=&amp;z=18&amp;ie=UTF8&amp;iwloc=&amp;output=embed' frameborder='0' scrolling='no' marginheight='0' marginwidth='0'><div class='zxos8_gm'><a rel='bookmark'  href='https://downloadyou.tube/youtube-videos/'>youtube downloader</a></div><div style='overflow:hidden;'><div id='gmap_canvas' style='height:100%;width:100%;'></div></div><div><small>Powered by <a href='https://www.embedgooglemap.co.uk'>Embed Google Map</a></small></div></iframe>";
            });
    }

    /**
     * This function will help you to print out datetime/timestamp
     * example :
     * View::dateTime('updated_at', 'Last Edit', 'en', true, true)
     * output :
     * Tuesday, August 31st 2021, 09:05:38 (depand on your value)
     *
     * @param $name
     * @param null $title
     * @param string $locale
     * @param bool $withTime
     * @param bool $withDayName
     * @return \Orchid\Screen\Cell|Sight
     */

    /**
     * This function will help you to print numeric/money value
     * example :
     * assume total = 250000.23
     * 1. View::make('total', null, 4, true)
     * 2. View::make('total', null, 4, false)
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
     * @return mixed
     */

    /**
     * Meta field array
     *
     * @return array
     */
    public static function meta(): array
    {
        return [
            static::make('meta_title'),
            static::make('meta_keywords'),
            static::make('meta_description')
        ];
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
     * Set view with meta
     *
     * @param array $views
     * @return array|\Orchid\Screen\Cell[]|Sight[]
     */
    public static function withMeta(array $views): array
    {
        return array_merge($views, static::meta());
    }

    /**
     * Set view with timestamp
     *
     * @param array $views
     * @return array|\Orchid\Screen\Cell[]|Sight[]
     */
    public static function withTimestamps(array $views): array
    {
        return array_merge($views, static::timestamps());
    }

    /**
     * Set view with meta and timestamp
     *
     * @param array $view
     * @return array
     */
    public static function withMetaAndTimestamps(array $view): array
    {
        return array_merge($view, static::meta(), static::timestamps());
    }
}
