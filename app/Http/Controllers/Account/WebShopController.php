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

        if (!$this->dependence->check_duplicate_item_serial($listSeri)) {
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

    public function getItemInSuperMarketList(Request $request)
    {
        $page = $request->page * 20;
        $listItem = [];
        $class_name = '';
        if ($request->searchKey == '') {
            if ($request->selectClass == 0 && $request->selectGroup == '') {
                $listItem = DB::table('BK_Super_Market')
                    ->where('status', 0)
                    ->orderBy('time_up', 'desc')
                    ->skip($page)
                    ->take(20)
                    ->get();
            } elseif ($request->selectClass != 0) {
                switch ($request->selectClass) {
                    case 1:
                        $class_name = 'dw';
                        break;
                    case 2:
                        $class_name = 'dk';
                        break;
                    case 3:
                        $class_name = 'elf';
                        break;
                    case 4:
                        $class_name = 'mg';
                        break;
                    case 5:
                        $class_name = 'dl';
                        break;
                    case 6:
                        $class_name = 'sum';
                        break;
                    case 7:
                        $class_name = 'rf';
                        break;
                }

                if ($request->selectGroup == '') {
                    $listItem = DB::table('BK_Super_Market')
                        ->where($class_name, 1)
                        ->where('status', 0)
                        ->orderBy('time_up', 'desc')
                        ->skip($page)
                        ->take(20)
                        ->get();
                } else {
                    $listItem = DB::table('BK_Super_Market')->where($class_name, 1)->where('item_type', $request->selectGroup)->where('status', 0)->orderBy('time_up', 'desc')->skip($page)->take(20)->get();
                }
            } elseif ($request->selectClass == 0 && $request->selectGroup != '') {
                $listItem = DB::table('BK_Super_Market')->where('item_type', $request->selectGroup)->where('status', 0)->orderBy('time_up', 'desc')->skip($page)->take(20)->get();
            }
        } else {
            if ($request->selectClass == 0 && $request->selectGroup == '') {
                $listItem = DB::table('BK_Super_Market')->where('status', 0)->where('name', 'LIKE', '%' . $request->searchKey . '%')->orderBy('time_up', 'desc')->skip($page)->take(20)->get();
            } elseif ($request->selectClass != 0) {
                switch ($request->selectClass) {
                    case 1:
                        $class_name = 'dw';
                        break;
                    case 2:
                        $class_name = 'dk';
                        break;
                    case 3:
                        $class_name = 'elf';
                        break;
                    case 4:
                        $class_name = 'mg';
                        break;
                    case 5:
                        $class_name = 'dl';
                        break;
                    case 6:
                        $class_name = 'sum';
                        break;
                    case 7:
                        $class_name = 'rf';
                        break;
                }

                if ($request->selectGroup == '') {
                    $listItem = DB::table('BK_Super_Market')->where('name', 'LIKE', '%' . $request->searchKey . '%')->where($class_name, 1)->where('status', 0)->orderBy('time_up', 'desc')->skip($page)->take(20)->get();
                } else {
                    $listItem = DB::table('BK_Super_Market')->where('name', 'LIKE', '%' . $request->searchKey . '%')->where($class_name, 1)->where('item_type', $request->selectGroup)->where('status', 0)->orderBy('time_up', 'desc')->skip($page)->take(20)->get();
                }
            } elseif ($request->selectClass == 0 && $request->selectGroup != '') {
                $listItem = DB::table('BK_Super_Market')->where('name', 'LIKE', '%' . $request->searchKey . '%')->where('item_type', $request->selectGroup)->where('status', 0)->orderBy('time_up', 'desc')->skip($page)->take(20)->get();
            }
        }
        $result = [];
        foreach ($listItem as $item) {
            $itemInfo = $this->itemFunc->ItemInfo($this->itemFunc->ItemDataArr(), $item->item_code);
            $itemInfo['price'] = $item->item_price;
            $itemInfo['item_id'] = $item->id;
            $itemInfo['person_sell'] = $item->name;
            $itemInfo['date_sell'] = date_format(date_create($item->time_up), "H:i:s d/m/Y");
            array_push($result, $itemInfo);
        }

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Tải thông tin thành công!";
        $apiFormat['data'] = $result;
        return response()->json($apiFormat);
    }

    public function buyItemInSuperMarket(Request $request)
    {
        $apiFormat = array();

        if ($this->dependence->check_pass2($request->account, $request->pass2) == 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Mật khẩu cấp 2 không đúng!';
            return response()->json($apiFormat);
        }

        $check_online = $this->dependence->check_online($request->account);
        if ($check_online == 1) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Bạn chưa thoát game!';
            return response()->json($apiFormat);
        }

        $itemInfo = DB::table('BK_Super_Market')->where('id', $request->item_id)->first();

        $itemPrice = (int)($itemInfo->item_price + $itemInfo->item_price / 100);

        if(strcmp($itemInfo->account, $request->account) == 0) {
            $itemPrice = $itemInfo->item_price;
        }

        if ($this->dependence->check_sliver($request->account, $itemPrice) == 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = "Không đủ Bạc. Cần $itemPrice bạc";
            return response()->json($apiFormat);
        }

//        print_r($itemInfo->status);
        if ($itemInfo->status != 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = "Món đồ này đã được người khác mua. Vui lòng tải lại trang!";
            return response()->json($apiFormat);
        }

        $itemData = $this->itemFunc->ItemDataArr();
        $item_code = $this->itemFunc->ItemInfo($itemData, $itemInfo->item_code);

        $warehouse_query = DB::table('warehouse')->select('Items')->where('AccountID', $request->account)->first();

        $warehouse = $warehouse_query->Items;
        $warehouse = strtoupper($warehouse);
        $warehouse1 = substr($warehouse, 0, 120 * 32);
        $warehouse2 = substr($warehouse, 120 * 32);

        $get_slot = $this->itemFunc->CheckSlot($itemData, $warehouse1, $item_code['x'], $item_code['y']);

        if ($get_slot == 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = "Hòm đồ không đủ chỗ. Hãy vào game và sắp xếp lại";
            return response()->json($apiFormat);
        }

//        $item_seri = $this->dependence->getItemSerial();
//        $item = substr_replace($itemInfo->item_code, $item_seri, 6, 8);
        $warehouse1_after = substr_replace($warehouse1, $itemInfo->item_code, ($get_slot - 1) * 32, 32);
        $warehouse_new = $warehouse1_after . $warehouse2;

        DB::update('UPDATE warehouse set Items=0x' . $warehouse_new . 'WHERE AccountID = ?', [$request->account]);
        DB::update('UPDATE MEMB_INFO set bank_sliver = bank_sliver - ? WHERE memb___id = ?', [$itemPrice, $request->account]);
        DB::update('UPDATE MEMB_INFO set bank_sliver = bank_sliver + ? WHERE memb___id = ?', [$itemInfo->item_price, $itemInfo->account]);
        DB::update('UPDATE BK_Super_Market set status = 1, account_buy=? WHERE id = ?', [$request->account, $request->item_id]);

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Mua đồ " . $item_code['name'] . " thành công!";
        return response()->json($apiFormat);
    }

    public function buyItemWebShop(Request $request)
    {
        $apiFormat = array();

        if ($this->dependence->check_pass2($request->account, $request->pass2) == 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Mật khẩu cấp 2 không đúng!';
            return response()->json($apiFormat);
        }

        $check_online = $this->dependence->check_online($request->account);
        if ($check_online == 1) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Bạn chưa thoát game!';
            return response()->json($apiFormat);
        }

        $itemInfo = DB::table('BK_Web_Shops')->where('id', $request->item_id)->first();

        if ($this->dependence->check_sliver($request->account, $itemInfo->item_price) == 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = "Không đủ Bạc. Cần $itemInfo->item_price bạc";
            return response()->json($apiFormat);
        }

        $itemData = $this->itemFunc->ItemDataArr();
        $item_code = $this->itemFunc->ItemInfo($itemData, $itemInfo->item_code);

        $warehouse_query = DB::table('warehouse')->select('Items')->where('AccountID', $request->account)->first();

        $warehouse = $warehouse_query->Items;
        $warehouse = strtoupper($warehouse);
        $warehouse1 = substr($warehouse, 0, 120 * 32);
        $warehouse2 = substr($warehouse, 120 * 32);

        $get_slot = $this->itemFunc->CheckSlot($itemData, $warehouse1, $item_code['x'], $item_code['y']);

        if ($get_slot == 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = "Hòm đồ không đủ chỗ. Hãy vào game và sắp xếp lại";
            return response()->json($apiFormat);
        }

        $item_seri = $this->dependence->getItemSerial();
        $item = substr_replace($itemInfo->item_code, $item_seri, 6, 8);
        $warehouse1_after = substr_replace($warehouse1, $item, ($get_slot - 1) * 32, 32);
        $warehouse_new = $warehouse1_after . $warehouse2;

        DB::update('UPDATE warehouse set Items=0x' . $warehouse_new . 'WHERE AccountID = ?', [$request->account]);
        DB::update('UPDATE MEMB_INFO set bank_sliver=bank_sliver - ? WHERE memb___id = ?', [$itemInfo->item_price, $request->account]);

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Mua đồ thành công!";
        return response()->json($apiFormat);
    }

    public function getItemWebShopList(Request $request)
    {
        $page = $request->page * 20;
        $listItem = [];
        $class_name = '';
        if ($request->selectClass == 0 && $request->selectGroup == '') {
            $listItem = DB::table('BK_Web_Shops')->skip($page)->take(20)->get();
        } elseif ($request->selectClass != 0) {
            switch ($request->selectClass) {
                case 1:
                    $class_name = 'dw';
                    break;
                case 2:
                    $class_name = 'dk';
                    break;
                case 3:
                    $class_name = 'elf';
                    break;
                case 4:
                    $class_name = 'mg';
                    break;
                case 5:
                    $class_name = 'dl';
                    break;
                case 6:
                    $class_name = 'sum';
                    break;
                case 7:
                    $class_name = 'rf';
                    break;
            }

            if ($request->selectGroup == '') {
                $listItem = DB::table('BK_Web_Shops')->where($class_name, 1)->skip($page)->take(20)->get();
            } else {
                $listItem = DB::table('BK_Web_Shops')->where($class_name, 1)->where('item_type', $request->selectGroup)->skip($page)->take(20)->get();
            }
        } elseif ($request->selectClass == 0 && $request->selectGroup != '') {
            $listItem = DB::table('BK_Web_Shops')->where('item_type', $request->selectGroup)->skip($page)->take(20)->get();
        }
        $result = [];
        foreach ($listItem as $item) {
            $itemInfo = $this->itemFunc->ItemInfo($this->itemFunc->ItemDataArr(), $item->item_code);
            $itemInfo['price'] = $item->item_price;
            $itemInfo['item_id'] = $item->id;
            array_push($result, $itemInfo);
        }

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Tải thông tin thành công!";
        $apiFormat['data'] = $result;
        return response()->json($apiFormat);
    }

}
