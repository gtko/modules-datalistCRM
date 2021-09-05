<?php

namespace Modules\DataListCRM\Http\Livewire;

use Modules\BaseCore\Interfaces\RepositoryFetchable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Modules\DataListCRM\Abstracts\DataListType;

class DataList extends Component
{
    public string $title;
    public string $datalistclass;
    public array $parents = [];

    public string $search = '';
    public $queryString = ['search'];

    public function mount(string $title, string $type, array $parents = [] ){

        $this->title = $title;
        $this->parents = $parents;
        $this->datalistclass = $type;
    }


    private function getDatas(RepositoryFetchable $repository): LengthAwarePaginator
    {
        if($this->search){
            return $repository->fetchSearch($this->search);
        }

        return $repository->fetchAll();
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
            'datas' => $this->getDatas($type->getRepository($this->parents)),
            'fields' => $type->getFields(),
            'actions' => $type->getActions(),
            'create' => $type->getCreate(),
            'searchable' => $type->isSearchable()
        ]);
    }
}
