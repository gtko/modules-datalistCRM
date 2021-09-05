<?php

namespace Modules\DataListCRM\View\Components;

use Illuminate\View\Component;
use Illuminate\View\ComponentAttributeBag;

class Dynamics extends Component
{

    public function __construct(
        public string $component,
        public array $datas
    ){}


    /**
     * Get the views / contents that represent the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view($this->component, $this->datas + [
            'attributes' => new ComponentAttributeBag($this->datas)
        ]);
    }
}
