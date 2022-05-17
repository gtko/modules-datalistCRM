<?php

namespace Modules\DataListCRM\Http\Livewire;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Livewire\WithPagination;
use Modules\BaseCore\Interfaces\RepositoryFetchable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Modules\BaseCore\Interfaces\RepositoryQueryCustom;
use Modules\BaseCore\Repositories\AbstractRepository;
use Modules\DataListCRM\Abstracts\DataListType;

class DataList extends Component
{

    use WithPagination;

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
                            /** Relation unique a 1 niveau  */
                            /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo $relation */
                            $relation = $relations[0];
                            $query->select($sortable['field'])
                                ->from($relation->getModel()->getTable())
                                ->whereColumn(
                                    $relation->getOwnerKeyName(),
                                    $relation->getParent()->getTable() . '.' . $relation->getForeignKeyName()
                                );
                        }
                        else
                        {
                            /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo $relation */
                            $relation = $relations[0];
                            $query//->select($relation->getModel()->getTable().'.'.$relation->getOwnerKeyName())
                            ->from($relation->getModel()->getTable())
                                ->whereColumn(
                                    $relation->getModel()->getTable() .'.'. $relation->getOwnerKeyName(),
                                    $relation->getParent()->getTable() . '.' . $relation->getForeignKeyName()
                                );

                            /** @var \Illuminate\Database\Eloquent\Relations\BelongsToMany $relation */
                            $relation = $relations[1];
                            if(is_a($relation, BelongsToMany::class)){

                                $query
                                    ->select($relation->getRelated()->getTable() .'.'.$sortable['field'])
                                    ->join(
                                        $relation->getTable(),
                                        $relations[0]->getModel()->getTable() .'.'. $relations[0]->getOwnerKeyName(),
                                        $relation->getTable() . '.' . $relation->getForeignPivotKeyName()
                                    )
                                    ->join(
                                        $relation->getRelated()->getTable(),
                                        $relation->getRelated()->getTable().'.'.$relation->getRelatedKeyName(),
                                        $relation->getTable() . '.' . $relation->getRelatedPivotKeyName()
                                    )->whereColumn(
                                        $relation->getTable() . '.' . $relation->getForeignPivotKeyName(),
                                        $relation->getParent()->getTable() . '.' . $relation->getParentKeyName()
                                    );
                                if ($sortable['pivot'] ?? false) {
                                    $query->where($relation->getTable().'.'.$sortable['pivot'][0], $sortable['pivot'][1]);
                                }
                            }
                            else{
                                $relation = $relations[1];
                                $query->select($sortable['field'])
                                    ->join($relation->getModel()->getTable(),
                                        $relation->getModel()->getTable().'.'.$relation->getOwnerKeyName(),
                                        $relation->getParent()->getTable() . '.' . $relation->getForeignKeyName()
                                    );
                            }
                        }


                    }, $this->direction);

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
            'datalist' => $type,
            'fields' => $type->getFields(),
            'actions' => $type->getActions(),
            'create' => $type->getCreate(),
            'searchable' => $type->isSearchable(),
            'sortable' => $type->isSortable(),
            'sort' => $this->sort
        ]);
    }
}
