<?php

namespace App\Http\Middleware;

use App\Character;
use Closure;
use Illuminate\Support\Facades\DB;

class CheckAction
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $check_action = DB::table('BK_Check_Action')->where('action_name', 'Top0H')->first();
        $today = date('Y-m-d', time());
        if (empty($check_action)) {
            DB::insert("insert into BK_Check_Action (action_name, action_time, status) values (?, ?, ?)",
                ['Top0H', $today, 1]);
            $this->updateTop0H();
        } else {
            if ($check_action->action_time != $today) {
                DB::update("Update BK_Check_Action set action_time = ?, status = ? Where action_name = ?", [$today, 1, 'Top0H']);
                $this->updateTop0H();
            }
        }
        return $next($request);
    }

    public function updateTop0H() {
        $top_0h = Character::select('AccountID'
                                    ,'Name'
                                    ,'cLevel'
                                    ,'Resets'
                                    ,'Relifes'
                                    ,'Reset_Time'
                                    ,'LevelUp_Time')
            ->orderBy('Relifes', 'desc')
            ->orderBy('Resets', 'desc')
            ->orderBy('cLevel', 'desc')
            ->orderBy('LevelUp_Time', 'desc')
            ->take(500)
            ->get();
        $top = count($top_0h);
        for($i = 0; $i < $top; $i++) {
            DB::update("Update Character set top_0h = ?, reset_0h=?, relife_0h=?, level_0h=?, reset_time=?, level_time_0h=? Where AccountID = ? and Name = ?",
                [$i+1, $top_0h[$i]['Resets'], $top_0h[$i]['Relifes'], $top_0h[$i]['cLevel'], $top_0h[$i]['Reset_Time'], $top_0h[$i]['LevelUp_Time'], $top_0h[$i]['AccountID'], $top_0h[$i]['Name']]);
        }
    }
}
