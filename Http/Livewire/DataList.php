<?php

namespace Modules\DataListCRM\Http\Livewire;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\BaseCore\Interfaces\RepositoryFetchable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Modules\BaseCore\Interfaces\RepositoryQueryCustom;
use Modules\BaseCore\Repositories\AbstractRepository;
use Modules\DataListCRM\Abstracts\DataListType;

class DataList extends Component
{
    public string $title;
    public string $datalistclass;
    public array $parents = [];

    public array $sort = [];
    public string $orderBy = '';
    public string $direction = '--';

    public string $search = '';
    public $queryString = ['search'];

    public function mount(string $title, string $type, array $parents = [] ){

        $this->title = $title;
        $this->parents = $parents;
        $this->datalistclass = $type;
    }


    private function getDatas(RepositoryFetchable|RepositoryQueryCustom|AbstractRepository $repository, $type): LengthAwarePaginator
    {
        //@todo Refactorisre ce code est suporter plus de relation et plus de sous niveau pour le orderby
        if(($this->direction ?? '--') !== '--') {
            $query = $repository->newQuery();
            $fields = $type->getFields();
                //solution 1 - order simple sur un champ direct
                if(($fields[$this->orderBy]['sortable'] ?? $type->isSortable()) === true){
                    $query->orderBy($this->orderBy, $this->direction); //DONE
                }else {
                    //solution 2 - order avec une relation belongTO
                    if($fields[$this->orderBy]['sortable']['relations'] ?? false) {
                            $query->orderBy(function ($query) use ($fields){
                                $sortable = $fields[$this->orderBy]['sortable'];
                                $relations = $sortable['relations'];
                                if(count($relations) < 2) {
                                    /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo $relation */
                                    $relation = $relations[0];
                                    $query->select($sortable['field'])
                                        ->from($relation->getModel()->getTable())
                                        ->whereColumn(
                                            $relation->getOwnerKeyName(),
                                            $relation->getParent()->getTable() . '.' . $relation->getForeignKeyName()
                                        );
                                } else {
                                    /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo $relation */
                                    $relation = $relations[0];
                                    $query->select($relation->getOwnerKeyName())
                                        ->from($relation->getModel()->getTable())
                                        ->whereColumn(
                                            $relation->getOwnerKeyName(),
                                            $relation->getParent()->getTable() . '.' . $relation->getForeignKeyName()
                                        );

                                    /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation */
                                    $relation = $relations[1];
                                    if(is_a($relation, BelongsToMany::class)){
                                        $query->orderBy(function ($query) use ($relation, $sortable) {
                                        $query->select($relation->getRelatedPivotKeyName())
                                            ->from($relation->getTable())
                                            ->join(
                                                $relation->getRelated()->getTable(),
                                                $relation->getRelatedKeyName(),
                                                $relation->getTable() . '.' . $relation->getRelatedPivotKeyName()
                                            )
                                            ->whereColumn(
                                                $relation->getTable() . '.' . $relation->getForeignPivotKeyName(),
                                                $relation->getParent()->getTable() . '.' . $relation->getParentKeyName()
                                            );

                                        if ($sortable['pivot'] ?? false) {
                                            $query->where($sortable['pivot'][0], $sortable['pivot'][1]);
                                        }

                                        $query->limit(1);
                                    });
                                    }else{
                                        $relation = $relations[1];
                                        $query->orderBy(function ($query) use ($relation, $sortable) {
                                            $query->select($sortable['field'])
                                                ->from($relation->getModel()->getTable())
                                                ->whereColumn(
                                                    $relation->getOwnerKeyName(),
                                                    $relation->getParent()->getTable() . '.' . $relation->getForeignKeyName()
                                                );
                                        });
                                    }
                                }
                            }, $this->direction);

                        //Solution 3 order avec une relation d'une relation d'une relation

                        //Solution 4 order avec une relation belongtomany
                    }
                }
            $repository->setQuery($query);

        }

        if($this->search){
            return $repository->fetchSearch($this->search);
        }

        return $repository->fetchAll();
    }

    public function sort($field){
        switch(($this->sort[$field] ?? '')){
            case 'asc' :
                $this->sort[$field] = 'desc';
                break;
            case 'desc' :
                $this->sort[$field] = '--';
                break;
            case '--' :
                $this->sort[$field] = 'asc';
                break;
            default :
                $this->sort[$field] = 'asc';
                break;
        }

        foreach($this->sort as $col => $value){
            if($col !== $field){
                $this->sort[$col] = '--';
            }
        }

        $this->orderBy = $field;
        $this->direction = $this->sort[$field];
    }

    /**
     * Get the views / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        /** @var DataListType $type */
        $type = new $this->datalistclass;

        return view('datalistcrm::livewire.data-list', [
            'datas' => $this->getDatas($type->getRepository($this->parents), $type),
            'fields' => $type->getFields(),
            'actions' => $type->getActions(),
            'create' => $type->getCreate(),
            'searchable' => $type->isSearchable(),
            'sortable' => $type->isSortable(),
            'sort' => $this->sort
        ]);
    }
}
