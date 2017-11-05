<?php

namespace App\EckPrince;

/**
 * Created by PhpStorm.
 * User: hoang
 * Date: 3/28/2017
 * Time: 4:06 PM
 */
use Illuminate\Support\Facades\DB;

class AllFunctions
{
    function writelog($file, $logcontent)
    {
        $Date = date("h:i:sA, d/m/Y");
        $fp = fopen($file, "a+");
        fputs($fp, "Lúc: $Date. $logcontent \n----------------------------------------------------------------------\n");
        fclose($fp);
    }

    function randStrGen($len)
    {
        $result = "";
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $charArray = str_split($chars);
        for ($i = 0; $i < $len; $i++) {
            $randItem = array_rand($charArray);
            $result .= "" . $charArray[$randItem];
        }
        return $result;
    }

    function randomPassword($len)
    {
        $result = "";
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $numbers = "0123456789";
        $charArray = str_split($chars);
        $numberArray = str_split($numbers);
        for ($i = 0; $i < $len; $i++) {
            $randItem = array_rand($charArray);
            $randNumb = array_rand($numberArray);
            $result .= "" . $charArray[$randItem] . "" . $numberArray[$randNumb];
        }
        return $result;
    }

    function get_content_url($data, $link)
    {
        $ch = curl_init($link);
        curl_setopt($ch, CURLOPT_POST, true);                //0 for a get request
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
// execute!
        $response = curl_exec($ch);
// close the connection, release resources used
        curl_close($ch);

        return $response;
    }

    function check_select_char($char_name)
    {
        $check_select_char = DB::table('AccountCharacter')->select('GameIDC')->where('GameIDC', $char_name)->first();
        if (count($check_select_char) > 0) {
            return 1;
        } else return 0;
    }

    function check_online($memb___id)
    {

        $check_online = DB::table('MEMB_STAT')->select('ConnectStat')->where('memb___id', $memb___id)->first();
        if (count($check_online) > 0) {
            return (int)$check_online->ConnectStat;
        } else {
            return 0;
        }
    }

    function check_character_in_account($memb___id, $char)
    {
        $check = DB::table('Character')->select('Name', 'AccountID')->where('AccountID', $memb___id)->where('Name', $char)->first();
        if (count($check) > 0) {
            return 1;
        }
        return 0;
    }

    function check_pass2($memb___id, $pass2)
    {
        $check = DB::table('MEMB_INFO')->select('memb___id', 'pass2')->where('memb___id', $memb___id)->where('pass2', $pass2)->first();
        if (count($check) > 0) {
            return 1;
        }
        return 0;
    }

    function check_sliver($memb___id, $gcoin_need)
    {
        $check = DB::table('MEMB_INFO')->select('memb___id', 'bank_sliver')->where('memb___id', $memb___id)->first();
        if ($check->bank_sliver < $gcoin_need) {
            return 0;
        }
        return 1;
    }

    function check_bank_sliver_and_sliver_lock($memb___id, $gcoin_need)
    {
        $check = DB::table('MEMB_INFO')->select('memb___id', 'bank_sliver', 'bank_sliver_lock')->where('memb___id', $memb___id)->first();
        if (($check->bank_sliver + $check->bank_sliver_lock) < $gcoin_need) {
            return 0;
        }
        return 1;
    }

    function check_bank_zen($memb___id, $zen_need)
    {
        $check = DB::table('MEMB_INFO')->select('memb___id', 'bank_zen')->where('memb___id', $memb___id)->first();
        if ($check->bank_zen < $zen_need) {
            return 0;
        }
        return 1;
    }

    function get_reset_day($account, $name)
    {
        $reset_in_day = DB::table('Log_Resets')
            ->whereDate('reset_time', date('Y-m-d H:i:s', time()))
            ->where('account', $account)
            ->where('name', $name)
            ->get();

        return count($reset_in_day);
    }

    function get_reset_month($account, $name)
    {
        $reset_in_day = DB::table('Log_Resets')
            ->whereMonth('reset_time', date('m', time()))
            ->where('account', $account)
            ->where('name', $name)
            ->get();

        return count($reset_in_day);
    }

    function getItemSerial()
    {
        $db = DB::connection()->getPdo();
        $stmt = $db->prepare("EXEC WZ_GetItemSerial");
        $stmt->execute();
        $seach = $stmt->fetch(\PDO::FETCH_COLUMN);

        $Serial = dechex($seach);

        while (strlen($Serial) < 8) {
            $Serial = '0' . $Serial;
        }

        return $Serial;
    }

    function check_block_char($memb___id, $name)
    {
        $char = DB::table('Character')->select('Name')->where('CtlCode', 1)->Where('AccountID', $memb___id)->where('Name', $name)->first();
        return count($char);
    }

    function check_seri_in_array($seri, $array)
    {
        foreach ($array as $item) {
            if ($item->item_seri === $seri) {
                return 1;
            }
        }
        return 0;
    }

    function check_duplicate_item_serial($listSerial)
    {
        return count($listSerial) === count(array_flip($listSerial));
    }

    /**
     * @param $char_top
     * @param $list_ghrs
     * @param $list_relife
     * @param $char_top_1
     * @return int
     */
    function calculateLimitReset($char_top, $list_ghrs, $list_relife, $char_top_1){
        $get_day = date('w', time());
        $reset_limit_top_1 = $list_ghrs[0]->max_reset_in_day;
        if ($char_top['Top_0h'] == 1) {
            $arr = $list_ghrs[0];
        } else if($char_top['Top_0h'] == 0) {
            $arr = $list_ghrs[count($list_ghrs) - 1];
        } else {
            for($i = count($list_ghrs) - 1; $i >= 0 ; $i--) {
                if ($char_top['Top_0h'] > $list_ghrs[$i]->reset_top) {
                    $arr = $list_ghrs[$i];
                }
            }
        }
        $reset_limit = 0;
        if(isset($arr)) {
            if ($char_top['Top_0h'] == 1) {
                $reset_limit = $arr->max_reset_in_day;
            } else {
                $reset_top_1 = $char_top_1->Resets;

                for ($i = 0; $i < $char_top_1->Relifes; $i++) {
                    $reset_top_1 += $list_relife[$i]->reset;
                }

                $reset_char = $char_top['Resets'];
                for ($i = 0; $i < $char_top['Relifes']; $i++) {
                    $reset_char += $list_relife[$i]->reset;
                }
                $reset_limit = $reset_top_1 - $reset_char;

                if ($reset_limit > $arr->max_reset_in_day) {
                    $reset_limit = $arr->max_reset_in_day;
                }
            }

            if($get_day == 6) {
                $reset_limit_top_1 = $reset_limit_top_1 + floor($reset_limit_top_1 * $arr->percent_saturday / 100);
                $reset_limit = $reset_limit + floor($reset_limit * $arr->percent_saturday / 100);
            } else if($get_day == 0) {
                $reset_limit_top_1 = $reset_limit_top_1 + floor($reset_limit_top_1 * $arr->percent_sunday / 100);
                $reset_limit = $reset_limit + floor($reset_limit * $arr->percent_sunday / 100);
            }

            if ($arr->distance_top_day_reset > 0) {
                $reset_limit = $reset_limit - $reset_limit_top_1 * $arr->distance_top_day_reset;
            }
        }

        return (int)$reset_limit;
    }

    function calResetConfigInfo($char, $list_ghrs) {
        $num_ghrs = count($list_ghrs);
        $reset_info = 0;
        for($i = 0; $i < $num_ghrs; $i++) {
            if($char->Resets > $list_ghrs[$i]->reset) {
                $reset_info =  $list_ghrs[$i];
            }
        }
        return $reset_info;
    }
}