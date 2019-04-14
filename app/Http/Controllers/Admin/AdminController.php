<?php

namespace App\Http\Controllers\Admin;

use App\Character;
use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use App\EckPrince\ItemFunction;
use App\Http\Controllers\Account\WebShopController;
use App\Memb_Info;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Validator;

class AdminController extends Controller
{
    private $dependence;
    private $itemFunc;

    public function __construct(AllFunctions $functions, ItemFunction $itemFunction)
    {
        $this->dependence = $functions;
        $this->itemFunc = $itemFunction;
    }

    public function login(Request $request)
    {
        $apiFormat = array();

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'pass' => 'required|min:6',

        ],
            [
                'email.required' => 'Email không được rỗng',
                'pass.required' => 'Chưa điền mật khẩu game',

            ]);

        if ($validator->fails()) {
            $errors = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $errors;
            return response()->json($apiFormat);
        }

        $user = Memb_Info::select('memb___id', 'mail_addr')
            ->where('mail_addr', $request->email)
            ->where('memb__pwd', $request->pass)
            ->where('permission', 1)->first();

        if (count($user) > 0) {
            $login_token = Hash::make($this->dependence->randStrGen(40));
            DB::update('update memb_info set login_token = ? where mail_addr = ?', [$login_token, $request->email]);
            DB::insert('insert into log_login (account, ip, time, description) values (?, ?, ?, ?)', ['Admin', $request->ip, time(), $request->email . " Đăng nhập vào Web Admin thành công"]);
            $user['login_token'] = $login_token;

            $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
            $apiFormat['message'] = 'Đăng nhập thành công!';
            $apiFormat['data'] = $user;
        } else {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Tên đăng nhập hoặc mật khẩu không đúng!';
            DB::insert('insert into log_login (account, ip, time, description) values (?, ?, ?, ?)', ['Admin', $request->ip, time(), "Tên đăng nhập hoặc mật khẩu không đúng!"]);
        }

        return response()->json($apiFormat);
    }

    public function dashBoard(Request $request)
    {
        $member_info = DB::table('MEMB_INFO')->selectRaw("sum(bank_sliver) as bank_sliver, COUNT(*) as account_no, sum(bank_sliver_lock) as bank_sliver_lock")->first();
        $char_no = DB::table('Character')->selectRaw("COUNT(*) as character_no")->first();
        $char_online = DB::table('MEMB_STAT')->selectRaw("COUNT(*) as character_online")->where('ConnectStat', 1)->first();
        $card_today = DB::table('BK_Card_Phones')->selectRaw("SUM(CAST(card_value as int)) as card_today")
            ->where('Status', Constains::CARD_SUCCESS)->where('updated_at', date('Y-m-d', time()))->first();
        $card_month = DB::table('BK_Card_Phones')->selectRaw("SUM(CAST(card_value as int)) as card_month")
            ->where('Status', Constains::CARD_SUCCESS)->whereMonth('updated_at', date('m', time()))->first();

        $data['bank_sliver'] = $member_info->bank_sliver;
        $data['bank_sliver_lock'] = $member_info->bank_sliver_lock;
        $data['account_total'] = $member_info->account_no;
        $data['char_total'] = $char_no->character_no;
        $data['char_online'] = $char_online->character_online;
        $data['card_today'] = $card_today->card_today;
        $data['card_month'] = $card_month->card_month;

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function getAccountCharacterOnline()
    {
        $data = DB::table('Character')->selectRaw('MEMB_INFO.memb___id, Name, Resets, Class, Clevel, Strength, Dexterity, Vitality, Energy, 
                        MapNumber, MapPosX, MapPosY, PkLevel, PkCount, Leadership, LevelUpPoint, ctlcode, relifes, ServerName, 
                        MEMB_STAT.IP, MEMB_INFO.ip,ConnectTM')
            ->join('MEMB_INFO', 'MEMB_INFO.memb___id', '=', 'Character.AccountID')
            ->join('AccountCharacter', 'AccountCharacter.GameIDC', '=', 'Character.Name')
            ->join('MEMB_STAT', 'Character.AccountID', '=', 'MEMB_STAT.memb___id')
            ->where('ConnectStat', 1)
            ->orderBy('ServerName', 'desc')
            ->orderBy('Name', 'desc')
            ->get();

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

    public function getWareHouse(Request $request)
    {
        return app('App\Http\Controllers\Account\WebShopController')->getItemWareHouseList($request);
    }

    public function getItemWebShop(Request $request)
    {
        return app('App\Http\Controllers\Account\WebShopController')->getItemWebShopList($request);
    }

    public function addItemWebShop(Request $request)
    {
        $apiFormat = array();
        $itemAddList = $request->itemAddList;
        $itemAdd = [];
        foreach ($itemAddList as $item) {
            if ($item['itemCode'] !== '') {
                $itemInfo = $this->itemFunc->ItemInfo($this->itemFunc->ItemDataArr(), $item['itemCode']);

                $itemObj['code'] = $itemInfo['image'];
                $itemObj['item_code'] = $item['itemCode'];
                $itemObj['item_type'] = $itemInfo['item_group'];
                $itemObj['item_price'] = $item['itemPrice'];
                $itemObj['time_up'] = date('Y-m-d H:i:s', time());
                $itemObj['dw'] = $itemInfo['dw'];
                $itemObj['dk'] = $itemInfo['dk'];
                $itemObj['elf'] = $itemInfo['elf'];
                $itemObj['mg'] = $itemInfo['mg'];
                $itemObj['dl'] = $itemInfo['dl'];
                $itemObj['sum'] = $itemInfo['sum'];
                $itemObj['rf'] = $itemInfo['rf'];
                $itemObj['status'] = 1;

                array_push($itemAdd, $itemObj);
            }
        }

        DB::table('BK_Web_Shops')->insert($itemAdd);

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Đưa đồ lên Web Shop thành công!";
        return response()->json($apiFormat);
    }

    public function deleteItemWebShop(Request $request)
    {
        DB::table('BK_Web_Shops')->where('item_code', $request->itemCode)->delete();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Xóa đồ Web Shop thành công!";
        return response()->json($apiFormat);
    }

    public function updateItemWebShop(Request $request)
    {
        DB::table('BK_Web_Shops')->where('item_code', $request->itemCode)->update(['item_price' => $request->itemPrice]);
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Update Web Shop thành công!";
        return response()->json($apiFormat);
    }

    public function updateAccount(Request $request)
    {
        DB::table('MEMB_INFO')->where('memb___id', $request->account)
            ->update((array)$request->dataUpdate);

        return $this->getAccountDetail($request);
    }

    public function getCharacterList(Request $request)
    {
        $data = Character::select('Name', 'CtlCode', 'AccountID')
            ->where('Name', 'LIKE', "%$request->search_key%")
            ->orderBy('Name', 'asc')->get();

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function getCharacterDetail(Request $request)
    {
        $listChar = Character::selectRaw('
                              Name, cLevel, AccountID
                              ,[LevelUpPoint]
                              ,[Class]
                              ,[Strength]
                              ,[Dexterity]
                              ,[Vitality]
                              ,[Energy]
                              ,[Leadership]
                              ,[Money]
                              ,[MapNumber]
                              ,[MapPosX]
                              ,[MapPosY]
                              ,[MapDir]
                              ,[PkCount]
                              ,[PkLevel]
                              ,[PkTime]
                              ,[Resets]
                              ,[Relifes]
                              ,[SCFMasterLevel]
                              ,[SCFMasterPoints]
                              ,[SCFMarried]
                              ,[SCFMarryHusbandWife]
                              ,[Lock_Item]
                              ,[MDate]
                              ,[Top_0h]
                              ')
            ->where('Name', $request->name)
            ->orderBy('Relifes', 'desc')
            ->orderBy('Resets', 'desc')->get()->toArray();

        $status_online = 0;
        $check_online = DB::table('MEMB_STAT')->select('ConnectStat', 'ServerName')->where('memb___id', $request->account)->first();
        if (count($check_online) > 0) {
            $status_online = $check_online->ConnectStat;
        }

        $data = array();
        foreach ($listChar as $char) {
            $char['Online'] = $status_online;
            $char['Online_Sub'] = $check_online->ServerName;

            $char['Reset_Day'] = $this->dependence->get_reset_day($request->account, $char['Name']);
            $char['Reset_Month'] = $this->dependence->get_reset_month($request->account, $char['Name']);
            array_push($data, $char);
        }

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin nhân vật thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function updateCharacter(Request $request)
    {
        DB::table('Character')->where('AccountID', $request->account)->where('Name', $request->name)
            ->update((array)$request->dataUpdate);

        return $this->getCharacterDetail($request);
    }

    public function viewLogs(Request $request)
    {
        switch ($request->type) {
            case Constains::LOG_TYPE["ALL"]:
                break;

            case  Constains::LOG_TYPE["SMS"]:
                return $this->getLogSMS($request->account);
                break;

            case Constains::LOG_TYPE["RESET"]:
                return $this->getLogReset($request->account);
                break;
            case Constains::LOG_TYPE["CARD"]:
                return $this->getLogCardPhone($request->account);
                break;
            case Constains::LOG_TYPE["BANK_TRANFER"]:
                return $this->getLogBankTranfer($request->account);
                break;
            case Constains::LOG_TYPE["ITEM_SLIVER_CHANGE"]:
                return $this->getLogItemSliverChange($request->account);
                break;
            case Constains::LOG_TYPE["LOGIN"]:
                return $this->getLogLogin($request->account);
                break;

            default:
                break;
        }

        $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
        $apiFormat['message'] = 'Không tìm thấy dữ liệu!';
        return response()->json($apiFormat);
    }

    public function getLogAll($account)
    {

    }

    public function getLogSMS($account)
    {
        $data = DB::table("BK_SMS_Service")->orderBy('time', 'desc')->get();

        if ($account) {
            $data = DB::table("BK_SMS_Service")->where('account', 'LIKE', "%$account%")->orderBy('time', 'desc')->get();
        }

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function getLogReset($account)
    {
        $data = DB::table("Log_Resets")->orderBy('reset_time', 'desc')->get();

        if ($account) {
            $data = DB::table("Log_Resets")->where('account', 'LIKE', "%$account%")->orderBy('reset_time', 'desc')->get();
        }

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function getLogCardPhone($account)
    {
        $data = DB::table("BK_Card_Phones")->orderBy('id', 'desc')->get();

        if ($account) {
            $data = DB::table("BK_Card_Phones")->where('account', 'LIKE', "%$account%")->orderBy('id', 'desc')->get();
        }

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function getLogBankTranfer($account)
    {
        $data = DB::table("Log_BankTransfer")->orderBy('time', 'desc')->get();

        if ($account) {
            $data = DB::table("Log_BankTransfer")
                ->where('from_account', 'LIKE', "%$account%")
                ->orWhere('to_account', 'LIKE', "%$account%")
                ->orderBy('time', 'desc')->get();
        }

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function getLogItemSliverChange($account)
    {
        $data = DB::table("Log_Item_Sliver_Change")->orderBy('time', 'desc')->get();

        if ($account) {
            $data = DB::table("Log_Item_Sliver_Change")
                ->where('account', 'LIKE', "%$account%")
                ->orderBy('time', 'desc')->get();
        }

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function getLogLogin($account)
    {
        $data = DB::table("Log_Login")->orderBy('time', 'desc')->get();

        if ($account) {
            $data = DB::table("Log_Login")
                ->where('account', 'LIKE', "%$account%")
                ->orderBy('time', 'desc')->get();
        }

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }
}
