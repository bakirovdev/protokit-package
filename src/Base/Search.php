<?php

namespace Bakirov\Protokit\Base;

use Bakirov\Protokit\Search\SearchFilter;
use Bakirov\Protokit\Search\SearchShow;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class Search
{
    public Builder $query;

    public array $relations = [];
    public array $filters = [];
    public int $pg = 20;

    public function setQuery(Builder $query): static
    {
        $this->query = $query;
        return $this;
    }

    public function filter(array $paramFilter): static
    {
        $filter = new SearchFilter(
            query: $this->query,
            filters: $this->filters,
            params: $paramFilter
        );
        $filter->process();

        return $this;
    }

    public function with(array $paramWith): static
    {
        $with = Arr::flatten($paramWith);
        $with = array_intersect($with, $this->relations);

        $this->query->with($with);
        return $this;
    }

    public function show(array $paramShow): static
    {
        $params = Arr::flatten($paramShow);

        $show = new SearchShow(
            query: $this->query,
            params: $params,
        );

        $show->process();

        return $this;
    }

    public function setPG(int $paramPG): static
    {
        if ($paramPG > 0 && $paramPG <= $this->pg) {
            $this->pg = $paramPG;
        }
        return $this;
    }

    public function extraQuery(): static
    {
        return $this;
    }

}
