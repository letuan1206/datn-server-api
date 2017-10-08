<?php

namespace App\Http\Controllers\Bank;

use App\Character;
use App\EckPrince\AllFunctions;
use App\EckPrince\Constains;
use App\EckPrince\SystemConfig;
use App\Memb_Info;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;

class BankController extends Controller
{
    //
    private $dependence;

    public function __construct(AllFunctions $functions)
    {
        $this->dependence = $functions;
    }

    public function getBankInfo(Request $request)
    {
        $bank = Memb_Info::select('memb___id', 'bank_sliver', 'bank_sliver_lock', 'bank_zen', 'bank_jewel', 'bank_jewel_lock',
            'wcoin', 'wcoinp', 'GoblinCoin', 'jewel_chao', 'jewel_cre', 'jewel_blue', 'jewel_heart')
            ->where('memb___id', $request->get('account'))
            ->first();

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Tải thông tin thành công!';
        $apiFormat['data'] = $bank;
        return response()->json($apiFormat);
    }

    public function bankTransfer(Request $request)
    {
        $apiFormat = array();

        $validator = Validator::make($request->all(), [
            'to_account' => 'required|max:10',
            'typez' => 'required',
            'quality' => 'required|numeric',
            'purpose' => 'required|max:45'
        ],
            [
                'to_account.required' => 'Chưa điền tên tài khoản chuyển đến',
                'typez.required' => 'Chưa chọn hình thức muốn dùng',
                'quality.required' => 'Chưa điền số lượng cần chuyển',
                'purpose.required' => 'Vui lòng ghi rõ lý do chuyển khoản',
            ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $message;
            return response()->json($apiFormat);
        }

        $checkAcc = Memb_Info::select('memb___id', 'bank_jewel', 'bank_sliver', 'bank_zen')->where('memb___id', $request->to_account)->first();
        if (count($checkAcc) == 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = "Tài khoản chuyển đến $request->to_account không tồn tại";
            return response()->json($apiFormat);
        }

        $quality = (int)($request->quality * (100 + SystemConfig::FEE_TRANSFER) / 100);

        if ($request->quality < 10) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = "Số lượng chuyển khoản tối thiểu phải là 10";
            return response()->json($apiFormat);
        }

        $infoAcc = Memb_Info::select('memb___id', 'bank_jewel', 'bank_sliver', 'bank_zen')->where('memb___id', $request->account)->first();
        switch ($request->typez) {
            case 'jewel':
                if ($infoAcc->bank_jewel < $quality) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = "Tài khoản của bạn không đủ $quality jewel để chuyển";
                    return response()->json($apiFormat);
                }

                DB::update("update memb_info set bank_jewel = bank_jewel - ? where memb___id = ?", [$quality, $request->account]);
                DB::update("update memb_info set bank_jewel = bank_jewel + ? where memb___id = ?", [$request->quality, $request->to_account]);

                DB::insert('insert into Log_BankTransfer (from_account, to_account, quality, description, time, type) values (?, ?, ?, ?, ?, ?)',
                    [$request->account, $request->to_account, $quality, $request->purpose, time(), $request->typez]);

                $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
                $apiFormat['message'] = "Chuyển thành công <strong>$request->quality</strong> jewel cho tài khoản <strong>$request->to_account</strong> thành công";
                return response()->json($apiFormat);
                break;

            case 'sliver':
                if ($infoAcc->bank_sliver < $quality) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = "Tài khoản của bạn không đủ $quality Bạc để chuyển";
                    return response()->json($apiFormat);
                }

                DB::update("update memb_info set bank_sliver = bank_sliver - ? where memb___id = ?", [$quality, $request->account]);
                DB::update("update memb_info set bank_sliver = bank_sliver + ? where memb___id = ?", [$request->quality, $request->to_account]);

                DB::insert('insert into Log_BankTransfer (from_account, to_account, quality, description, time, type) values (?, ?, ?, ?, ?, ?)',
                    [$request->account, $request->to_account, $quality, $request->purpose, time(), $request->typez]);

                $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
                $apiFormat['message'] = "Chuyển thành công <strong>$request->quality</strong> Bạc cho tài khoản <strong>$request->to_account</strong> thành công";
                return response()->json($apiFormat);
                break;
            case 'trZen':
                if ($infoAcc->bank_zen < $request->quality) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = "Tài khoản của bạn không đủ $request->quality triệu Zen để chuyển";
                    return response()->json($apiFormat);
                }

                DB::update("update memb_info set bank_zen = bank_zen - ? where memb___id = ?", [$request->quality, $request->account]);
                DB::update("update memb_info set bank_zen = bank_zen + ? where memb___id = ?", [$request->quality, $request->to_account]);

                DB::insert('insert into Log_BankTransfer (from_account, to_account, quality, description, time, type) values (?, ?, ?, ?, ?, ?)',
                    [$request->account, $request->to_account, $quality, $request->purpose, time(), $request->typez]);

                $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
                $apiFormat['message'] = "Chuyển thành công <strong>$request->quality</strong> Zen cho tài khoản <strong>$request->to_account</strong> thành công";
                return response()->json($apiFormat);
                break;
            default:
                $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                $apiFormat['message'] = "Chưa chọn hình thức muốn chuyển";
                return response()->json($apiFormat);
                break;
        }
    }

    public function changeMoney(Request $request)
    {
        $apiFormat = array();

        $validator = Validator::make($request->all(), [
            'quality' => 'required|numeric',
            'pass2' => 'required|min:6',
            'typez' => 'required'
        ],
            [
                'quality.required' => 'Chưa nhập số cash muốn mua',
                'pass2.required' => 'Chưa điền mật khẩu cấp 2',
                'typez.required' => 'Chưa chọn hình thức muốn chuyển'
            ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $message;
            return response()->json($apiFormat);
        }


        $bank = Memb_Info::select('memb___id', 'bank_sliver', 'wcoin')
            ->where('memb___id', $request->account)->first();

        if (!$this->dependence->check_pass2($request->account, $request->pass2)) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Mật khẩu cấp 2 không đúng!';
            return response()->json($apiFormat);
        }

        switch ($request->typez) {
            case 'bac2cash':
                if ($bank->bank_sliver < $request->quality) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = 'Số Bạc bạn có ít hơn số Cash muốn đổi';
                    return response()->json($apiFormat);
                }

                $sliver_after = $bank->bank_sliver - $request->quality;
                $cash_after = $bank->wcoin + $request->quality;

                DB::update("UPDATE MEMB_INFO set bank_sliver=?, wcoin=? WHERE memb___id = ?", [$sliver_after, $cash_after, $request->account]);

                $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
                $apiFormat['message'] = "Đổi thành công $request->quality Bạc sang $request->quality Cash";
                return response()->json($apiFormat);
                break;

            case 'cash2bac':
                if ($bank->wcoin < $request->quality) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = 'Số Cash bạn có ít hơn số Bạc muốn đổi';
                    return response()->json($apiFormat);
                }

                $sliver_after = $bank->bank_sliver + $request->quality;
                $cash_after = $bank->wcoin - $request->quality;

                DB::update("UPDATE MEMB_INFO set bank_sliver=?, wcoin=? WHERE memb___id = ?", [$sliver_after, $cash_after, $request->account]);

                $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
                $apiFormat['message'] = "Đổi thành công $request->quality Cash sang $request->quality Bạc";
                return response()->json($apiFormat);
                break;

            default:
                $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                $apiFormat['message'] = "Chưa chọn hình thức muốn đổi!";
                return response()->json($apiFormat);
                break;
        }
    }

    public function buyItemSliver(Request $request)
    {
        $apiFormat = array();

        $validator = Validator::make($request->all(), [
            'slg' => 'required|numeric',
            'pass2' => 'required|min:6',
            'item' => 'required',
        ],
            [
                'slg.required' => 'Chưa chọn số item muốn mua',
                'pass2.required' => 'Chưa điền mật khẩu cấp 2',
            ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $message;
            return response()->json($apiFormat);
        }

        if ($this->dependence->check_pass2($request->account, $request->pass2) == 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Mật khẩu cấp 2 không đúng!';
            return response()->json($apiFormat);
        }

        $bank = Memb_Info::select('memb___id', 'bank_sliver')
            ->where('memb___id', $request->account)->first();

        $item = $request->item;

        switch ($item) {
            case "1k":
                $item_code = "0C1000001234560000E0000000000000";
                $item_name = "Item Gold";
                $gia = 1000;
                break;
            case "10k":
                $item_code = "0C0000001234560000E0000000000000";
                $item_name = "Item Zen";
                $gia = 10000;
                break;
            case "50k":
                $item_code = "0F0000001234560000E0000000000000";
                $item_name = "Item Zen 50k";
                $gia = 50000;
                break;

            //Mac dinh
            default:
                $item_code = "00000000000000000000000000000000";
                $item_name = "Giá trị sai";
                $gia = 0;
                break;
        }

        $total_bac = $gia * $request->slg;

        if ($bank->bank_sliver < $total_bac) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = "Bạn không đủ Bạc để mua $request->slg $item_name";
            return response()->json($apiFormat);
        }
        $bac_after = $bank->bank_sliver - $total_bac;

        $item_codes = '';
        $no_item = 'FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF';

        $warehouse = DB::table('warehouse')->SELECT('Items')->WHERE('AccountID', $request->account)->first();

        $inventory_trong = '';
        $otrong = 8;
        for ($i = 0; $i < $otrong; $i++) {
            $inventory_trong .= $no_item;
        }

        $inventory_trong = strtoupper($inventory_trong);
        $inventory_kt = substr($warehouse->Items, 0, $otrong * 32);
        $inventory_kt = strtoupper($inventory_kt);

        if (strcmp($inventory_trong, $inventory_kt) != 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = "Hòm đồ của bạn chưa để trống $otrong ô đầu. Hãy vào game sắp xếp lại";
            return response()->json($apiFormat);
        }

        for ($i = 0; $i < $request->slg; ++$i) {
            $serial = $this->dependence->getItemSerial();
            $item_code = substr_replace($item_code, $serial, 6, 8);
            $item_codes .= $item_code;
        }

        $inventory2_after = substr_replace($warehouse->Items, $item_codes, 0, $request->slg * 32);

        DB::update('UPDATE warehouse set Items=0x' . $inventory2_after . 'WHERE AccountID = ?', [$request->account]);

        DB::update('UPDATE MEMB_INFO set bank_sliver=? WHERE memb___id = ?', [$bac_after, $request->account]);

        $gia_show = number_format($gia, '0', ',', '.');
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Mua thành công $request->slg Item $gia_show Bạc";
        return response()->json($apiFormat);
    }

    public function sellItemSliver(Request $request)
    {

        $apiFormat = array();

        /*$bytes_written = File::prepend('logs/log_sell_item_sliver.eck', 'sangtra|QQQQ|Item Vpoint 1k|000A0123|1000|2017-05-12 12:05:56<eck>');
        if ($bytes_written === false) {
            die("Error writing to file");
        }*/

        /*$content = File::get('logs/log_sell_item_sliver.eck');
        $content = explode('<eck>', $content);
        for($i = 0; $i < count($content) - 1; $i++) {
            print_r($content[$i]);

        }*/

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'pass2' => 'required|min:6',
        ],
            [
                'name.required' => 'Chưa chọn nhân vật muốn đổi',
                'pass2.required' => 'Chưa điền mật khẩu cấp 2',
            ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $message;
            return response()->json($apiFormat);
        }

        if ($this->dependence->check_pass2($request->account, $request->pass2) == 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Mật khẩu cấp 2 không đúng!';
            return response()->json($apiFormat);
        }

        $time_dis = DB::table('MEMB_STAT')->SELECT('DisConnectTM')->WHERE('memb___id', $request->account)->first();

        $time_dis = strtotime($time_dis->DisConnectTM);

        $min_out = 1;

        $time_wait = ($time_dis + $min_out * 60) - time();
        if ($time_wait > 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = 'Bạn cần thoát game và đợi ' . date('i', $time_wait) . ' phút ' . date('s', $time_wait) . ' giây';
            return response()->json($apiFormat);
        }

        $list_log_seri = DB::table('Log_Item_Sliver_Change')->select('item_seri')->get();

        $inventory = DB::table('character')->select('Inventory')->where('AccountID', $request->account)
            ->where('Name', $request->name)->first();
        $inventory1 = substr($inventory->Inventory, 0, 12 * 32);
        $inventory2 = substr($inventory->Inventory, 12 * 32, 64 * 32);
        $inventory3 = substr($inventory->Inventory, 76 * 32);

        $noitem = "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF";

        $sliver_add = 0;
        $inventory2_after = '';

        for ($i = 0; $i < 64; ++$i) {
            $item = substr($inventory2, $i * 32, 32);
            if ($item != $noitem) {
                $code1 = substr($item, 0, 4);
                $code2 = substr($item, 18, 1);
                $seri = substr($item, 6, 8);
                $seri_dex = hexdec($seri);

                if (($seri_dex != 0 && $seri_dex < 4294967280) && $this->dependence->check_seri_in_array($seri, $list_log_seri) == false) {
                    if ($code1 === "0C10" AND $code2 === "E") {
                        $itemtype = "Item Sliver 1k";
                        $itemvalue = 1000;
                        $sliver_add += 1000;
                        $item = "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF";
                        DB::insert('insert into Log_Item_Sliver_Change (account, name, item_type, item_seri, item_value, time) values (?, ?, ?, ?, ?, ?)', [$request->account, $request->name, $itemtype, $seri, $itemvalue, time()]);
                    } elseif ($code1 === "0C00" AND $code2 === "E") {
                        $itemtype = "Item Sliver 10k";
                        $itemvalue = 10000;
                        $sliver_add += 10000;
                        $item = "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF";
                        DB::insert('insert into Log_Item_Sliver_Change (account, name, item_type, item_seri, item_value, time) values (?, ?, ?, ?, ?, ?)', [$request->account, $request->name, $itemtype, $seri, $itemvalue, time()]);
                    } elseif ($code1 === "0F00" AND $code2 === "E") {
                        $itemtype = "Item Sliver 50k";
                        $itemvalue = 50000;
                        $sliver_add += 50000;
                        $item = "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF";
                        DB::insert('insert into Log_Item_Sliver_Change (account, name, item_type, item_seri, item_value, time) values (?, ?, ?, ?, ?, ?)', [$request->account, $request->name, $itemtype, $seri, $itemvalue, time()]);
                    } elseif ($code1 === "7800" AND $code2 === "E") {
                        $itemtype = "Item Sliver 50k";
                        $itemvalue = 50000;
                        $sliver_add += 50000;
                        $item = "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF";
                        DB::insert('insert into Log_Item_Sliver_Change (account, name, item_type, item_seri, item_value, time) values (?, ?, ?, ?, ?, ?)', [$request->account, $request->name, $itemtype, $seri, $itemvalue, time()]);
                    }
                }
            }

            $inventory2_after .= $item;
        }

        $inventory_after = $inventory1 . $inventory2_after . $inventory3;

        DB::update("update Character set inventory = 0x" . $inventory_after . " where name = ?", [$request->name]);
        DB::update("update memb_info set bank_sliver = bank_sliver + ? where memb___id = ?", [$sliver_add, $request->account]);

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Đổi thành công $sliver_add bạc";
        return response()->json($apiFormat);
    }

    public function jewelAction(Request $request)
    {
        $apiFormat = array();

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'action' => 'required',
        ],
            [
                'name.required' => 'Chưa chọn nhân vật',
                'action.required' => 'Chưa chọn hình thức muốn dùng',
            ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $message;
            return response()->json($apiFormat);
        }

        // quality, name
        $quality = abs($request->quality);
        switch ($request->action) {
            case 'guizen':
                $infoChar = Character::select('Money')->where('Name', $request->name)->where('AccountID', $request->account)->first();
                if (($infoChar->Money / 1000000) < $quality) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = "Số Zen bạn muốn gửi nhiều hơn số Zen nhân vật đang có";
                    return response()->json($apiFormat);
                }

                DB::update('update character set Money = Money - ? where AccountID = ? and Name = ?', [$quality * 1000000, $request->account, $request->name]);
                DB::update('update memb_info set bank_zen = bank_zen + ? where memb___id = ?', [$quality, $request->account]);

                $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
                $apiFormat['message'] = "Gửi $quality triệu Zen vào ngân hàng thành công";
                return response()->json($apiFormat);

                break;
            case 'rutzen':

                $infoAcc = Memb_Info::select('bank_zen')->where('memb___id', $request->account)->first();
                if (($infoAcc->bank_zen) < $quality) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = "Số Zen bạn muốn rút nhiều hơn số Zen ngân hàng đang có";
                    return response()->json($apiFormat);
                }

                $infoChar = Character::select('Money')->where('Name', $request->name)->where('AccountID', $request->account)->first();

                if ($infoChar->Money + ($quality * 10000000) > 2000000000) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = "Tổng số Zen không được vượt quá 2.000.000.000";
                    return response()->json($apiFormat);
                }

                DB::update('update memb_info set bank_zen = bank_zen - ? where memb___id = ?', [$quality, $request->account]);
                DB::update('update Character set Money = Money + ? where AccountID = ? and Name = ?', [$quality * 1000000, $request->account, $request->name]);

                $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
                $apiFormat['message'] = "Rút $quality triệu Zen cho nhân vật $request->name thành công";
                return response()->json($apiFormat);

                break;
            case 'guingoc':

                $listLogSeri = DB::table('Log_Item_Sliver_Change')->select('item_seri')->get();

                $inventory = DB::table('character')->select('Inventory')->where('AccountID', $request->account)
                    ->where('Name', $request->name)->first();

                $inventory1 = substr($inventory->Inventory, 0, 12 * 32);
                $inventory2 = substr($inventory->Inventory, 12 * 32, 64 * 32);
                $inventory3 = substr($inventory->Inventory, 76 * 32);

                $noitem = "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF";

                $jewel_add = 0;
                $bless = 0;
                $soul = 0;
                $life = 0;
                $inventory2_after = '';

                for ($i = 0; $i < 64; ++$i) {
                    $item = substr($inventory2, $i * 32, 32);
                    if ($item != $noitem) {
                        $code1 = substr($item, 0, 4);
                        $code2 = substr($item, 18, 1);
                        $seri = substr($item, 6, 8);
                        $seri_dex = hexdec($seri);

                        /* 0D00FF007F90520000E0000000000000 bless
                         0E00FF007F90530000E0000000000000 Soul
                         1000FF007F90540000E0000000000000 life

                         1E00FF007F90690000C0000000000000 bless + 0
                         1E08FF007F90690000C0000000000000 bless + 1
                         1E10FF007F90690000C0000000000000 bless + 2

                         1F00FF007F906A0000C0000000000000 soul + 0
                         8800FF007F906B0000C0000000000000 life + 0*/
                        if (($seri_dex != 0 && $seri_dex < 4294967280) && $this->dependence->check_seri_in_array($seri, $listLogSeri) == false) {
                            if ($code1 === "0D00" AND $code2 === "E") {
                                $itemtype = "Jewel of Bless";
                                $itemvalue = SystemConfig::BLESS_TO_JEWEL;
                                $jewel_add += $itemvalue;
                                $bless++;
                                $item = "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF";
                                DB::insert('insert into Log_Item_Sliver_Change (account, name, item_type, item_seri, item_value, time) values (?, ?, ?, ?, ?, ?)', [$request->account, $request->name, $itemtype, $seri, $itemvalue, time()]);
//                                DB::insert('insert into log_item_jewel_change (acc, name, itemtype, type, seri, value, time) values (?, ?, ?, ?, ?, ?, ?)', [$request->account, $request->name, $itemtype, 0, $seri, $itemvalue, time()]);
                            } elseif ($code1 === "0E00" AND $code2 === "E") {
                                $itemtype = "Jewel of Soul";
                                $itemvalue = SystemConfig::SOUL_TO_JEWEL;
                                $jewel_add += $itemvalue;
                                $soul++;
                                $item = "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF";
                                DB::insert('insert into Log_Item_Sliver_Change (account, name, item_type, item_seri, item_value, time) values (?, ?, ?, ?, ?, ?)', [$request->account, $request->name, $itemtype, $seri, $itemvalue, time()]);
                            } elseif ($code1 === "1000" AND $code2 === "E") {
                                $itemtype = "Jewel of Life";
                                $itemvalue = SystemConfig::LIFE_TO_JEWEL;
                                $jewel_add += $itemvalue;
                                $life++;
                                $item = "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF";
                                DB::insert('insert into Log_Item_Sliver_Change (account, name, item_type, item_seri, item_value, time) values (?, ?, ?, ?, ?, ?)', [$request->account, $request->name, $itemtype, $seri, $itemvalue, time()]);
                            }
                        }
                    }

                    $inventory2_after .= $item;
                }

                $inventory_after = $inventory1 . $inventory2_after . $inventory3;

                DB::update("update Character set inventory = 0x" . $inventory_after . " where name = ?", [$request->name]);
                DB::update("update memb_info set bank_jewel = bank_jewel + ? where memb___id = ?", [$jewel_add, $request->account]);

                $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
                $apiFormat['message'] = "Gửi thành công <strong>$bless</strong> Bless, <strong>$soul</strong> Soul, <strong>$life</strong> Life sang <strong>$jewel_add</strong> Jewel từ nhân vật $request->name thành công";
                return response()->json($apiFormat);

                break;
            case 'rutngoc1':    // Bless
            case 'rutngoc2':    // Soul
            case 'rutngoc3':    // Life
                $item_name = '';
                $jewel_num = 0;

                if ($request->action == 'rutngoc1') {
                    $item_name = 'Bless';
                    $jewel_num = SystemConfig::BLESS_TO_JEWEL;
                } elseif ($request->action == 'rutngoc2') {
                    $item_name = 'Soul';
                    $jewel_num = SystemConfig::SOUL_TO_JEWEL;
                } else if ($request->action == 'rutngoc3') {
                    $item_name = 'Life';
                    $jewel_num = SystemConfig::LIFE_TO_JEWEL;
                }

                $inventory = DB::table('character')->select('Inventory')->where('AccountID', $request->account)
                    ->where('Name', $request->name)->first();

                $inventory1 = substr($inventory->Inventory, 0, 12 * 32);
                $inventory2 = substr($inventory->Inventory, 12 * 32, 64 * 32);
                $inventory3 = substr($inventory->Inventory, 76 * 32);

                $inventory_trong = '';
                $no_item = "FFFFFFFFFFFFFFFFFFFFFFFFFFFFFFFF";
                $cell = 8;
                for ($i = 0; $i < $cell; $i++) {
                    $inventory_trong .= $no_item;
                }

                $inventory_trong = strtoupper($inventory_trong);

                $inventory_kt = substr($inventory2, 0, $cell * 32);
                $inventory_kt = strtoupper($inventory_kt);

                if (strcmp($inventory_trong, $inventory_kt) != 0) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = "Hành trang của bạn chưa để trống $cell ô đầu. Hãy vào game sắp xếp lại";
                    return response()->json($apiFormat);
                }
                if ($quality < 10 || $quality % 10 != 0) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = "Số lượng ngọc muốn rút phải là bội của 10 (10, 20,...)";
                    return response()->json($apiFormat);
                }

                if ($quality > 240) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = "Số lượng ngọc muốn rút tối đa là 240 viên";
                    return response()->json($apiFormat);
                }

                $infoAcc = Memb_Info::select('bank_jewel')->where('memb___id', $request->account)->first();
                if (($infoAcc->bank_jewel) < $quality * $jewel_num) {
                    $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
                    $apiFormat['message'] = "Số <strong>$item_name</strong> bạn muốn rút nhiều hơn số Jewel ngân hàng đang có";
                    return response()->json($apiFormat);
                }

                $b2 = (int)($quality / 30);
                $b1 = (int)(($quality % 30) / 20);
                $b0 = (int)(($quality - $b2 * 30 - $b1 * 20) / 10);

                $count_jewel = $b0 + $b1 + $b2;

                // 1E00FF001234560000C0000000000000 bless + 0
                // 1E08FF007F90690000C0000000000000 bless + 1
                // 1E10FF007F90690000C0000000000000 bless + 2
                $item_codes = '';

                if ($request->action == 'rutngoc1') {
                    for ($i = 0; $i < $b0; $i++) {
                        $item_code = "1E00FF001234560000C0000000000000";
                        $serial = $this->dependence->getItemSerial();
                        $item_code = substr_replace($item_code, $serial, 6, 8);
                        $item_codes .= $item_code;
//                        writelog('logs/log_rut_jewel.txt', "Tài khoản $request->account, nhân vật $request->name rút 1 $item_name + 0 Serial [$serial]");
                    }

                    for ($i = 0; $i < $b1; $i++) {
                        $item_code = "1E08FF007F90690000C0000000000000";
                        $serial = $this->dependence->getItemSerial();
                        $item_code = substr_replace($item_code, $serial, 6, 8);
                        $item_codes .= $item_code;
//                        writelog('logs/log_rut_jewel.txt', "Tài khoản $request->account, nhân vật $request->name rút 1 $item_name + 1 Serial [$serial]");
                    }

                    for ($i = 0; $i < $b2; $i++) {
                        $item_code = "1E10FF007F90690000C0000000000000";
                        $serial = $this->dependence->getItemSerial();
                        $item_code = substr_replace($item_code, $serial, 6, 8);
                        $item_codes .= $item_code;
//                        writelog('logs/log_rut_jewel.txt', "Tài khoản $request->account, nhân vật $request->name rút 1 $item_name + 2 Serial [$serial]");
                    }
                } elseif ($request->action == 'rutngoc2') {
                    for ($i = 0; $i < $b0; $i++) {
                        $item_code = "1F00FF007F906A0000C0000000000000";
                        $serial = $this->dependence->getItemSerial();
                        $item_code = substr_replace($item_code, $serial, 6, 8);
                        $item_codes .= $item_code;
//                        writelog('logs/log_rut_jewel.txt', "Tài khoản $request->account, nhân vật $request->name rút 1 $item_name + 0 Serial [$serial]");
                    }

                    for ($i = 0; $i < $b1; $i++) {
                        $item_code = "1F08FF007F906A0000C0000000000000";
                        $serial = $this->dependence->getItemSerial();
                        $item_code = substr_replace($item_code, $serial, 6, 8);
                        $item_codes .= $item_code;
//                        writelog('logs/log_rut_jewel.txt', "Tài khoản $request->account, nhân vật $request->name rút 1 $item_name + 1 Serial [$serial]");
                    }

                    for ($i = 0; $i < $b2; $i++) {
                        $item_code = "1F10FF007F906A0000C0000000000000";
                        $serial = $this->dependence->getItemSerial();
                        $item_code = substr_replace($item_code, $serial, 6, 8);
                        $item_codes .= $item_code;
//                        writelog('logs/log_rut_jewel.txt', "Tài khoản $request->account, nhân vật $request->name rút 1 $item_name + 2 Serial [$serial]");
                    }
                } else if ($request->action == 'rutngoc3') {
                    for ($i = 0; $i < $b0; $i++) {
                        $item_code = "8800FF007F906B0000C0000000000000";
                        $serial = $this->dependence->getItemSerial();
                        $item_code = substr_replace($item_code, $serial, 6, 8);
                        $item_codes .= $item_code;
//                        writelog('logs/log_rut_jewel.txt', "Tài khoản $request->account, nhân vật $request->name rút 1 $item_name + 0 Serial [$serial]");
                    }

                    for ($i = 0; $i < $b1; $i++) {
                        $item_code = "8808FF007F906B0000C0000000000000";
                        $serial = $this->dependence->getItemSerial();
                        $item_code = substr_replace($item_code, $serial, 6, 8);
                        $item_codes .= $item_code;
//                        writelog('logs/log_rut_jewel.txt', "Tài khoản $request->account, nhân vật $request->name rút 1 $item_name + 1 Serial [$serial]");
                    }

                    for ($i = 0; $i < $b2; $i++) {
                        $item_code = "8810FF007F906B0000C0000000000000";
                        $serial = $this->dependence->getItemSerial();
                        $item_code = substr_replace($item_code, $serial, 6, 8);
                        $item_codes .= $item_code;
//                        writelog('logs/log_rut_jewel.txt', "Tài khoản $request->account, nhân vật $request->name rút 1 $item_name + 2 Serial [$serial]");
                    }
                }

                $inventory2_after = substr_replace($inventory2, $item_codes, 0, $count_jewel * 32);
                $inventory_after = $inventory1 . $inventory2_after . $inventory3;

                DB::update("update Character set inventory = 0x" . $inventory_after . " where name = ?", [$request->name]);
                DB::update("update memb_info set bank_jewel = bank_jewel - ?, bank_sliver = bank_sliver - ? where memb___id = ?", [$quality * $jewel_num, SystemConfig::FEE_JEWEL_ACTION, $request->account]);

                $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
                $apiFormat['message'] = "Rút thành công $quality $item_name về nhân vật $request->name thành công";
                return response()->json($apiFormat);
                break;
        }

        $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
        $apiFormat['message'] = "Chưa chọn hình thức muốn dùng";
        return response()->json($apiFormat);
    }
}
