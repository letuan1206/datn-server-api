<?php
/**
 * Created by PhpStorm.
 * User: TuanLe
 * Date: 9/27/2017
 * Time: 9:41 PM
 */

namespace App\EckPrince;


class SystemConfig
{
//  CONFIG SMS
    const SMS_SYNC_TAX = 'DV MUML';
    const SMS_HEAD_PHONE = '8085';
    const SMS_TIME_REMAINING = 5;

// CONFIG NẠP THẺ VIP_PAY
    const VIP_PAY_MERCHANT_ID = 6060; //API_ID lay trong tich hop website sau khi dang nhạp vippay.vn
    const VIP_PAY_API_USER = "bc3dde06e12d4af0be7f453b38b4e66a"; //API_USERNAME lay trong tich hop website sau khi dang nhạp vippay.vn
    const VIP_PAY_API_PASSWORD = "5fbd683801584e00831ca6a89d513373"; //API_PASSWORD lay trong tich hop website sau khi dang nhạp vippay.vn

// CONFIG CHUYỂN KHOẢN
    const FEE_TRANSFER = 5; // x%

//  CONFIG JEWEL ACTION
    const BLESS_TO_JEWEL = 10;
    const SOUL_TO_JEWEL = 6;
    const LIFE_TO_JEWEL = 12;
    const FEE_JEWEL_ACTION = 10;

// CONFIG CHARACTER CONTROLLER
// ----- RESET SKILL MASTER
    const RESET_SKILL_MASTER_SLIVER = 5;
    const RESET_SKILL_MASTER_ZEN = 10000000;
// ----- CLEAR PK
    const CLEAR_PK_ZEN = 10000000;
    const CLEAR_PK_SLIVER = 5;
    const CLEAR_PK_COUNT = 100;
// ----- CONFIG RESET
    const LEVEL_AFTER_RESET = 6;
    const POINT_BONUS_RESET_VIP = 8;
    const POINT_BONUS_RESET_VIP_PO = 2;
// CONFIG CHANGE CLASS
    const CHANGE_CLASS_SLIVER = 500;

    const CARD_PHONE = [
        "10000" => 100,
        "20000" => 200,
        "30000" => 300,
        "50000" => 520,
        "100000" => 1040,
        "200000" => 2080,
        "300000" => 3120,
        "500000" => 5200,
        "1000000" => 10400
    ];

    const CARD_GATE = [
        "10000" => 100,
        "20000" => 200,
        "30000" => 300,
        "50000" => 550,
        "100000" => 1100,
        "200000" => 2200,
        "300000" => 3300,
        "500000" => 5500,
        "1000000" => 11000
    ];
}