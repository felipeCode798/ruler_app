<?php

namespace App\Livewire;

use Livewire\Component;

class DateRangePicker extends Component
{

    public $startDate;
    public $endDate;

    public function render()
    {
        return view('livewire.date-range-picker');
    }
}
