<?php

namespace IslamDB\OrchidHelper\Screens;

use Orchid\Attachment\Models\Attachment;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Repository;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

abstract class ResourceScreen extends Screen
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

    /**
     * Use to define which file column
     * to save (in attachment)
     *
     * @var array
     */
    public $files = [];

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
                ->filters()
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
                ->modalTitle(__('Add') . ' ' . $this->name)
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
                ])->modalTitle(__('Edit') . ' ' . $model->{$this->title}),
            Button::make(__('Delete'))
                ->icon('trash')
                ->method('delete')
                ->parameters(['key' => $model->{$this->key}])
                ->confirm(__('Delete'). ' ' . $model->{$this->title})
        ];
    }

    public function asyncEditData($key = null)
    {
        $data = !empty($key)
            ? [
                'key' => $key,
                'data' => $this->model()
                    ->where($this->key, $key)
                    ->first()
            ]
            : [];

        return $data;
    }

    public function asyncViewData($key = null)
    {
        $data = !empty($key)
            ? [
                'key' => $key,
                'data' => $this->modelView()
                    ->where($this->key, $key)
                    ->first()
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
        $model = $this->model()
            ->where($this->key, request()->key)
            ->first();

        $model->update(request()->data);

        return $model;
    }

    public function onCreate()
    {
        return $this->model()->create(request()->data);
    }

    public function onDelete()
    {
        $this->model()
            ->where($this->key, request()->key)
            ->delete();
    }

    public function saveAttachment($model)
    {
        $attachmentIds = [];
        $files = [];
        foreach ($this->files as $file) {
            $ids = request()->input($file, []);
            $files[$file] = $ids;
            $ids = is_array($ids) ? $ids : [$ids];
            $attachmentIds = array_merge($attachmentIds, $ids);
        }

        if (!empty($attachmentIds)) {
            $model->attachment()
                ->syncWithoutDetaching($attachmentIds);

            $model->update($files);
        }
    }

    public function addOrEdit()
    {
        $this->validate(
            request(),
            $this->rules(),
            $this->messages()
        );

        rescue(function () {
            if (!empty(request()->key)) {
                $this->saveAttachment($this->onUpdate());

                Alert::success(__('Saved'));
            } else {
                $this->saveAttachment($this->onCreate());

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

    public function getRequiredFieldAttributes()
    {
        $attrs = [];
        foreach ($this->fields() as $field) {
            $required = $field->get('required');
            if (!is_null($required)) {
                $attrs[] = (object)[
                    'name' => $field->get('name'),
                    'title' => $field->get('title')
                ];
            }
        }

        return $attrs;
    }

    public function rules()
    {
        $rules = [];
        foreach ($this->getRequiredFieldAttributes() as $attr) {
            $rules[$attr->name] = 'required';
        }

        return $rules;
    }

    public function messages()
    {
        $messages = [];
        foreach ($this->getRequiredFieldAttributes() as $attr) {
            $messages[$attr->name . '.required'] = $attr->title . ' is required.';
        }

        return $messages;
    }
}
