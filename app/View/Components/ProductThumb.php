<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ProductThumb extends Component
{
    
    public $image;
    public $name;
    public $code;
    public $inventory;
    public $slug;
    public $id;
    
    public function __construct($data)
    {
        $this->image = $data['image'];
        $this->name = $data['name'];
        $this->code = $data['code'];
        $this->inventory = $data['inventory'];
        $this->slug = $data['slug'];
        $this->id = $data['id'];
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.product-thumb');
    }
}
