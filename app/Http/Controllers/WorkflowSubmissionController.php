<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\WorkflowSubmissionRequest;

class WorkflowSubmissionController extends Controller
{
    private $url = config('workflow.staging');

    public function submit(WorkflowSubmissionRequest $request) {
        $ch = curl_init($this->url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $request);

        $res = curl_exec($ch);
        $response = json_decode($res);

        curl_close($ch);
    }

    public function accept() {
        // put your updating of status here
    }
}
