<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SampleDownloadController extends Controller
{
    public function download()
    {
        return response()->download(
            storage_path('app/private/sample_file.csv'),
            'sample_file.csv',
        );
    }
}
