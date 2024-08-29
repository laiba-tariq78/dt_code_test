<?php

namespace App\Http\Controllers;

use App\Services\ThrottleService;

class ThrottleController extends Controller
{
    protected $throttleService;

    public function __construct(ThrottleService $throttleService)
    {
        $this->throttleService = $throttleService;
    }

    public function ignoreThrottle($id)
    {
        return $this->throttleService->ignoreThrottle($id);
    }
}
