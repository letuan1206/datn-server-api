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
use phpDocumentor\Reflection\Types\Integer;
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
        $char_info = Character::select('Name', 'cLevel'
            , 'LevelUpPoint'
            , 'Point_Reserve'
            , 'Class'
            , 'Experience'
            , 'Strength'
            , 'Dexterity'
            , 'Vitality'
            , 'Energy'
            , 'Leadership'
            , 'Money'
            , 'MapNumber'
            , 'MapPosX'
            , 'MapPosY'
            , 'MapDir'
            , 'PkCount'
            , 'PkLevel'
            , 'PkTime'
            , 'Resets'
            , 'Relifes'
            , 'SCFMasterLevel'
            , 'SCFMasterPoints'
            , 'SCFMarried'
            , 'SCFMarryHusbandWife'
            , 'Lock_Item'
            , 'SCFSealItem'
            , 'SCFSealTime'
            , 'MDate'
            , 'Top_0h')
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

        // Lấy về cấu hình thông tin reset của nhân vật
        $pos_current = $this->dependence->getPositionResetConfig($char_info, $reset_config);
//        $reset_next_info = $this->dependence->calResetConfigInfo($char_info, $reset_config);
        $reset_next_info = $reset_config[$pos_current];
//        if (count($reset_in_day) >= $limit_reset) {
//            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
//            $apiFormat['message'] = 'Bạn đã đạt max reset trong ngày hôm nay. Hãy đợi đến ngày mai để reset tiếp';
//            return response()->json($apiFormat);
//        }

        if ($limit_reset === 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Bạn đã đạt max reset trong ngày hôm nay. Hãy đợi đến ngày mai để reset tiếp';
            return response()->json($apiFormat);
        }

//        if ($reset_next_info === 0) {
//            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
//            $apiFormat['message'] = 'Lỗi hệ thống! Liên hệ Admin để fix';
//            return response()->json($apiFormat);
//        }

        $level_reset = ($char_info->Resets + 1) * 5 + 200;
        if ($level_reset >= 400) {
            $level_reset = 400;
        }

        if ((int)$char_info->cLevel < (int)$level_reset) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Nhân vật không đủ Level để thực hiện Reset. Cần ' . $level_reset . 'Level';
            return response()->json($apiFormat);
        }

        if ((int)$char_info->Money < (int)$reset_next_info->zen_reset) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Nhân vật không đủ Zen trên người để thực hiện Reset';
            return response()->json($apiFormat);
        }

        // Tính point nhân được
        $point_add = 0;

        for ($i = 0; $i < count($reset_config); $i++) {
            if ($i >= $pos_current) {
                break;
            }

            if ($i == (count($reset_config) - 1)) {
                break;
            }

            $point_add += ($reset_config[$i + 1]->reset - $reset_config[$i]->reset) * $reset_config[$i]->point;
        }

        $point_add += (($char_info->Resets + 1) - $reset_config[$pos_current]->reset) * $reset_config[$i]->point;

        $default_class = DB::table('DefaultClassType')->select('Strength', 'Dexterity', 'Vitality', 'Energy', 'Leadership', 'MapNumber', 'MapPosX', 'MapPosY')->where('class', $this->dependence->getClassDefault($char_info->Class))->first();

        DB::insert('insert into Log_Resets (account, name, reset_type, reset_time) values (?, ?, ?, ?)', [$request->account, $request->name, Constains::RESET_TYPE['NORMAL'], date('Y-m-d H:i:s', time())]);
        DB::update("update Character set cLevel = ?, Resets = Resets + 1, LevelUpPoint = ?, Resets_Time = ?, 
                    Strength = ?, Dexterity = ?, Vitality = ?, Energy = ?, Leadership = ?, MapNumber = ?, MapPosX = ?, MapPosY = ?
                    where AccountID=? and Name=?",
            [SystemConfig::LEVEL_AFTER_RESET,
                $point_add,
                time(),
                $default_class->Strength,
                $default_class->Dexterity,
                $default_class->Vitality,
                $default_class->Energy,
                $default_class->Leadership,
                $default_class->MapNumber,
                $default_class->MapPosX,
                $default_class->MapPosY,
                $request->account,
                $request->name]);

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Reset nhân vật $request->name thành công";
        return response()->json($apiFormat);
    }

    public function resetVipCharacter(Request $request)
    {
        $char_info = Character::select('Name', 'cLevel'
            , 'LevelUpPoint'
            , 'Point_Reserve'
            , 'Class'
            , 'Experience'
            , 'Strength'
            , 'Dexterity'
            , 'Vitality'
            , 'Energy'
            , 'Leadership'
            , 'Money'
            , 'MapNumber'
            , 'MapPosX'
            , 'MapPosY'
            , 'MapDir'
            , 'PkCount'
            , 'PkLevel'
            , 'PkTime'
            , 'Resets'
            , 'Relifes'
            , 'SCFMasterLevel'
            , 'SCFMasterPoints'
            , 'SCFMarried'
            , 'SCFMarryHusbandWife'
            , 'Lock_Item'
            , 'SCFSealItem'
            , 'SCFSealTime'
            , 'MDate'
            , 'Top_0h')
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
        $acc_info = Memb_Info::select('memb___id', 'bank_sliver', 'bank_sliver_lock', 'bank_zen', 'bank_jewel', 'bank_jewel_lock')
            ->where('memb___id', $request->account)
            ->first();

        // Lấy về cấu hình thông tin reset của nhân vật
        $pos_current = $this->dependence->getPositionResetConfig($char_info, $reset_config);
        $reset_next_info = $reset_config[$pos_current];

        if ($limit_reset === 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Bạn đã đạt max reset trong ngày hôm nay. Hãy đợi đến ngày mai để reset tiếp';
            return response()->json($apiFormat);
        }

        if ($acc_info->bank_sliver < $reset_next_info->sliver) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Tài khoản của bạn không đủ Bạc để Reset VIP';
            return response()->json($apiFormat);
        }

        $level_reset = ($char_info->Resets + 1) * 5 + 200;
        if ($level_reset >= 400) {
            $level_reset = 400;
        }

        if ((int)$char_info->cLevel < (int)$level_reset) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Nhân vật không đủ Level để thực hiện Reset. Cần ' . $level_reset . 'Level';
            return response()->json($apiFormat);
        }

        // Tính point nhân được
        $point_add = 0;

        for ($i = 0; $i < count($reset_config); $i++) {
            if ($i >= $pos_current) {
                break;
            }

            if ($i == (count($reset_config) - 1)) {
                break;
            }

            $point_add += ($reset_config[$i + 1]->reset - $reset_config[$i]->reset) * $reset_config[$i]->point;
        }

        $point_add += (($char_info->Resets + 1) - $reset_config[$pos_current]->reset) * $reset_config[$i]->point;
        $point_add = floor(($point_add + 500) * 1.05);

        $default_class = DB::table('DefaultClassType')->select('Strength', 'Dexterity', 'Vitality', 'Energy', 'Leadership', 'MapNumber', 'MapPosX', 'MapPosY')->where('class', $this->dependence->getClassDefault($char_info->Class))->first();

        DB::update("update MEMB_INFO set bank_sliver = bank_sliver - ?, point_bonus = point_bonus + ? where memb___id=?", [$reset_next_info->sliver, SystemConfig::POINT_BONUS_RESET_VIP, $request->account]);
        DB::insert('insert into Log_Resets (account, name, reset_type, reset_time) values (?, ?, ?, ?)', [$request->account, $request->name, Constains::RESET_TYPE['VIP'], date('Y-m-d H:i:s', time())]);
        DB::update("update Character set cLevel = ?, Resets = Resets + 1, LevelUpPoint = ?, Resets_Time = ?, 
                    Strength = ?, Dexterity = ?, Vitality = ?, Energy = ?, Leadership = ?, MapNumber = ?, MapPosX = ?, MapPosY = ?
                    where AccountID=? and Name=?",
            [SystemConfig::LEVEL_AFTER_RESET,
                $point_add,
                time(),
                $default_class->Strength,
                $default_class->Dexterity,
                $default_class->Vitality,
                $default_class->Energy,
                $default_class->Leadership,
                $default_class->MapNumber,
                $default_class->MapPosX,
                $default_class->MapPosY,
                $request->account,
                $request->name]);

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Reset V.I.P nhân vật $request->name thành công";
        return response()->json($apiFormat);
    }

    public function resetVipPOCharacter(Request $request)
    {
        $char_info = Character::select('Name', 'cLevel'
            , 'LevelUpPoint'
            , 'Point_Reserve'
            , 'Class'
            , 'Experience'
            , 'Strength'
            , 'Dexterity'
            , 'Vitality'
            , 'Energy'
            , 'Leadership'
            , 'Money'
            , 'MapNumber'
            , 'MapPosX'
            , 'MapPosY'
            , 'MapDir'
            , 'PkCount'
            , 'PkLevel'
            , 'PkTime'
            , 'Resets'
            , 'Relifes'
            , 'SCFMasterLevel'
            , 'SCFMasterPoints'
            , 'SCFMarried'
            , 'SCFMarryHusbandWife'
            , 'Lock_Item'
            , 'SCFSealItem'
            , 'SCFSealTime'
            , 'MDate'
            , 'Top_0h')
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
        $acc_info = Memb_Info::select('memb___id', 'point_online')
            ->where('memb___id', $request->account)
            ->first();

        // Lấy về cấu hình thông tin reset của nhân vật
        $pos_current = $this->dependence->getPositionResetConfig($char_info, $reset_config);
        $reset_next_info = $reset_config[$pos_current];

        if ($limit_reset === 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Bạn đã đạt max reset trong ngày hôm nay. Hãy đợi đến ngày mai để reset tiếp';
            return response()->json($apiFormat);
        }

        if ($acc_info->point_online < $reset_next_info->point_online) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Tài khoản của bạn không đủ ' . $reset_next_info->point_online . ' Point Online để Reset VIP PO';
            return response()->json($apiFormat);
        }

        $level_reset = ($char_info->Resets + 1) * 5 + 200;
        if ($level_reset >= 400) {
            $level_reset = 400;
        }

        if ((int)$char_info->cLevel < (int)$level_reset) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Nhân vật không đủ Level để thực hiện Reset. Cần ' . $level_reset . 'Level';
            return response()->json($apiFormat);
        }

        // Tính point nhân được
        $point_add = 0;

        for ($i = 0; $i < count($reset_config); $i++) {
            if ($i >= $pos_current) {
                break;
            }

            if ($i == (count($reset_config) - 1)) {
                break;
            }

            $point_add += ($reset_config[$i + 1]->reset - $reset_config[$i]->reset) * $reset_config[$i]->point;
        }

        $point_add += (($char_info->Resets + 1) - $reset_config[$pos_current]->reset) * $reset_config[$i]->point + 1000;

        $default_class = DB::table('DefaultClassType')->select('Strength', 'Dexterity', 'Vitality', 'Energy', 'Leadership', 'MapNumber', 'MapPosX', 'MapPosY')->where('class', $this->dependence->getClassDefault($char_info->Class))->first();

        DB::update("update MEMB_INFO set point_online = point_online - ?, point_bonus = point_bonus + ? where memb___id=?", [$reset_next_info->point_online, SystemConfig::POINT_BONUS_RESET_VIP_PO, $request->account]);
        DB::insert('insert into Log_Resets (account, name, reset_type, reset_time) values (?, ?, ?, ?)', [$request->account, $request->name, Constains::RESET_TYPE['VIP_PO'], date('Y-m-d H:i:s', time())]);
        DB::update("update Character set cLevel = ?, Resets = Resets + 1, LevelUpPoint = ?, Resets_Time = ?, 
                    Strength = ?, Dexterity = ?, Vitality = ?, Energy = ?, Leadership = ?, MapNumber = ?, MapPosX = ?, MapPosY = ?
                    where AccountID=? and Name=?",
            [SystemConfig::LEVEL_AFTER_RESET,
                $point_add,
                time(),
                $default_class->Strength,
                $default_class->Dexterity,
                $default_class->Vitality,
                $default_class->Energy,
                $default_class->Leadership,
                $default_class->MapNumber,
                $default_class->MapPosX,
                $default_class->MapPosY,
                $request->account,
                $request->name]);

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Reset VIP PO nhân vật $request->name thành công";
        return response()->json($apiFormat);
    }
}
