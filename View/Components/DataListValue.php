<?php

namespace Modules\DataListCRM\View\Components;

use Illuminate\View\Component;

class DataListValue extends Component
{


    public function __construct(
        public $item,
        public string $field,
        public array $options = [],
        public array $parents = [],
    ){
    }

    /**
     * Get the views / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        $datas = [];
        $value =  $this->item->{$this->field};
        if($this->options['format'] ?? null){
            $value =$this->options['format']($this->item);
        }


        if($this->options['component'] ?? false) {
            $datas = ($this->options['component']['attribute'] instanceof \Closure) ? $this->options['component']['attribute']($this->item) : [$this->options['component']['attribute'] => $value];
        }

        return view('datalistcrm::components.data-list-value', [
            'value' => $value,
            'datas' => $datas
        ]);
    }
}
