<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HttpTestController extends Controller {
    public function __invoke() {
        $response = Http::get('https://libraryapi.site/api/exemplars/12345678');
        dd($response->json()['data']);
    }
}