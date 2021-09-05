<?php

namespace Modules\DataListCRM\Abstracts;


use Modules\BaseCore\Interfaces\RepositoryFetchable;

abstract class DataListType
{

    protected bool $searchable = true;

    abstract public function getFields():array;
    abstract public function getActions():array;
    abstract public function getCreate():array;

    abstract public function getRepository(array $parents = []):RepositoryFetchable;

    public function isSearchable(): bool
    {
        return $this->searchable;
    }
}
