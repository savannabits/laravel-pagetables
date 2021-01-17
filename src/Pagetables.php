<?php

namespace Savannabits\Pagetables;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use ReflectionClass;
use Throwable;

class Pagetables
{
    /**
     * @var Builder|\Illuminate\Database\Query\Builder
     */
    private $query;
    private $currentPage;
    private $search;
    private $sort;
    private $sortDirection;
    /**
     * @var Column[]
     */
    private array $columns;
    /**
     * @var int
     */
    private $perPage;
    private array $filters;

    public function __construct(Builder $query)
    {
        $this->query = $query;
        $this->search = request()->get('search');
        $this->sort = request()->get('sort');
        $this->currentPage = request()->get('page');
        $this->sortDirection = request()->get('sort_direction', 'asc');
        $this->perPage = request()->get('per_page', 15);
        $this->filters = request()->except(['search','sort','sort_direction','per_page','page']);
    }

    /**
     * @param Column[] $columns
     * @return Pagetables
     */
    public function columns(array $columns): Pagetables
    {
        $this->columns = $columns;

        return $this;
    }

    public static function of(Builder $query): Pagetables
    {
        return new self($query);
    }

    public function make($withColumns = false): \Illuminate\Support\Collection
    {
        $columns = collect($this->columns)->reject(fn ($column) => $column->isRaw());
        $columnNames = $columns->map(fn ($column) => $column->getName());
        $query = $this->query->where(function (Builder $q) use ($columns) {
            $firstColumn = collect($columns)->get(0);
            $otherColumns = collect($columns)->except(0);
            $this->applySearch($firstColumn, $q);
            foreach ($otherColumns as $column) {
                $this->applySearch($column, $q, true);
            }
        });
        $with = collect([]);
        foreach ($columnNames as $columnName) {
            $exploded = explode(".", $columnName);
            if (sizeof($exploded) == 2) {
                $with->push($exploded[0]);
            } elseif (sizeof($exploded) == 3) {
                $with->push($exploded[0].".".$exploded[1]);
            }
        }
        $query->with($with->toArray());
        $this->applySort($query);
        $pagination = $query->paginate($this->perPage);
        $dt = json_decode($pagination->toJson());
        if ($withColumns) {
            $dt->columns = $this->getColumnsArray();
        }

        return collect($dt);
    }

    private function applySearch(Column $column, Builder &$q, $or = false)
    {
        // Apply Search to a depth of 3
        $searchParts = explode(".", $column->getSearchKey());
        if (sizeof($searchParts) < 3) {
            switch (sizeof($searchParts)) {
                case 1:
                    if ($or) {
                        $q->orWhere($searchParts[0], "LIKE", "%".$this->search."%");
                    } else {
                        $q->where($searchParts[0], "LIKE", "%".$this->search."%");
                    }

                    break;
                case 2:
                    if ($or) {
                        $q->orWhereHas($searchParts[0], function ($q1) use ($searchParts) {
                            $q1->where($searchParts[1], "LIKE", "%".$this->search."%");
                        });
                    } else {
                        $q->whereHas($searchParts[0], function ($q1) use ($searchParts) {
                            $q1->where($searchParts[1], "LIKE", "%".$this->search."%");
                        });
                    }

                    break;
                case  3:
                    if ($or) {
                        $q->orWhereHas($searchParts[0], function ($q1) use ($searchParts) {
                            $q1->whereHas($searchParts[1], function ($q2) use ($searchParts) {
                                $q2->where($searchParts[2], "LIKE", "%".$this->search."%");
                            });
                        });
                    } else {
                        $q->whereHas($searchParts[0], function ($q1) use ($searchParts) {
                            $q1->whereHas($searchParts[1], function ($q2) use ($searchParts) {
                                $q2->where($searchParts[2], "LIKE", "%".$this->search."%");
                            });
                        });
                    }

                    break;
                default:break;
            }
        }
    }

    private function applySort(Builder &$q)
    {
        if ($this->sort) {
            $key = explode(".", $this->sort);
            if (sizeof($key) === 1) {
                $q->orderBy($this->sort ?? $key[0], $this->sortDirection ?? 'asc');
            } elseif (sizeof($key) === 2) {
                $relationship = $this->getRelatedFromMethodName($key[0], get_class($q->getModel()));
                if ($relationship) {
                    $ownerKey = $relationship->getOwnerKeyName();
                    $fKey = $relationship->getForeignKeyName();
                    $fTable = $relationship->getRelated()->getTable();
                    $ownerTable = $relationship->getParent()->getTable();
                    if ($relationship instanceof BelongsTo) {
                        $q->orderBy(
                            get_class($relationship->getRelated())::select($key[1])->whereColumn("$fTable.$ownerKey", "$ownerTable.$fKey"),
                            $this->sortDirection ?? 'asc'
                        );
                    } elseif ($relationship instanceof HasOne) {
                        $q->orderBy(
                            get_class($relationship->getRelated())::select($key[1])->whereColumn("$fTable.$fKey", "$ownerTable.$ownerKey"),
                            $this->sortDirection ?? 'asc'
                        );
                    }
                    /*$q->join($fTable, "$ownerTable.$fKey", '=', "$fTable.$ownerKey")
                        ->orderBy($fTable.".".$key[1],$this->sortDirection ?? 'asc');*/
                    /*$q->orderBy($fKey,$this->sortDirection ?? 'asc');*/
                }
            }
        }
    }

    public function getColumnsArray(): array
    {
        return collect($this->columns)->map(function (Column $column) {
            return $column->toArray();
        })->toArray();
    }

    private function getRelatedFromMethodName(string $method_name, string $class)
    {
        try {
            $method = (new ReflectionClass($class))->getMethod($method_name);
            $return = $method->invoke(new $class);

            return $return;
        } catch (Throwable $exception) {
            return null;
        }
    }
}
