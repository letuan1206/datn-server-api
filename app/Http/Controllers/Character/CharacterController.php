<?php

namespace App\Http\Controllers\Character;

use App\Character;
use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class CharacterController extends Controller
{
    private $dependence;

    public function __construct(AllFunctions $functions)
    {
        $this->dependence = $functions;
    }

    public function getInfoCharacter(Request $request)
    {
        $listChar = Character::selectRaw('
                               [Name]
                              ,[cLevel]
                              ,[LevelUpPoint]
                              ,[Class]
                              ,[Experience]
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
                              ,[khoado]
                              ,[UyThac]
                              ,[PointUyThac]
                              ,[Top50]
                              ,[SCFSealItem]
                              ,[SCFSealTime]
                              ,[MDate]
                              ')
            ->where('AccountID', $request->memb___id)
            ->orderBy('Relifes', 'desc')
            ->orderBy('Resets', 'desc')->get()->toArray();

        $status_online = 0;
        $check_online = DB::table('MEMB_STAT')->select('ConnectStat')->where('memb___id', $request->memb___id)->first();
        if (count($check_online) > 0) {
            $status_online = $check_online->ConnectStat;
        }

        $check_select_char = DB::table('AccountCharacter')->select('GameIDC')->where('Id', $request->memb___id)->first();

        $data = array();
        foreach ($listChar as $char) {
            $char['online'] = $status_online;

            if ($char['Name'] == $check_select_char->GameIDC) {
                $char['doinv'] = 0;
            } else {
                $char['doinv'] = 1;
            }

            $char['reset_day'] = 0; //get_reset_day($char['Name']);

            array_push($data, $char);
        }

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin nhân vật thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }
}
