<?php


namespace IslamDB\OrchidHelper\Resource\Traits;


use Illuminate\Support\Str;

trait ResourceOnSave
{
    /**
     * Sluggable with condition
     *
     * @param $request
     * @param string $referencedColumn
     * @param string $column
     */
    public function sluggable($request, $referencedColumn = 'title', $column = 'slug')
    {
        if (empty($request->{$column})){
            $request->merge([
                $column => Str::slug($request->{$referencedColumn})
            ]);
        }
    }

    /**
     * Save attachment
     *
     * @param $request
     * @param $model
     * @param string $name
     */
    public function saveWithAttachment($request, $model, $name = 'attachment')
    {
        $attachmentIds = $request->input($name, []);
        $request->request
            ->remove($name);

        parent::onSave($request, $model);

        $model->attachment()
            ->syncWithoutDetaching($attachmentIds);
    }
}
