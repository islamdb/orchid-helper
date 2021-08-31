# Introduction

Orchid Helper has class and traits that will help you a lot in development

# Installation

> The manual assumes that you already have a copy of [Laravel](https://laravel.com/docs/installation) with [Orchid](https://orchid.software/en/docs/installation/)

You can install the package using the Сomposer. Run this at the command line:
```php
composer require islamdb/orchid-helper
```
This will update `composer.json` and install the package into the `vendor/` directory.

# Traits

## ResourceDefaultAllowedSortsAndFilters
This trait will make the ```$allowedSorts``` and ```$allowedFilters``` filled. Depand on ```$fillable``` in your Model
#### Usage
```php
use IslamDB\OrchidHelper\Resource\Traits\ResourceDefaultAllowedSortsAndFilters;

class YourModel extends Model
{
    use ResourceDefaultAllowedSortsAndFilters;
    
    ...
}
```

## ResourceDefaultFilter
This trait will make default sorted in your resource
#### Usage
```php
use IslamDB\OrchidHelper\Resource\Traits\ResourceDefaultFilter;

class YourResource extends Resource
{
    use ResourceDefaultFilter;
    
    ...
}
```
You can change ```$defaultSortedColumn``` and ```$defaultSortedOrder``` in <b>construct</b>

## ResourceDefaultLabel
This trait will change the default label of your Resource name
#### Usage
```php
use IslamDB\OrchidHelper\Resource\Traits\ResourceDefaultLabel;

class YourResource extends Resource
{
    use ResourceDefaultLabel;
    
    ...
}
```
This will change from "Your Resource" to "Your". But you can change ```static $labelToReplace``` value in construct

## ResourceDefaultSortingByFilename
This trait will sort your resources by filename
#### Usage
```php
use IslamDB\OrchidHelper\Resource\Traits\ResourceDefaultSortingByFilename;

class YourResource extends Resource
{
    use ResourceDefaultSortingByFilename;
    
    ...
}
```

## ResourceDeleteAction
This trait will give default delete action
#### Usage
```php
use IslamDB\OrchidHelper\Resource\Traits\ResourceDeleteAction;

class YourResource extends Resource
{
    use ResourceDeleteAction;
    
    ...
}
```

## ResourceOnSave
This trait will help you to save your attachment and sluggable in resource
#### Usage
```php
use IslamDB\OrchidHelper\Resource\Traits\ResourceOnSave;

class PostResource extends Resource
{
    use ResourceOnSave;
    
    public function onSave(ResourceRequest $request, Model $model)
    {
        $this->sluggable($request);

        $this->saveWithAttachment($request, $model);
    }
    
    ...
}
```

## ResourceDefault
All traits before are in this trait
#### Usage
```php
use IslamDB\OrchidHelper\Resource\Traits\ResourceDefault;

class YourResource extends Resource
{
    use ResourceDefault;
    
    ...
}
```

# Field
This class is used by Orchid Setting package. You can visit <a href="https://github.com/islamdb/orchid-setting">islamdb/orchid-setting</a>
## Input Types
```php
Field::INPUT_EMAIL // 'email'
Field::INPUT_FILE // 'file'
Field::INPUT_HIDDEN // 'hidden'
Field::INPUT_MONTH // 'month'
Field::INPUT_NUMBER // 'number'
Field::INPUT_PASSWORD // 'password'
Field::INPUT_RADIO // 'radio'
Field::INPUT_RANGE // 'range'
Field::INPUT_SEARCH // 'search'
Field::INPUT_TEL // 'tel'
Field::INPUT_TEXT // 'text'
Field::INPUT_TIME // 'time'
Field::INPUT_URL // 'url'
Field::INPUT_WEEK // 'week'
```

## Required Methods
```php
\IslamDB\OrchidHelper\Field::REQUIRED_METHODS
```
#### Value
```phpt
[
    RadioButtons::class => [
        'options' => "['one' => 'One', 'two' => 'Two', 'three' => 'Three']"
    ], Range::class => [
        'min' => "1",
        'max' => "100",
        'step' => "1"
    ], Select::class => [
        'options' => "['one' => 'One', 'two' => 'Two', 'three' => 'Three']"
    ], Picture::class => [
        'targetId' => ''
    ]
]
```

## With Meta
Returne meta fields with Field params
```php
// function
public static function withMeta(array $fields)

// usage
\IslamDB\OrchidHelper\Field::withMeta([
    View::make('group'),
    View::make('slug'),
    Input::make('title'),
    View::make('body'),
    View::dateTime('published_at'),
    View::dateTime('expired_at')
])
```

## Check File Field
Check wether type is file field or not
```php
// function
public static function isFileField($type)

// usage
\IslamDB\OrchidHelper\Field::isFileField(\Orchid\Screen\Fields\Input::class) // false
```

## Get All Fields
Get all available orchid fields
```php
// function
public static function all(bool $withMethods = true, $typeClass = null)

// usage
\IslamDB\OrchidHelper\Field::all()
```

## Find Field
Find field by class name and return methods etc
```php
// function
public static function find($type, bool $withMethods = true)

// usage
\IslamDB\OrchidHelper\Field::find(\Orchid\Screen\Fields\Matrix::class)
```

## Generate Field
Generate field by class name with options (Orchid Setting)
```php
public static function make($type, string $name = 'value', array $options = [])
```

# Column

## Usage
```php
use IslamDB\OrchidHelper\Column;
```

## Make
This function will help you to make column with default title, sorting and filter
```php
public static function make($name, $title = null, bool $sorting = true, $filter = TD::FILTER_TEXT)
```
#### Example
```php
Column::make('full_name')
```
#### Output
```phpt
(column with "Full Name" column name and filter)
```

## URL
Use this function to generate url table column
```php
public static function url(string $name, string $title = null, $target = '_blank')
```
#### Example
```php
Column::url('social_media_url', null, null)
Column::url('social_media_url', null, '_blank')
```
#### Output
```phpt
1. (clickable and go to address in current tab)
2. (clickable and go to address in new tab)
```

## HTML
This function will help you to print html
```php
public static function html(string $name, string $title = null)
```
#### Example
```php
Column::html('body')
```
#### Output
<b><i>Bold Text</i></b>
```html
<b><i>Bold Text</i></b>
```

## Relation
This function will help you to print out the relation fields
```php
public static function relation(string $name, string $title = null, $columns = 'name', string $glue = ', ', string $glueColumn = ' ')
```
#### Example
Assume that you want to get users with their roles and roles are (super admin and administrator)
```php
Column::relation('roles', null, ['name', 'slug'], ', ', ' - ')
```
#### Output
```phpt
Super Admin - super-admin, Administrator - administrator
```

## Boolean
This function will help you to print out the boolean value
```php
public static function boolean($name, $title = null, array $labels = null)
```
#### Example
```php
Column::boolean('enabled', 'Is Active', [true => 'Yes', false => 'No'])
```
#### Output
```phpt
Yes/No (depand on your value)
```

## Date Time
This function will help you to print out datetime/timestamp
```php
public static function dateTime($name, $title = null, string $locale = 'id', $withTime = true, $withDayName = true)
```
#### Example
```php
Column::dateTime('updated_at', 'Last Edit', 'en', true, true)
```
#### Output
```phpt
Tuesday, August 31st 2021, 09:05:38 (depand on your value)
```

## Money/Numeric
This function will help you to print numeric/money value
```php
public static function money($name, $title = null, $decimals = 2, $zeroTrail = true, $decimalSeparator = '.', $thousandSeparator = ',')
```
#### Example
```php
1. Column::make('total', null, 4, true)
2. Column::make('total', null, 4, false)
```
#### Output
```phpt
1. 250,000.23
2. 250,000.2300
```

## Shortcut
This function will help you to view/edit resource
```php
public static function shortcut($name, $title = null, string $route = 'platform.resource.view', int $deep = 2)
```
#### Example
```php
1. Column::shortcut('name', null, 'platform.resource.view')
2. Column::shortcut('name', null, 'platform.resource.edit')
```
#### Output
```phpt
1. (clickable, and go to view page)
2. (clickable, and go to edit page)
```


# View

## Usage
```php
use IslamDB\OrchidHelper\View;
```

## Make
This function will help you to make sight with default title
```php
public static function make(string $name, string $title = null)
```
#### Example
```php
View::make('full_name')
```
#### Output
```phpt
(view with "Full Name")
```

## URL
Use this function to generate url in view page
```php
public static function url(string $name, string $title = null, string $target = '_blank')
```
#### Example
```php
1. View::url('social_media_url', null, null)
2. View::url('social_media_url', null, '_blank')
```
#### Output
```phpt
1. (clickable and go to address in current tab)
2. (clickable and go to address in new tab)
```

## HTML
This function will help you to print html
```php
public static function html(string $name, string $title = null)
```
#### Example
```php
View::html('body')
```
#### Output
<b><i>Bold Text</i></b>
```html
<b><i>Bold Text</i></b>
```

## Relation
This function will help you to print out the relation fields
```php
public static function relation(string $name, string $title = null, $columns = 'name', string $glue = ', ', string $glueColumn = ' ')
```
#### Example
```php
View::relation('roles', null, ['name', 'slug'], ', ', ' - ')
```
#### Output
```phpt
Super Admin - super-admin, Administrator - administrator
```

## Boolean
This function will help you to print out the boolean value
```php
public static function boolean($name, $title = null, array $labels = null)
```
#### Example
```php
View::boolean('enabled', 'Is Active', [true => 'Yes', false => 'No'])
```
#### Output
```phpt
Yes/No (depand on your value)
```

## Date Time
This function will help you to print out datetime/timestamp
```php
public static function dateTime($name, $title = null, string $locale = 'id', $withTime = true, $withDayName = true)
```
#### Example
```php
View::dateTime('updated_at', 'Last Edit', 'en', true, true)
```
#### Output
```phpt
Tuesday, August 31st 2021, 09:05:38 (depand on your value)
```

## Money/Numeric
This function will help you to print numeric/money value
```php
public static function money($name, $title = null, int $decimals = 2, bool $zeroTrail = true, string $decimalSeparator = '.', string $thousandSeparator = ',')
```
#### Example
Assume total = 250000.23
```php
1. View::make('total', null, 4, true)
2. View::make('total', null, 4, false)
```
#### Output
```phpt
1. 250,000.23
2. 250,000.2300
```

## Meta View
Return meta sights
```php
public static function meta(): array
{
    return [
        static::make('meta_title'),
        static::make('meta_keywords'),
        static::make('meta_description')
    ];
}
```

## Timestamps View
Return timestamp sights
```php
public static function timestamps(): array
{
    return [
        static::dateTime('created_at'),
        static::dateTime('updated_at')
    ];
}
```

## With Meta View
Return meta sights with array sight params
````php
public static function withMeta(array $views): array
{
    return array_merge($views, static::meta());
}
````

## With Timestamps
Return timestamp sights with array sight params
```php
public static function withTimestamps(array $views): array
{
    return array_merge($views, static::timestamps());
}
```

## With Meta & Timestamps
Return meta timestamp sights with array sight params
```php
public static function withMetaAndTimestamps(array $view): array
{
    return array_merge($view, static::meta(), static::timestamps());
}
```
