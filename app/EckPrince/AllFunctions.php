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

    function get_reset_day($name)
    {
        $day = date('d', time());
        $month = date('m', time());
        $year = date('Y', time());

        $list = DB::table('TopReset')->select('name', 'reset')
            ->where('day', $day)
            ->where('month', $month)
            ->where('year', $year)
            ->where('name', $name)->get();

        $reset_day = 0;
        if (count($list) > 0) {
            foreach ($list as $item) {
                $reset_day += $item->reset;
            }
        }

        return $reset_day;
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
}