<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Carbon\Carbon;

class Misc {
    const LIST_COMMAND = 'list_command';
    const LIST_DATETIME = 'list_datetime';
    const LIST_MESSAGE = 'list_message';
    const LIST_STATUS = 'list_status';

    public static function list_command():array {
        return Redis::lrange(self::LIST_COMMAND,0,-1);
    }

    public static function list_datetime():array {
        return Redis::lrange(self::LIST_DATETIME,0,-1);
    }

    public static function list_message():array {
        return Redis::lrange(self::LIST_MESSAGE,0,-1);
    }

    public static function list_status():array {
        return Redis::lrange(self::LIST_STATUS,0,-1);
    }

    public static function monitor(string $command, string $message, int $status):void {
        $l = 1 + intval(Redis::llen(self::LIST_COMMAND));
        Redis::multi();
        Redis::lpush(self::LIST_COMMAND,substr($command,4));
        Redis::lpush(self::LIST_DATETIME,strval(Carbon::now()));
        Redis::lpush(self::LIST_MESSAGE,$message);
        Redis::lpush(self::LIST_STATUS,strval($status));
        if ($l > env('MAX_MONITORED_LINES',120)) {
            Redis::rpop(self::LIST_COMMAND, $l - env('MAX_MONITORED_LINES',120));
            Redis::rpop(self::LIST_DATETIME, $l - env('MAX_MONITORED_LINES',120));
            Redis::rpop(self::LIST_MESSAGE, $l - env('MAX_MONITORED_LINES',120));
            Redis::rpop(self::LIST_STATUS, $l - env('MAX_MONITORED_LINES',120));
        }
        Redis::exec();
    }
}