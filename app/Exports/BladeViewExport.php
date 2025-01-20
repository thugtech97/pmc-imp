<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class BladeViewExport implements FromView
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
     * Return a view file to be exported to Excel.
     */
    public function view(): View
    {
        return view('admin.purchasing.components.users', [
            'data' => $this->data,
        ]);
    }
}
