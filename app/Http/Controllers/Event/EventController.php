<?php

namespace App\Http\Controllers\Event;

use App\EckPrince\Constains;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Validator;

class EventController extends Controller
{
    //
    public function getEventList(Request $request)
    {
        $apiFormat = array();

        $month = date_format(date_create(), 'm');

        $from = $request->start_date;
        $to = $request->end_date;

        $event = DB::table('BK_Event_CheckIn')
            ->where('account', $request->account)
            ->whereBetween('time', array($from, $to))
            ->orderBy('time', 'asc')
            ->get();

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = 'Tải thông tin thành công!';
        $apiFormat['data'] = $event;
        return response()->json($apiFormat);
    }

    public function addCheckIn(Request $request)
    {
        $apiFormat = array();

        $validator = Validator::make($request->all(), [
//            'time' => 'required|date',
        ],
            [
//                'time.required' => 'Chưa điền ngày báo danh',
//                'time.date' => 'Dữ liệu ngày không đúng',
            ]);

        if ($validator->fails()) {
            $message = $validator->errors()->first();
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = $message;
            return response()->json($apiFormat);
        }

        $today = date_format(date_create(), 'Y-m-d H:i:s');

        $check = DB::table('BK_Event_CheckIn')->where('account', $request->account)
            ->whereDate('time', $today)->first();

        $yesterday = date_create();
        date_modify($yesterday, "-1 days");

        if (count($check) > 0) {
            $apiFormat['status'] = Constains::RESPONSE_STATUS_ERROR;
            $apiFormat['message'] = "Bạn đã báo danh ngày " . date_format(date_create(), 'Y-m-d') . " rồi!";
            return response()->json($apiFormat);
        }

        $checkYesterday = DB::table('BK_Event_CheckIn')
            ->where('account', $request->account)
            ->whereDate('time', date_format($yesterday, "Y-m-d"))
            ->first();

        if (count($checkYesterday) > 0) {
            $dayCheck = $checkYesterday->day_check + 1;
            if ($dayCheck > 38) {
                $dayCheck = 1;
            }
        } else {
            $dayCheck = 1;
        }

        $sliver_add = 10;
        if ($dayCheck > 7 && $dayCheck <= 14) {
            $sliver_add *= 2;
        } elseif ($dayCheck > 14 && $dayCheck <= 21) {
            $sliver_add *= 3;
        } elseif ($dayCheck > 21 && $dayCheck <= 28) {
            $sliver_add *= 4;
        } elseif ($dayCheck > 28) {
            $sliver_add *= 5;
        }

        $description = 'Điểm danh ' . $dayCheck . ' ngày liên tiếp';

        DB::insert('insert into BK_Event_CheckIn (account, time, day_check, location, description) values (?, ?, ?, ?, ?)', [$request->account, $today, $dayCheck, $request->location, $description]);

        DB::update("update memb_info set bank_sliver = bank_sliver + ? where memb___id = ?", [$sliver_add, $request->account]);

        $result = array();
        $result['title'] = 'Checked';
        $result['time'] = $today;
        $result['description'] = $description;
        $result['location'] = $request->location;
        $result['day_check'] = $dayCheck;

        $apiFormat['status'] = Constains::RESPONSE_STATUS_OK;
        $apiFormat['message'] = "Báo danh ngày $today thành công!";
        $apiFormat['data'] = $result;
        return response()->json($apiFormat);
    }
}
