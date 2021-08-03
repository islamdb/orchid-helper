<?php

namespace IslamDB\OrchidHelper\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;
use Orchid\Screen\Fields\Select;

class DefaultFilter extends Filter
{
    /**
     * @var array
     */
    public $parameters = [];

    public $name;

    public $data;

    public function __construct(string $name, array $data)
    {
        parent::__construct();

        $this->name = $name;
        $this->parameters = collect($data)->pluck('param')->toArray();
        $this->data = collect($data)
            ->map(function ($datum) {
                $datum['options'] = $datum['model']
                    ->get()
                    ->mapWithKeys(function ($row) use ($datum) {
                        return [$row->{$datum['key']} => $row->{$datum['name']}];
                    });
                $datum['options']->prepend(__('All'), 'all');
                $param = $this->request->{$datum['param']};
                $datum['options'] = $datum['options']->when(!empty($param) and $param != 'all', function (Collection $d) use ($datum) {
                        return $d->sortBy(function ($d, $k) use ($datum) {
                            return $k == $this->request->{$datum['param']}
                                ? 'aaa'
                                : ($k != 'all' ? "zzz$k" : 'all');
                        }, SORT_NATURAL);
                    });

                return $datum;
            })
            ->toArray();
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function run(Builder $builder): Builder
    {
        foreach ($this->data as $datum) {
            $param = $this->request->{$datum['param']};
            $builder->when(!empty($param) and $param != 'all', function ($builder) use ($datum) {
                $builder->where($datum['foreign'], $this->request->{$datum['param']});
            });
        }

        return $builder;
    }

    /**
     * @return Field[]
     */
    public function display(): array
    {
        $filterFields = [];
        foreach ($this->data as $title => $datum) {
            $filterFields[] = Select::make($datum['param'])
                ->title($title)
                ->options($datum['options'])
                ->value($this->request->get($datum['param']));
        }

        return $filterFields;
    }

    /**
     * @return string
     */
    public function value(): string
    {
        $text = '';
        foreach ($this->data as $title => $datum) {
            $param = $this->request->{$datum['param']};
            if (!empty($param) and $param != 'all') {
                $text .= $title.': '.$datum['options']
                        ->filter(function ($option, $key) use ($param) {
                            return $key == $param;
                        })
                        ->first().' | ';
            }
        }
        $text = !empty($text)
            ? substr($text, 0, -3)
            : $text;

        return $text;
    }
}
