<?php

namespace App\Http\Controllers\Character;

use App\Character;
use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use App\EckPrince\SystemConfig;
use App\Memb_Info;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;

class ResetController extends Controller
{
    private $dependence;

    public function __construct(AllFunctions $functions)
    {
        $this->dependence = $functions;
    }

    public function getResetInfo()
    {
        $reset_config = DB::table('BK_Config_Reset')->get();

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin cấu hình thành công!';
        $apiFormat['data'] = $reset_config;

        return response()->json($apiFormat);
    }

    public function resetCharacter(Request $request)
    {
        $char_info = Character::select('Name','cLevel'
                                    ,'LevelUpPoint'
                                    ,'Point_Reserve'
                                    ,'Class'
                                    ,'Experience'
                                    ,'Strength'
                                    ,'Dexterity'
                                    ,'Vitality'
                                    ,'Energy'
                                    ,'Leadership'
                                    ,'Money'
                                    ,'MapNumber'
                                    ,'MapPosX'
                                    ,'MapPosY'
                                    ,'MapDir'
                                    ,'PkCount'
                                    ,'PkLevel'
                                    ,'PkTime'
                                    ,'Resets'
                                    ,'Relifes'
                                    ,'SCFMasterLevel'
                                    ,'SCFMasterPoints'
                                    ,'SCFMarried'
                                    ,'SCFMarryHusbandWife'
                                    ,'Lock_Item'
                                    ,'SCFSealItem'
                                    ,'SCFSealTime'
                                    ,'MDate'
                                    ,'Top_0h')
            ->where('AccountID', $request->account)
            ->where('Name', $request->name)
            ->first();

        if (empty($char_info)) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Không tìm thấy thông tin nhân vật';
            return response()->json($apiFormat);
        }

        // TODO check đổi nhân vật, mật khẩu
        $list_ghrs = DB::table('BK_Config_Limit_Reset')->get();
        $list_relife = DB::table('BK_Config_Relife')->get();
        // Thông tin reset nhân vật TOP 1
        $char_top_1 = Character::selectRaw('[Name],[cLevel],[Resets],[Relifes],[Top_0h]')->where('Top_0h', 1)->first();
        // Giới hạn reset của nhân vật
        $limit_reset = $this->dependence->calculateLimitReset($char_info, $list_ghrs, $list_relife, $char_top_1);
        // Cấu hình reset
        $reset_config = DB::table('BK_Config_Reset')->get();
        // Kiểm tra điều kiện reset của nhân vật
        $reset_in_day = DB::table('Log_Resets')
            ->whereDate('reset_time', date('Y-m-d H:i:s', time()))
            ->where('account', $request->account)
            ->where('name', $request->name)
            ->get();

        $reset_next_info = $this->dependence->calResetConfigInfo($char_info, $reset_config);

        if(count($reset_in_day) >= $limit_reset) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Bạn đã đạt max reset trong ngày hôm nay. Hãy đợi đến ngày mai để reset tiếp';
            return response()->json($apiFormat);
        }

        if($reset_next_info === 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Lỗi hệ thống! Liên hệ Admin để fix';
            return response()->json($apiFormat);
        }

//        for($i = 0; $i < $reset_config; $i++) {
//            if((int)$char_info['Resets'] > (int)$reset_config[$i]->reset) {
//                $reset_info =  $reset_config[$i];
//                $reset_info_pos = $i;
//            }
//        }

        DB::insert('insert into Log_Resets (account, name, reset_type, reset_time) values (?, ?, ?, ?)', [$request->account, $request->name, Constains::RESET_TYPE['NORMAL'], date('Y-m-d H:i:s', time())]);
        DB::update('update Character set cLevel = ?, Resets = Resets + 1, Resets_Time = ? where AccountID = ? and Name = ?', [SystemConfig::LEVEL_AFTER_RESET, time(), $request->account, $request->namne]);

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Reset nhân vật $request->name thành công";
        return response()->json($apiFormat);
    }
}
