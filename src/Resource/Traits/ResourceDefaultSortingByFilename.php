<?php


namespace IslamDB\OrchidHelper\Resource\Traits;


use Illuminate\Support\Facades\File;

trait ResourceDefaultSortingByFilename
{
    public static function sort(): string
    {
        return collect(File::allFiles(base_path('app/Orchid/Resources')))
            ->map(function (\SplFileInfo $file) {
                return $file->getFilename();
            })->sort()
            ->values()
            ->flip()[(new \ReflectionClass(static::class))->getShortName().'.php'] + 2000;
    }
}
