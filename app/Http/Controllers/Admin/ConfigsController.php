<?php

namespace App\Http\Controllers\Admin;

use App\Character;
use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use App\Memb_Info;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class ConfigsController extends Controller
{
    private $dependence;

    public function __construct(AllFunctions $functions)
    {
        $this->dependence = $functions;
    }

    public function getConfigReset()
    {
        $data = DB::table('BK_Config_Reset')->orderBy('id', 'asc')->get();

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function postConfigReset(Request $request)
    {
        DB::table('BK_Config_Reset')->truncate();
        DB::table('BK_Config_Reset')
            ->insert($request->params);

        return $this->getConfigReset();
    }

    public function getConfigLimitReset()
    {
        $data = DB::table('BK_Config_Limit_Reset')->orderBy('reset_top', 'asc')->get();

        $apiFormat = array();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Lấy thông tin thành công!';
        $apiFormat['data'] = $data;

        return response()->json($apiFormat);
    }

    public function postConfigLimitReset(Request $request)
    {
        DB::table('BK_Config_Limit_Reset')->truncate();
        DB::table('BK_Config_Limit_Reset')
            ->insert($request->params);

        return $this->getConfigLimitReset();
    }

}
