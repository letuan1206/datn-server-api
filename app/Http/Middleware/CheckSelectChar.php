<?php

namespace App\Http\Middleware;

use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use Closure;

class CheckSelectChar
{
    private $dependence;

    public function __construct(AllFunctions $functions)
    {
        $this->dependence = $functions;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $check_select = $this->dependence->check_select_char($request->name);

        if ($check_select == 1) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $request->name . ' không được thoát game cuối cùng!';
            return response()->json($apiFormat);
        }

        $check_char = $this->dependence->check_character_in_account($request->account, $request->name);

        if ($check_char == 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $request->name . ' không nằm trong tài khoản ' . $request->memb___id;
            return response()->json($apiFormat);
        }
        return $next($request);
    }
}
