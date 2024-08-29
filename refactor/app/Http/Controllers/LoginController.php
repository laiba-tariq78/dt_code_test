<?php

namespace App\Http\Controllers;

use App\Models\Throttles;

class LoginController extends Controller
{


    public function userLoginFailed()
    {
        $throttles = Throttles::where('ignore', 0)->with('user')->paginate(15);

        return ['throttles' => $throttles];
    }
}
