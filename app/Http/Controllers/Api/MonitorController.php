<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Services\Misc;

class MonitorController extends Controller {
    public function __invoke():JsonResponse {
        $c = count(Misc::list_datetime());
        $arr = [];
        for($i = 0; $i < $c; $i++) $arr[] = [
            'id' => $i,
            'command' => Misc::list_command()[$i],
            'datetime' => Misc::list_datetime()[$i],
            'message' => Misc::list_message()[$i],
            'status' => intval(Misc::list_status()[$i])
        ];
        return response()->json([
            'data' => $arr
        ], Response::HTTP_OK);
    }
}