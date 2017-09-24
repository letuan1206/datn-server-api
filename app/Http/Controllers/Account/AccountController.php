<?php

namespace App\Http\Controllers\Account;

use App\EckPrince\AllFunctions;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AccountController extends Controller
{
    private $dependence;

    public function __construct(AllFunctions $functions)
    {
        $this->dependence = $functions;
    }
}
