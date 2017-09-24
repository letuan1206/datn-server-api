<?php

namespace App\Http\Controllers\Account;

use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use App\Memb_Info;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;

class LoginController extends Controller
{
    //
    private $dependence;

    public function __construct(AllFunctions $functions)
    {
        $this->dependence = $functions;
    }

    public function login(Request $request)
    {
        $apiFormat = array();

        $validator = Validator::make($request->all(), [
            'account' => 'required',
            'pass' => 'required|min:6',

        ],
            [
                'account.required' => 'Tên tài khoản không được rỗng',
                'pass.required' => 'Chưa điền mật khẩu game',

            ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $errors;
            return response()->json($apiFormat);
        }

        $user = Memb_Info::select('memb___id',
            'memb_name',
            'bank_sliver',
            'bank_sliver_lock',
            'bank_zen',
            'wcoin',
            'wcoinp',
            'bank_jewel',
            'bank_jewel_lock',
            DB::raw('LEFT(mail_addr, 5) as mail_addr, RIGHT(tel__numb, 4) as tel__numb, LEFT(sno__numb, 3) as sno__numb'))
            ->where('memb___id', $request->account)
            ->where('memb__pwd', $request->pass)->first();

        if (count($user) > 0) {
            $status_online = DB::table('MEMB_STAT')->select('ConnectStat')->where('memb___id', $request->account)->first();
            if (count($status_online) > 0) {
                $user['ConnectStat'] = $status_online->ConnectStat;
            } else {
                $user['ConnectStat'] = 0;
            }

            $login_token = $this->dependence->randStrGen(40);
            DB::update('update memb_info set checklogin = ? where memb___id = ?', [$login_token, $request->account]);
            DB::insert('insert into log_login (account, ip, time, description) values (?, ?, ?, ?)', [$request->account, $request->ip, time(), "Đăng nhập thành công"]);
            $user['login_token'] = $login_token;

            $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
            $apiFormat['message'] = 'Đăng nhập thành công!';
            $apiFormat['data'] = $user;
        } else {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Tên đăng nhập hoặc mật khẩu không đúng!';
            DB::insert('insert into log_login (account, ip, time, description) values (?, ?, ?, ?)', [$request->account, $request->ip, time(), "Tên đăng nhập hoặc mật khẩu không đúng!"]);
        }

        return response()->json($apiFormat);
    }
}
