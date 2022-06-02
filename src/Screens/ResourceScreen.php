<?php

namespace IslamDB\OrchidHelper\Screens;

use Illuminate\Support\Str;
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

    public $sortingDirection = 'desc';

    public $perPage = 10;

    public $key = 'id';

    public $title = 'name';

    public $defaultRequestValues = [];

    /**
     * Use to define which file column
     * to save (in attachment)
     *
     * @var array
     */
    public $files = [];

    /**
     * Use to define sluggable columns
     *
     * example :
     * public $sluggables = [
     *     'name' => 'slug',
     *     'name' => 'code'
     * ];
     *
     * @var array
     */
    public $sluggables = [];

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
        $perPage = request()->perPage;
        $this->perPage = $perPage != null ? $perPage : $this->perPage;

        $list = $this->modelView()
            ->filters()
            ->defaultSort($this->defaultSort, $this->sortingDirection);
        $list = str_contains($this->perPage, "-") ?
            $list->get() :
            $list->paginate($this->perPage);
        
        return [
            'list' => $list,
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
        $commands = [];
        $request = request()->all();

        if (!empty($this->fields())) {
            $commands = [
                ModalToggle::make(__('Add'))
                    ->modal('addOrEditForm')
                    ->method('addOrEdit')
                    ->icon('plus')
                    ->modalTitle(__('Add') . ' ' . $this->name)
                    ->asyncParameters(array_merge($this->defaultRequestValues, [
                        'key' => null
                    ])),
                DropDown::make(__("Page"))
                    ->list([
                        Button::make("5")
                            ->method('perPage', array_merge($request, ['perPage' => 5])),
                        Button::make("10")
                            ->method('perPage', array_merge($request, ['perPage' => 10])),
                        Button::make("20")
                            ->method('perPage', array_merge($request, ['perPage' => 20])),
                        Button::make("50")
                            ->method('perPage', array_merge($request, ['perPage' => 50])),
                        Button::make(__("All"))
                            ->method('perPage', array_merge($request, ['perPage' => -1])),
                    ]),
            ];
        }

        return $commands;
    }
    
    public function perPage($page)
    {
        $request = request();
        return redirect()->route($request->route()->getName(), $request->except(['_token', 'page']));
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
        $actions = [];
        if (!empty($this->views())) {
            $actions[] = ModalToggle::make(__('View'))
                ->icon('eye')
                ->modal('viewModal')
                ->asyncParameters(array_merge($this->defaultRequestValues, [
                    'key' => $model->{$this->key}
                ]))->modalTitle($model->{$this->title});
        }
        if (!empty($this->fields())) {
            $actions[] = ModalToggle::make(__('Edit'))
                ->icon('note')
                ->modal('addOrEditForm')
                ->method('addOrEdit')
                ->asyncParameters(array_merge($this->defaultRequestValues, [
                    'key' => $model->{$this->key}
                ]))->modalTitle(__('Edit') . ' ' . $model->{$this->title});
        }
        $actions[] = Button::make(__('Delete'))
            ->icon('trash')
            ->method('delete')
            ->parameters(array_merge($this->defaultRequestValues, [
                'key' => $model->{$this->key}
            ]))
            ->confirm(__('Delete'). ' ' . $model->{$this->title});

        return $actions;
    }

    public function asyncGetFiles($data, $key = null)
    {
        if (!empty($key) and !empty($this->files)) {
            $attachments = $data['data']->attachment()
                ->get();

            foreach ($this->files as $file) {
                $ids = $attachments->where('group', $file)
                    ->pluck('id')
                    ->toArray();

                $data[$file] = $ids;
            }
        }

        return $data;
    }

    public function asyncEditData()
    {
        $data = !empty(request()->key)
            ? [
                'key' => request()->key,
                'data' => $this->model()
                    ->where($this->key, request()->key)
                    ->first()
            ]
            : [];
        $data = array_merge(request()->all(), $data);

        return $this->asyncGetFiles($data, request()->key);
    }

    public function asyncViewData()
    {
        $data = !empty(request()->key)
            ? [
                'key' => request()->key,
                'data' => $this->modelView()
                    ->where($this->key, request()->key)
                    ->first()
            ]
            : [];

        return $this->asyncGetFiles($data, request()->key);
    }

    public function fields()
    {
        return [];
    }

    public function columns()
    {
        return [];
    }

    public function views()
    {
        return [];
    }

    public function sluggable($data)
    {
        foreach ($this->sluggables as $column => $sluggable) {
            if (empty($data[$sluggable])) {
                $slugValue = collect(explode('|', $column))
                    ->map(function ($column) use ($data) {
                        return $data[$column];
                    })
                    ->join(' ');
                $data[$sluggable] = Str::slug($slugValue);
            }
        }

        return $data;
    }

    public function onUpdate()
    {
        $model = $this->model()
            ->where($this->key, request()->key)
            ->first();

        if (!empty($model)) {
            $model->update($this->sluggable(request()->data));
        }

        return $model;
    }

    public function onCreate()
    {
        return $this->model()->create($this->sluggable(request()->data));
    }

    public function onDelete()
    {
        $model = $this->model()
            ->where($this->key, request()->key)
            ->first();

        if (!empty($model)) {
            $model->delete();
        }

        return $model;
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

            Attachment::query()
                ->whereIn('id', $ids)
                ->update([
                    'group' => $file
                ]);
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
            if (!is_null($required) && $required) {
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
