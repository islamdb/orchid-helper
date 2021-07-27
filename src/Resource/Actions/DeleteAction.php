<?php

namespace IslamDB\OrchidHelper\Resource\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Orchid\Crud\Action;
use Orchid\Screen\Actions\Button;
use Orchid\Support\Facades\Alert;

class DeleteAction extends Action
{
    /**
     * The button of the action.
     *
     * @return Button
     */
    public function button(): Button
    {
        return Button::make(__('Delete selected items'))
            ->icon('trash')
            ->confirm(__('Are you sure you want to delete these resources?'));
    }

    /**
     * Perform the action on the given models.
     *
     * @param \Illuminate\Support\Collection $models
     */
    public function handle(Collection $models)
    {
        rescue(function () use (&$models) {
            DB::beginTransaction();

            $models->each(function ($model) {
                $model->delete();
            });

            DB::commit();

            Alert::success("{$models->count()} ".__('deleted'));
        }, function ($e) {
            DB::rollBack();

            Alert::error(__('Failed').". {$e->getMessage()}");
        });
    }
}
