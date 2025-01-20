<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class PurchaseAdviceReport implements FromView
{
    protected $purchaseAdviceData;
    protected $postedDate;
    protected $salesHeader;
    protected $paHeader;
    protected $requestor;

    public function __construct($purchaseAdviceData, $postedDate, $salesHeader, $paHeader, $requestor)
    {
        //$purchaseAdviceData, $postedDate, $salesHeader, $paHeader
        $this->purchaseAdviceData = $purchaseAdviceData;
        $this->postedDate = $postedDate;
        $this->salesHeader = $salesHeader;
        $this->paHeader = $paHeader;
        $this->requestor = $requestor;
    }

    /**
     * Return a view file to be exported to Excel.
     */
    public function view(): View
    {
        
        return view('admin.purchasing.components.generate-report-2', [
            'purchaseAdviceData' => $this->purchaseAdviceData,
            'postedDate' => $this->postedDate,
            'salesHeader' => $this->salesHeader,
            'paHeader' => $this->paHeader,
            'requestor' => $this->requestor,
        ]);
        
        
        /*
        return view('admin.purchasing.components.users', [
            'data' => [],
        ]);
        */
        
    }
}
