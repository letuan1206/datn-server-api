<?php
namespace App\EckPrince;
/**
 * Created by PhpStorm.
 * User: hoang
 * Date: 3/28/2017
 * Time: 4:06 PM
 */
class Constains
{
    const RESPONSE_STATUS_OK = 1;
    const RESPONSE_STATUS_ERROR = 0;
    const RESPONSE_STATUS_LOGOUT = 2;

    const SMS_TYPE = [
        "PASS1" => 1,
        "PASS2" => 2,
        "SNO_NUMBER" => 3,
        "PHONE_NUMBER" => 4,
        "EMAIL" => 5,
        "FORGOT_PASS" => 6
    ];

    const SMS_STATUS_PENDING = 0;
    const SMS_STATUS_ACTIVE = 1;

    const RESET_TYPE = [
        "NORMAL" => 1,
        "VIP" => 2,
        "VIP_PO" => 3
    ];

    const LOG_TYPE = [
        "ALL" => 1,
        "SMS" => 2,
        "RESET" => 3,
        "CARD" => 4,
        "BANK_TRANFER" => 5,
        "ITEM_SLIVER_CHANGE" => 6,
        "LOGIN" => 7
    ];

    const CARD_PENDING = 0;
    const CARD_SUCCESS = 1;
    const CARD_ERROR = 2;
}