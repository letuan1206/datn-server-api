<?php

namespace App\Http\Controllers\Admin;

use App\Character;
use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use App\Memb_Info;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    private $dependence;

    public function __construct(AllFunctions $functions)
    {
        $this->dependence = $functions;
    }

    public function dashBoard(Request $request)
    {
        $member_info = DB::table('MEMB_INFO')->selectRaw("sum(bank_sliver) as bank_sliver, COUNT(*) as account_no, sum(bank_sliver_lock) as bank_sliver_lock")->first();
        $char_no = DB::table('Character')->selectRaw("COUNT(*) as character_no")->first();
        $char_online = DB::table('MEMB_STAT')->selectRaw("COUNT(*) as character_online")->where('ConnectStat', 1)->first();
        $card_today = DB::table('BK_Card_Phones')->selectRaw("SUM(CAST(card_value as int)) as card_today")
            ->where('Status', Constains::CARD_SUCCESS)->where('updated_at', date('Y-m-d', time()))->first();

        $data['bank_sliver'] = $member_info->bank_sliver;
        $data['account_no'] = $member_info->account_no;
        $data['bank_sliver_lock'] = $member_info->bank_sliver_lock;
        $data['char_no'] = $char_no->character_no;
        $data['char_online'] = $char_online->character_online;
        $data['card_today'] = $card_today->card_today;

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function getAccountList(Request $request)
    {
        $data = Memb_Info::select('memb___id', 'ctl1_code', 'bloc_code', 'mail_addr')
            ->where('memb___id', 'LIKE', "%$request->search_key%")
            ->orderBy('memb___id', 'asc')->get();

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function getAccountDetail(Request $request)
    {
        $user = Memb_Info::select('memb___id', 'memb_name', 'memb__pwd', 'pass2',
            'mail_addr', 'tel__numb', 'sno__numb', 'bloc_code', 'SCFIsVip', 'SCFVipDays',
            'bank_sliver', 'bank_sliver_lock', 'bank_zen', 'wcoin', 'wcoinp', 'bank_jewel', 'bank_jewel_lock')
            ->where('memb___id', $request->account)->first();

        $status_online = DB::table('MEMB_STAT')->select('ConnectStat')->where('memb___id', $request->account)->first();
        if (count($status_online) > 0) {
            $user['ConnectStat'] = $status_online->ConnectStat;
        } else {
            $user['ConnectStat'] = 0;
        }

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Tải thông tin thành công!';
        $apiFormat['data'] = $user;

        return response()->json($apiFormat);
    }

    public function getCharacterList(Request $request)
    {
        $data = Character::select('Name', 'CtlCode')
            ->where('Name', 'LIKE', "%$request->search_key%")
            ->orderBy('Name', 'asc')->get();

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function getCharacterById(Request $request)
    {
        $data = Character::select('Name', 'Resets')
            ->where('AccountID', $request->account)
            ->orderBy('Name', 'asc')->get();

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function viewLogs(Request $request) {
        switch ($request->type) {
            case Constains::LOG_TYPE["ALL"]:
                break;
        }

    }

    public function getLogAll($account) {

    }

    public function getLogReset($account)
    {

    }
}
