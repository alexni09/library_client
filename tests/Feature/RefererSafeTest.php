<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
// use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\Misc;

class RefererSafeTest extends TestCase {
    public function test_some_referer_strings(): void {
        $this->assertFalse(Misc::isRefererSafe(null));
        $this->assertFalse(Misc::isRefererSafe(''));
        $this->assertFalse(Misc::isRefererSafe('http://local'));
        $this->assertTrue(Misc::isRefererSafe(env('APP_URL')));
        $this->assertTrue(Misc::isRefererSafe(env('APP_URL') . ':8000'));
        $this->assertTrue(Misc::isRefererSafe(env('APP_URL') . '/'));
        $this->assertTrue(Misc::isRefererSafe(env('APP_URL') . '/test'));
        $this->assertFalse(Misc::isRefererSafe(env('APP_URL') . 'abcd'));
        $this->assertTrue(Misc::isRefererSafe(env('APP_URL') . '/api/monitor'));
        $this->assertFalse(Misc::isRefererSafe(env('LIBRARY_API_URL') . '/api/books/1'));
        $this->assertFalse(Misc::isRefererSafe('https://another.website/api/categories'));
    }
}