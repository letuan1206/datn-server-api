<?php

namespace App\Http\Controllers\CardPhone;

use App\CardPhone;
use App\EckPrince\Constains;
use App\EckPrince\SystemConfig;
use App\EckPrince\VipPay_API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;

class CardPhoneController extends Controller
{
    public function chargeCard(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cardType' => 'required',
            'cardSeri' => 'numeric',
            'cardCode' => 'numeric'
        ],
            [
                'cardType.required' => 'Chưa chọn loại thẻ cần nạp',
            ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $message;
            return response()->json($apiFormat);
        }

        switch ($request->cardType) {
            case 'Viettel':
                $telco = 1;
                break;
            case 'MobiPhone':
                $telco = 2;
                break;
            case 'VinaPhone':
                $telco = 3;
                break;
            case 'GATE':
                $telco = 4;
                break;
            case 'VTC':
                $telco = 5;
                break;
            default :
                $telco = 0;
                break;
        };

        $card_phone = new CardPhone();
        $card_phone->account = $request->account;
        $card_phone->card_seri = $request->cardSeri;
        $card_phone->card_code = $request->cardCode;
        $card_phone->card_type = $request->cardType;
        $card_phone->card_value = 0;
        $card_phone->status = Constains::CARD_PENDING;
        $card_phone->created_at = date('Y-m-d H:i:s', time());
        $card_phone->updated_at = date('Y-m-d H:i:s', time());
        $card_phone->save();

        $vip_pay_api = new VipPay_API();
        $vip_pay_api->setMerchantId(SystemConfig::VIP_PAY_MERCHANT_ID);
        $vip_pay_api->setApiUser(SystemConfig::VIP_PAY_API_USER);
        $vip_pay_api->setApiPassword(SystemConfig::VIP_PAY_API_PASSWORD);
        $vip_pay_api->setPin($request->cardCode);
        $vip_pay_api->setSeri($request->cardSeri);
        $vip_pay_api->setCardType(intval($telco));
        $vip_pay_api->setNote($request->account);  // Ghi chu cua ban
        $vip_pay_api->cardCharging();

        $code = intval($vip_pay_api->getCode());
        $info_card = intval($vip_pay_api->getInfoCard());
        $error = $vip_pay_api->getMsg();

        if ($code === 0 && $info_card >= 10000) {
            if ($request->cardType == 'GATE') {
                $sliver_add = SystemConfig::CARD_GATE['' . abs(intval($info_card))];
            } else {
                $sliver_add = SystemConfig::CARD_PHONE['' . abs(intval($info_card))];
            }

            DB::update('update MEMB_INFO set bank_sliver = bank_sliver + ? where memb___id = ?', [$sliver_add, $request->account]);
            $card_phone->status = Constains::CARD_SUCCESS;
            $card_phone->card_value = $sliver_add;
            $card_phone->save();

            $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
            $apiFormat['message'] = "Nạp thẻ thành công. Thẻ mệnh giá $info_card nhận được $sliver_add Bạc";
            return response()->json($apiFormat);
        } else {
            $card_phone->status = Constains::CARD_ERROR;
            $card_phone->save();

            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $error;
            return response()->json($apiFormat);
        }
    }

    public function getCardHistory(Request $request)
    {
        $listCard = CardPhone::where('account', $request->account)->orderBy('created_at', 'desc')->get();
        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Tải thông tin thành công";
        $apiFormat['data'] = $listCard;
        return response()->json($apiFormat);
    }
}
