<?php

namespace App\Http\Controllers\Account;

use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AccountController extends Controller
{
    private $dependence;

    public function __construct(AllFunctions $functions)
    {
        $this->dependence = $functions;
    }

    public function getSMSService(Request $request) {
        return $request;
    }

    public function changePass1SMS(Request $request) {
        DB::insert('insert into log_login (account, ip, time, description) values (?, ?, ?, ?)', [$request->account, $request->ip, time(), "Đăng nhập thành công"]);
    }
}
