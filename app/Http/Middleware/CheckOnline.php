<?php

namespace App\Http\Middleware;

use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use Closure;

class CheckOnline
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
        $check_online = $this->dependence->check_online($request->account);
        if ($check_online == 1) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Bạn chưa thoát game!';
            return response()->json($apiFormat);
        }

        return $next($request);
    }
}
