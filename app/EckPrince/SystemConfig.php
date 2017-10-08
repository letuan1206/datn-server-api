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

//    CONFIG CHUYỂN KHOẢN
    const FEE_TRANSFER = 5; // x%

//  CONFIG JEWEL ACTION
    const BLESS_TO_JEWEL = 10;
    const SOUL_TO_JEWEL = 6;
    const LIFE_TO_JEWEL = 12;
    const FEE_JEWEL_ACTION = 1000;

// CONFIG CHARACTER CONTROLLER
    const RESET_SKILL_MASTER_SLIVER = 500;
    const RESET_SKILL_MASTER_ZEN    = 10000000;
}