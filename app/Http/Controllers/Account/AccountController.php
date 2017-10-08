<?php

namespace App\Http\Controllers\Account;

use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use App\EckPrince\SystemConfig;
use App\Memb_Info;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;

class AccountController extends Controller
{
    private $dependence;

    public function __construct(AllFunctions $functions)
    {
        $this->dependence = $functions;
    }

    public function getSMSService(Request $request)
    {
        // 1. Nhan du lieu request tu iNET gui qua
//        $code = $request->get('code');            // Ma chinh
//        $subCode = $request->get('subCode');         // Ma phu
//        $info = $request->get('info');            // Noi dung tin nhan

//        $mobile = str_replace('+84', '0', $request->get('mobile'));          // So dien thoai +84
        $mobile = '0' . substr($request->get('mobile'), 2);
        $info = strtoupper(str_replace("  ", " ", $request->get('info')));
        $info = explode(SystemConfig::SMS_SYNC_TAX . ' ', $info);

        if (!isset($info[1])) {
            return "0|Sai cu phap. Lien he Admin de fix";
        }
        $info_arr = explode(' ', $info[1]);
        switch ($info_arr[0]) {
            default:
                $check_sms = DB::table('BK_SMS_Service')->select('account', 'phone_number', 'sms_code', 'sms_type', 'time', 'status', 'info_change')
                    ->where('sms_code', $info_arr[0])
                    ->where('status', Constains::SMS_STATUS_PENDING)
                    ->where('time', '>', time() - SystemConfig::SMS_TIME_REMAINING * 60)
                    ->first();

                if (count($check_sms) > 0) {
                    $acc_info = Memb_Info::select('tel__numb')
                        ->where('memb___id', $check_sms->account)
                        ->where('tel__numb', $mobile)
                        ->first();

                    if (count($acc_info) == 0) {
                        return "0|Khong tim thay thong tin tai khoan!";
                    }
                    if ($acc_info->tel__numb == '' || $acc_info->tel__numb == null) {
                        return "0|Sai thong tin tai khoan hoac so dien thoai!";
                    }

                    $flag = true;
                    $msg = "Loi! Ma code sai. Lien he admin de fix!";
                    $login_token = $this->dependence->randStrGen(40);
                    switch ($check_sms->sms_type) {
                        case Constains::SMS_TYPE['PASS1']:
                        case Constains::SMS_TYPE['FORGOT_PASS']:
                            $msg = "Tai khoan: $check_sms->account doi mat khau cap 1 thanh $check_sms->info_change";
                            DB::update('update MEMB_INFO set memb__pwd = ?, checklogin = ? where memb___id = ?', [$check_sms->info_change, $login_token, $check_sms->account]);
                            break;
                        case Constains::SMS_TYPE['PASS2']:
                            $msg = "Tai khoan: $check_sms->account doi mat khau cap 2 thanh $check_sms->info_change";
                            DB::update('update MEMB_INFO set memb__pwdmd5 = ?, pass2 = ? where memb___id = ?', [md5($check_sms->info_change), $check_sms->info_change, $check_sms->account]);
                            break;
                        case Constains::SMS_TYPE['SNO_NUMBER']:
                            $msg = "Tai khoan: $check_sms->account doi 7 so bi mat thanh $check_sms->info_change";
                            DB::update('update MEMB_INFO set sno__numb = ? where memb___id = ?', [$check_sms->info_change, $check_sms->account]);
                            break;
                        case Constains::SMS_TYPE['PHONE_NUMBER']:
                            $msg = "Tai khoan: $check_sms->account doi so dien thoai thanh $check_sms->info_change";
                            DB::update('update MEMB_INFO set tel__numb = ?, checklogin = ? where memb___id = ?', [$check_sms->info_change, $login_token, $check_sms->account]);
                            break;
                        case Constains::SMS_TYPE['EMAIL']:
                            $msg = "Tai khoan: $check_sms->account doi email thanh $check_sms->info_change";
                            DB::update('update MEMB_INFO set mail_addr = ? where memb___id = ?', [$check_sms->info_change, $check_sms->account]);
                            break;
                        default:
                            $flag = false;
                            break;
                    }

                    if ($flag) {
                        DB::update('update BK_SMS_Service set status = 1 where sms_code = ? and account = ?', [$check_sms->sms_code, $check_sms->account]);
                    }
                    return "0|$msg";
                } else {
                    return "0|Ma khong ton tai hoac da het han!";
                }
                break;
        }
    }

    public function changeAccountInfoUseSMS(Request $request)
    {
        switch ($request->action_type) {
            case Constains::SMS_TYPE['PASS1']:
            case Constains::SMS_TYPE['PASS2']:
                $req = 'required|min:6|max:20';
                break;
            case Constains::SMS_TYPE['SNO_NUMBER']:
                $req = 'required|regex:/^[0-9]{7,20}$/';
                break;
            case Constains::SMS_TYPE['PHONE_NUMBER']:
                $req = 'required|regex:/^[0-9]{10,11}$/';
                break;
            case Constains::SMS_TYPE['EMAIL']:
                $req = 'required|email';
                break;
            case Constains::SMS_TYPE['FORGOT_PASS'];
                $req = 'required|min:6|max:20';
                break;
            default:
                $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                $apiFormat['message'] = "Sai cú pháp. <br>Liên hệ Admin để fix";
                return response()->json($apiFormat);
                break;

        }
        $validator = Validator::make($request->all(), [
            'info_change' => $req,
        ],
            [
                'info_change.required' => 'Vui lòng điền thông tin cần thay đổi!',
                'info_change.email' => 'Vui lòng nhập đúng định dạng Email',
                'info_change.regex' => 'Vui lòng nhập đúng định dạng số điện thoại'
            ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $errors;
            return response()->json($apiFormat);
        }

        $sms_code = $this->dependence->randomPassword(3);
        $acc_info = Memb_Info::select('tel__numb')->where('memb___id', $request->account)->first();

        if ($acc_info->tel__numb == '' || $acc_info->tel__numb == null) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Bạn chưa cập nhật số điện thoại.<br>Vui lòng cập nhật số điện thoại trước khi thực hiện chức năng này';
            return response()->json($apiFormat);
        }

        DB::insert("insert into BK_SMS_Service (account, phone_number, sms_code, sms_type, time, status, info_change) values (?, ?, ?, ?, ?, ?, ?)",
            [$request->account, $acc_info->tel__numb, $sms_code, $request->action_type, time(), Constains::SMS_STATUS_PENDING, $request->info_change]);

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Để thay đổi thông tin: Vui lòng nhắn tin theo cú pháp dưới đây. <br><strong>" . SystemConfig::SMS_SYNC_TAX . " " . $sms_code . " gửi " . SystemConfig::SMS_HEAD_PHONE .
            "</strong><br>Mã có hiệu lực trong " . SystemConfig::SMS_TIME_REMAINING . " phút";
        return response()->json($apiFormat);
    }
}
