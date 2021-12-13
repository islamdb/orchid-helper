<?php

namespace IslamDB\OrchidHelper\Screens;

use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class ResourceScreen extends Screen
{
    /**
     * Display header name.
     *
     * @var string
     */
    public $name = '';

    public $defaultSort = 'created_at';

    public $perPage = 10;

    public $key = 'id';

    public $title = 'name';

    public function model()
    {
        return null;
    }

    public function modelView()
    {
        return $this->model();
    }

    /**
     * Query data.
     *
     * @return array
     */
    public function query(): array
    {
        return [
            'list' => $this->modelView()
                ->defaultSort($this->defaultSort)
                ->paginate($this->perPage),
            'data' => new Repository([])
        ];
    }

    /**
     * Button commands.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            ModalToggle::make(__('Add'))
                ->modal('addOrEditForm')
                ->method('addOrEdit')
                ->icon('plus')
                ->modalTitle(__('Add') . $this->name)
                ->asyncParameters([
                    'key' => null
                ])
        ];
    }

    /**
     * Views.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): array
    {
        $columns = [
            TD::make('#')
                ->align(TD::ALIGN_CENTER)
                ->width('100px')
                ->render(function ($model) {
                    return DropDown::make()
                        ->icon('options-vertical')
                        ->list($this->actions($model));
                })
        ];

        $layouts = [
            Layout::modal('viewModal', [
                Layout::legend('data', $this->views())
            ])->async('asyncViewData')
                ->withoutApplyButton()
                ->size(Modal::SIZE_LG),
            Layout::table('list', array_merge($columns, $this->columns())),
            Layout::modal('addOrEditForm', [
                Layout::rows($this->fields())
            ])->async('asyncEditData')
                ->applyButton(__('Save'))
                ->size(Modal::SIZE_LG)
        ];

        return $layouts;
    }

    public function actions($model)
    {
        return [
            ModalToggle::make(__('View'))
                ->icon('eye')
                ->modal('viewModal')
                ->asyncParameters([
                    'key' => $model->{$this->key}
                ])->modalTitle($model->{$this->title}),
            ModalToggle::make(__('Edit'))
                ->icon('note')
                ->modal('addOrEditForm')
                ->method('addOrEdit')
                ->asyncParameters([
                    'key' => $model->{$this->key},
                ])->modalTitle(__('Edit') . $model->{$this->title}),
            Button::make(__('Delete'))
                ->icon('trash')
                ->method('delete')
                ->parameters(['key' => $model->{$this->key}])
                ->confirm(__('Delete'))
        ];
    }

    public function asyncEditData($key = null)
    {
        return !empty($key)
            ? [
                'key' => $key,
                'data' => $this->model()
                    ->find($key)
            ]
            : [];
    }

    public function asyncViewData($key = null)
    {
        $data = !empty($key)
            ? [
                'key' => $key,
                'data' => $this->modelView()
                    ->find($key)
            ]
            : [];

        return $data;
    }

    public function fields()
    {
        return [

        ];
    }

    public function columns()
    {
        return [

        ];
    }

    public function views()
    {
        return [

        ];
    }

    public function onUpdate()
    {
        $this->model()
            ->where($this->key, request()->key)
            ->first()
            ->update(request()->data);
    }

    public function onCreate()
    {
        $this->model()->create(request()->data);
    }

    public function onDelete()
    {
        $this->model()
            ->where($this->key, request()->key)
            ->delete();
    }

    public function addOrEdit()
    {
        rescue(function () {
            if (!empty(request()->key)) {
                $this->onUpdate();

                Alert::success(__('Saved'));
            } else {
                $this->onCreate();

                Alert::success(__('Saved'));
            }
        }, function (\Throwable $e) {
            if (request()->has('key')) {
                Alert::error(__('Failed') . '<br>' . $e->getMessage());
            } else {
                Alert::error(__('Failed') . '<br>' . $e->getMessage());
            }
        });
    }

    public function delete()
    {
        rescue(function () {
            $this->onDelete();

            Alert::success(__('Deleted'));
        }, function (\Throwable $e) {
            Alert::error(__('Failed') . '<br>' . $e->getMessage());
        });
    }
}
