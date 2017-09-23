<?php

namespace App\Http\Controllers\Account;

use App\EckPrince\Constains;
use App\Memb_Info;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Validator;

class RegisterController extends Controller
{
    public function postRegister(Request $request) {
        $apiFormat = array();

        $validator = Validator::make($request->all(), [
            'username' => 'required',
            'password' => 'required|min:6',
            'pass2' => 'required|min:6',
//            'question' => 'required',
//            'answer' => 'required',
            'numb' => 'required|numeric',
            'email' => 'required',
            'type_email' => 'required',
            'phone' => 'required|numeric'
        ],
            [
                'username.required' => 'Tên tài khoản không được rỗng',
                'password.required' => 'Chưa điền mật khẩu game',
                'pass2.required' => 'Chưa điền mật khẩu Web cấp 2',
//                'question.required' => 'Chưa chọn câu hỏi',
//                'answer.required' => 'Chưa điền câu trả lời bí mật',
                'numb.required' => 'Chưa điền 7 số bí mật',
                'email.required' => 'Chưa điền email',
                'type_email.required' => 'Chưa chọn kiểu email',
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
        $user->memb___id = $request->username;
        $user->memb__pwd = $request->password;
        $user->memb_name = $request->username;
//        $user->memb__pwd2 = $request->pass1;
        $user->memb__pwdmd5 = md5($request->pass2);
        $user->pass2 = $request->pass2;
//        $user->fpas_ques = $request->question;
//        $user->fpas_answ = $request->answer;
        $user->sno__numb = $request->numb;
        $user->mail_addr = $request->email;
        $user->tel__numb = $request->phone;
        $user->ctl1_code = 0;
        $user->ip = $request->ip;

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
