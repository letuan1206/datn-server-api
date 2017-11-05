<?php

namespace App\Http\Middleware;

use App\EckPrince\Constains;
use Closure;
use Illuminate\Support\Facades\DB;

class CheckLoginToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $login_token = DB::table('MEMB_INFO')->select('checklogin')->where('memb___id', $request->account)->first();

        if (strcmp($login_token->checklogin, $request->header('LOGIN-TOKEN')) != 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Bạn đã đăng nhập ở một nơi khác!';
            return response()->json($apiFormat);
        }

        return $next($request);
    }
}
