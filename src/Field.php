<?php


namespace IslamDB\OrchidHelper;


use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\RadioButtons;
use Orchid\Screen\Fields\Range;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Fields\Upload;
use Reflection;
use ReflectionClass;
use ReflectionMethod;
use SplFileInfo;

class Field
{
    // Input types
    const INPUT_EMAIL = 'email';
    const INPUT_FILE = 'file';
    const INPUT_HIDDEN = 'hidden';
    const INPUT_MONTH = 'month';
    const INPUT_NUMBER = 'number';
    const INPUT_PASSWORD = 'password';
    const INPUT_RADIO = 'radio';
    const INPUT_RANGE = 'range';
    const INPUT_SEARCH = 'search';
    const INPUT_TEL = 'tel';
    const INPUT_TEXT = 'text';
    const INPUT_TIME = 'time';
    const INPUT_URL = 'url';
    const INPUT_WEEK = 'week';

    /**
     * Required methods
     */
    const REQUIRED_METHODS = [
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
    ];

    /**
     * File fields
     */
    const FILE_FIELDS = [
        Upload::class,
        Picture::class,
        Cropper::class
    ];

    /**
     * Add meta fields
     *
     * @param array $fields
     * @return array|\Orchid\Screen\Field[]|Input|Select[]|TextArea
     */
    public static function withMeta(array $fields)
    {
        return array_merge($fields, [
            Input::make('meta_title')
                ->type(Field::INPUT_TEXT)
                ->title(__('Meta Title')),
            Select::make('meta_keywords')
                ->title(__('Meta Keywords'))
                ->multiple()
                ->taggable(),
            TextArea::make('meta_description')
                ->title(__('Meta Description'))
                ->rows(4)
        ]);
    }

    /**
     * Check wether type is file field or not
     *
     * @param $type
     * @return bool
     */
    public static function isFileField($type)
    {
        return in_array($type, static::FILE_FIELDS);
    }

    /**
     * Get all available fields
     *
     * @param null $typeClass
     * @return \Illuminate\Support\Collection
     */
    public static function all(bool $withMethods = true, $typeClass = null)
    {
        $types = collect(File::allFiles(base_path('vendor/orchid/platform/src/Screen/Fields')))
            ->filter(function (SplFileInfo $file) {
                $isPhpFile = Str::endsWith($file->getFilename(), '.php');

                return $isPhpFile;
            })
            ->map(function (SplFileInfo $file) {
                $className = 'Orchid\Screen\Fields\\' . str_replace('.php', '', $file->getBasename());
                $class = new ReflectionClass($className);

                return $class;
            })
            ->filter(function (ReflectionClass $class) use ($typeClass) {
                if (is_bool($class->getParentClass())) {
                    return false;
                }

                $filter = ($class->getParentClass()->getName() == 'Orchid\Screen\Field'
                    and !in_array($class->getShortName(), [
                        'Relation',
                        'ViewField',
                        'Label',
                        'Radio',
                        'Password',
                        'Range'
                    ]));

                if (empty($typeClass)) {
                    return $filter;
                } elseif (is_array($typeClass)) {
                    return ($filter and in_array($class->getName(), $typeClass));
                }

                return ($filter and $class->getName() == $typeClass);
            })
            ->map(function (ReflectionClass $class) use ($withMethods) {
                if ($withMethods) {
                    $comment = $class->getDocComment() . $class->getParentClass()->getDocComment();
                    $isThereRequiredMethod = in_array($class->getName(), array_keys(static::REQUIRED_METHODS));
                    $requiredMethods = $isThereRequiredMethod
                        ? static::REQUIRED_METHODS[$class->getName()]
                        : [];

                    $methods = collect(explode("\n", $comment))
                        ->filter(function ($line) {
                            return str_contains($line, '@method ')
                                and !Str::contains($line, ['title(', 'help(']);
                        })
                        ->map(function ($line) use ($isThereRequiredMethod, $requiredMethods) {
                            $method = method_from_doc_code($line);
                            $method['active'] = false;
                            if ($isThereRequiredMethod and in_array($method['name'], array_keys($requiredMethods))) {
                                $method['active'] = true;
                                $method['param_str'] = $requiredMethods[$method['name']];
                            }

                            return (object)$method;
                        });
                    collect($class->getMethods())
                        ->filter(function (ReflectionMethod $method) {
                            $isSelfReturnType = false;
                            if ($method->hasReturnType()) {
                                $isSelfReturnType = $method->getReturnType()->getName() == 'self';
                            }

                            return Reflection::getModifierNames($method->getModifiers())[0] == 'public' and $isSelfReturnType;
                        })
                        ->each(function (ReflectionMethod $method) use ($isThereRequiredMethod, $requiredMethods, &$methods) {
                            $exists = $methods->where('name', '=',  $method->getName())
                                    ->count() > 0;
                            if (!$exists) {
                                $filename = $method->getFileName();
                                $startLine = $method->getStartLine() - 1;
                                $source = file($filename);
                                $line = implode("", array_slice($source, $startLine, 1));

                                $method_ = method_from_doc_code($line);
                                $method_['active'] = false;
                                if ($isThereRequiredMethod and in_array($method->getName(), array_keys($requiredMethods))) {
                                    $method_['active'] = true;
                                    $method_['param_str'] = $requiredMethods[$method->getName()];
                                }

                                $methods->push((object)$method_);
                            }
                        });
                    $methods = $methods->sortBy('name')
                        ->values();
                } else {
                    $methods = collect([]);
                }

                return (object)[
                    'name' => $class->getShortName(),
                    'class' => $class->getName(),
                    'methods' => $methods
                ];
            });

        return $types;
    }

    /**
     * Find field by class name
     *
     * @param $type
     * @param bool $withMethods
     * @return mixed
     */
    public static function find($type, bool $withMethods = true)
    {
        return static::all($withMethods, $type)->first();
    }

    /**
     * Generate field by class name with options
     *
     * @param $type
     * @param string $name
     * @param array $options
     * @return mixed
     */
    public static function make($type, string $name = 'value', array $options = [])
    {
        return rescue(function () use ($type, $name, $options) {
            $field = $type::make($name);
            $field = chained_method_call($field, $options);

            return $field;
        }, function () use ($name) {
            return TextArea::make($name);
        });
    }
}
