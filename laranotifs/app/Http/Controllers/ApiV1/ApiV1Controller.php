<?php

namespace App\Http\Controllers\ApiV1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiV1Controller extends Controller
{
    public function isWorking(){
        return "Todo Ok 200 check";
    }
}
