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
        "VIP" => 2
    ];

}