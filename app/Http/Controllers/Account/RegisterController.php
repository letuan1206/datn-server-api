<?php

namespace App\Http\Controllers\Account;

use App\EckPrince\Constains;
use App\Memb_Info;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class RegisterController extends Controller
{
    public function register(Request $request) {
        $apiFormat = array();

        $validator = Validator::make($request->all(), [
            'account' => 'required|unique:memb_info,memb___id',
            'pass1' => 'required|min:6',
            'pass2' => 'required|min:6',
            'email' => 'required|unique:memb_info,mail_addr',
            'phone' => 'required|numeric'
        ],
            [
                'account.required' => 'Tên tài khoản không được rỗng',
                'account.unique' => 'Tên tài khoản đã được sử dụng',
                'pass1.required' => 'Chưa điền mật khẩu game',
                'pass2.required' => 'Chưa điền mật khẩu Web cấp 2',
                'email.required' => 'Chưa điền email',
                'email.unique' => 'Địa chỉ email đã có người sử dụng',
                'phone.required' => 'Chưa nhập số điện thoại'
            ]);

        if ($validator->fails()) {

            $errors = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $errors;
            return response()->json($apiFormat);
        }
        //memb___id,memb__pwd,memb_name,sno__numb,mail_addr,appl_days,modi_days,out__days,
        //true_days,mail_chek,bloc_code,ctl1_code,memb__pwd2,fpas_ques,fpas_answ,pass2,memb__pwdmd5,
        //tel__numb,time_checksms,thehe, ip

        $user = new Memb_Info();
        $user->memb___id = $request->account;
        $user->memb__pwd = $request->pass1;
        $user->memb_name = $request->account;
//        $user->memb__pwd2 = $request->pass1;
        $user->memb__pwdmd5 = md5($request->pass2);
        $user->pass2 = $request->pass2;
//        $user->fpas_ques = $request->question;
//        $user->fpas_answ = $request->answer;
        $user->sno__numb = '11111111111111111';
        $user->mail_addr = $request->email;
        $user->tel__numb = $request->phone;
        $user->ctl1_code = 0;
//        $user->ip = $request->ip;

        if ($user->save()) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
            $apiFormat['message'] = 'Đăng kí thành công!';
        } else {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Đăng kí thất bại!';
        }
        return response()->json($apiFormat);
    }
}
