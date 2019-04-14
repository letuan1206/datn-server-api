<?php

namespace App\Http\Controllers\Rank;

use App\Character;
use App\EckPrince\Constains;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;

class RankController extends Controller
{
    public function getRankAll(Request $request) {
        $apiFormat = array();

        $listUser = [];

        if($request->class_type == 'all') {
            $listUser = Character::select('name', 'Resets', 'Relifes', 'cLevel', 'Class', 'Resets_Time', 'LevelUp_Time')
                ->limit(50)
                ->orderBy('Relifes', 'desc')
                ->orderBy('Resets', 'desc')
                ->orderBy('cLevel', 'desc')
                ->get();
        } else {
            $arr = [];

            if ($request->class_type == 0) {
                $arr = [0, 1, 2, 3];
            }
            elseif ($request->class_type == 16) {
                $arr = [16, 17, 18, 19];
            }
            elseif ($request->class_type == 32) {
                $arr = [32, 33, 34, 35];
            }
            elseif ($request->class_type == 48) {
                $arr = [48, 49, 50];
            }
            elseif ($request->class_type == 64) {
                $arr = [64, 65, 66];
            }
            elseif ($request->class_type == 80) {
                $arr = [80, 81, 82, 83];
            }
            elseif ($request->class_type == 96) {
                $arr = [96, 97, 98];
            }

            $listUser = Character::select('name', 'Resets', 'Relifes', 'cLevel', 'Class', 'Resets_Time', 'LevelUp_Time')
                ->whereIn('Class', $arr)
                ->limit(50)
                ->orderBy('Relifes', 'desc')
                ->orderBy('Resets', 'desc')
                ->orderBy('cLevel', 'desc')
                ->get();
        }

        foreach ($listUser as $item) {
            $item->Resets_Time = date('d/m/Y H:i:s', $item->Resets_Time);
        }

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Tải thông tin thành công!';
        $apiFormat['data'] = $listUser;
        return response()->json($apiFormat);
    }

    public function getRankDay(Request $request)
    {
        $apiFormat = array();

        $listResetDay = DB::table('Log_Resets')->selectRaw('Log_Resets.name, Character.Class, COUNT(Log_Resets.name) as Resets')
            ->join('Character', 'Log_Resets.name', '=', 'Character.Name')
            ->whereDate('Log_Resets.reset_time', date('Y-m-d', time()))
            ->limit(50)
            ->groupBy(['Log_Resets.Name', 'Character.Class'])
            ->orderBy('Resets', 'desc')
            ->get();

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Tải thông tin thành công!';
        $apiFormat['data'] = $listResetDay;
        return response()->json($apiFormat);
    }

    public function getRankTop()
    {
        $apiFormat = array();

        $listCharacter = Character::select('Name','cLevel'
                                        ,'Class'
                                        ,'Reset_0h'
                                        ,'Relife_0h'
                                        ,'Level_0h'
                                        ,'Level_Time_0h'
                                        ,'Top_0h')
            ->limit(50)
            ->orderBy('Top_0h', 'asc')
            ->get();

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Tải thông tin thành công!';
        $apiFormat['data'] = $listCharacter;
        return response()->json($apiFormat);
    }

    public function getRankGuild()
    {
        $apiFormat = array();

        $listGuild = DB::table('GuildMember')->selectRaw('sum(Resets) as G_Reset, count(GuildMember.Name) as G_Count, GuildMember.G_Name, Guild.G_Master, Guild.G_Mark')
            ->join('Character', 'GuildMember.Name', '=', 'Character.Name')
            ->join('Guild', 'Guild.G_Name', '=', 'GuildMember.G_Name')
            ->limit(20)
            ->groupBy(['GuildMember.G_Name', 'Guild.G_Master', 'Guild.G_Mark'])
            ->orderBy('G_Reset', 'desc')
            ->get();

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Tải thông tin thành công!';
        $apiFormat['data'] = $listGuild;
        return response()->json($apiFormat);
    }

    public function getCharInGuild(Request $request) {
        $apiFormat = array();

        $validator = Validator::make($request->all(), [
            'guild_name' => 'required',
        ],
            [
                'guild_name.required' => 'Chưa chọn guild name',
            ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $message;
            return response()->json($apiFormat);
        }

        $listCharInGuild = DB::table('GuildMember')->selectRaw('GuildMember.Name, G_Level, Resets, Relifes, cLevel')
            ->join('Character', 'GuildMember.Name', '=', 'Character.Name')
            ->where('G_Name', $request->guild_name)
            ->orderBy('Relifes', 'desc')
            ->orderBy('Resets', 'desc')
            ->orderBy('cLevel', 'desc')
            ->get();

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Tải thông tin thành công!';
        $apiFormat['data'] = $listCharInGuild;
        return response()->json($apiFormat);
    }
}
