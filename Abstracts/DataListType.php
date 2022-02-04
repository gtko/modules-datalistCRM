<?php

namespace Modules\DataListCRM\Abstracts;


use Modules\BaseCore\Interfaces\RepositoryFetchable;

abstract class DataListType
{
    protected bool $sortable = false;
    protected bool $searchable = true;

    abstract public function getFields():array;
    abstract public function getActions():array;
    abstract public function getCreate():array;

    abstract public function getRepository(array $parents = []):RepositoryFetchable;


    public function link($params = []): string
    {
        return '';
    }

    public function linkBlank($params = []): string
    {
        return '';
    }

    public function isSearchable(): bool
    {
        return $this->searchable;
    }

    public function isSortable(): bool
    {
        return $this->sortable;
    }
}
