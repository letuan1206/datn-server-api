<?php

namespace App\Http\Controllers\Account;

use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use App\EckPrince\ItemFunction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class WebShopController extends Controller
{
    protected $dependence;
    protected $itemFunc;

    public function __construct(AllFunctions $functions, ItemFunction $itemFunction)
    {
        $this->dependence = $functions;
        $this->itemFunc = $itemFunction;
    }

    public function getItemWareHouseList(Request $request)
    {
        $apiFormat = array();
        $warehouse_query = DB::table('warehouse')->select('Items')->where('AccountID', $request->account)->first();
        $warehouse = $warehouse_query->Items;
        $warehouse = strtoupper($warehouse);
        $warehouse1 = substr($warehouse, 0, 120 * 32);
        $warehouse2 = substr($warehouse, 120 * 32);

        $result = [];
        $listSeri = [];
        for ($i = 0; $i < 120; $i++) {
            $item = substr($warehouse, $i * 32, 32);

            $item_serial = substr($item, 6, 8);
            $item_serial_dex = hexdec($item_serial);

            if (($item == 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF') || ($item_serial_dex > 4294967280) || ($item_serial_dex == 0)) {
                continue;
            }
            $itemInfo = $this->itemFunc->ItemInfo($this->itemFunc->ItemDataArr(), $item);
            $itemInfo['item_code'] = $item;
            $itemInfo['position'] = $i;
            array_push($listSeri, $item_serial);
            array_push($result, $itemInfo);
        }

        if(!$this->dependence->check_duplicate_item_serial($listSeri)) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Thùng đồ có Item bất hợp pháp. Liên hệ Admin để Fix!';
            return response()->json($apiFormat);
        }

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Success";
        $apiFormat['data'] = $result;//ItemInfo(ItemDataArr(), "4000FF007F90920000D0000000000000");
        return response()->json($apiFormat);
    }

    public function addItemToSuperMarket(Request $request)
    {
        $apiFormat = array();

        if ($this->dependence->check_online($request->account) == 1) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Bạn chưa thoát game!';
            return response()->json($apiFormat);
        }

        $warehouse_query = DB::table('warehouse')->select('Items')->where('AccountID', $request->account)->first();
        $warehouse = $warehouse_query->Items;
        $warehouse = strtoupper($warehouse);
        $warehouse1 = substr($warehouse, 0, 120 * 32);
        $warehouse2 = substr($warehouse, 120 * 32);

        $result = [];
        $no_item = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';

        foreach ($request->list_item as $item) {
            $pos = stripos($warehouse1, $item['item_code']);

            if ($pos) {
                DB::insert('insert into BK_Super_Market (account, item_code, item_type, item_price, time_up, status, code, name, dw, dk, elf, mg, dl, sum, rf) values (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                    [$request->account, $item['item_code'], $item['item_type'], $item['item_price'], date_format(date_create(), 'Y-m-d H:i:s'), 0, $item['code'], $request->name, $item['dw'], $item['dk'], $item['elf'], $item['mg'], $item['dl'], $item['sum'], $item['rf']]);
            }

            $warehouse1 = substr_replace($warehouse1, $no_item, $pos, 32);
        }

        $warehouse_new = $warehouse1 . $warehouse2;
        DB::update('UPDATE warehouse set Items=0x' . $warehouse_new . 'WHERE AccountID = ?', [$request->account]);

        /*
        for ($i = 0; $i < 120; $i++) {
            $item = substr($warehouse, $i * 32, 32);
            $seri = substr($item, 6, 8);
            $seri_dex = hexdec($seri);

            if (($item == 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF') || ($seri_dex > 4294967280) || ($seri_dex == 0)) {
                continue;
            }
            $itemInfo = ItemInfo(ItemDataArr(), $item);
            $itemInfo['item_code'] = $item;
            $itemInfo['position'] = $i;
            array_push($result, $itemInfo);
        }*/

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Đưa đồ lên chợ trời thành công!";
        return response()->json($apiFormat);
    }
}
