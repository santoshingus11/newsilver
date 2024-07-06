<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\BetRecord;
use Illuminate\Http\Request;
use App\Models\CricketMatch;
use App\Models\CricketPlaceBet;
use App\Models\FootballPlaceBet;
use App\Models\GreyhoundRacingPlaceBet;
use App\Models\HorseRacingPlaceBet;
use App\Models\TennisPlaceBet;

class CommandController extends Controller
{
    public function index(Request $request)
    {
        $requestData = $request->json()->all();
        // dd($requestData['cmd']);
        $validatedData = $this->validate($request, [
            'hall' => 'required',
            'key' => 'required',
            'login' => 'required',
        ]);
        if ($requestData['cmd'] == "getBalance") {
            $admin = Admin::where('username', $validatedData['login'])->first();
            if ($admin) {
                $response = [
                    'status' => 'success',
                    'error' => '',
                    'login' => $admin->username,
                    'balance' => $admin->balance,
                    'currency' => 'INR'
                ];
                return response()->json($response);
            } else {
                return response()->json($this->errorfunction('user_not_found'));
            }
        } elseif ($requestData['cmd'] == "writeBet") {


            $admin = Admin::where('username', $validatedData['login'])->first();
            if ($admin) {
                $response = $this->writeBet($requestData, $admin);
                return response()->json($response);
            } else {
                return response()->json($this->errorfunction('user_not_found'));
            }
        } else {
            return response()->json($this->errorfunction('request_not_found!'));
        }
    }


    public function writeBet($request, $admin)
    {
        if ($request['betInfo'] != "refund") {
            if ($request['bet'] > $admin->balance) {
                return response()->json($this->errorfunction('fail_balance'));
            }
            $balance = $admin->balance - $request['bet'];
            $admin->update([

                'balance' => $balance + $request['win']
            ]);

            $record = BetRecord::create(
                [
                    'admin_id' => $admin->id,
                    'hall' => $request['hall'],
                    'key' => $request['key'],
                    'sessionId' => (isset($request['sessionId']) ? $request['sessionId'] : ''),
                    'bet' => $request['bet'],
                    'win' => $request['win'],
                    'tradeId' => (isset($request['tradeId'])) ? $request['tradeId'] : '',
                    'action' => (isset($request['action'])) ? $request['action'] : '',
                    'betInfo' => (isset($request['betInfo'])) ? $request['betInfo'] : '',
                    'gameId' => $request['gameId'],
                    'matrix' => (isset($request['matrix'])) ? $request['matrix'] : '',
                    'date' => (isset($request['date'])) ? $request['date'] : '',
                    'winLines' => (isset($request['WinLines'])) ? $request['WinLines'] : ''
                ]
            );

            return [
                'status' => 'success',
                'error' => '',
                'login' => $admin->username,
                'balance' => $admin->balance,
                'currency' => 'INR',
                'operationId' => $record->id
            ];
        } else {
            return  [
                'status' => 'fail',
                'error' => 'fail_balance'

            ];
        }
    }


    public function profit_loss($id, $game_id, $win_loss)
    {

        try {


            if ($game_id == 'cricket') {
                $winnerSelection = CricketPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $win_loss)
                    ->join('admins', 'cricket_place_bet.user_id', '=', 'admins.id')
                    ->get();

                if (!empty($winnerSelection)) {
                    foreach ($winnerSelection as $winner) {
                        $total_balance = $winner->balance + $winner->bet_profit;

                        $admin = Admin::findOrFail($winner->user_id);
                        $admin->balance = $total_balance;
                        $admin->save();
                    }
                }

                $placeBetResults = CricketPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $win_loss)->get();

                if (!empty($placeBetResults)) {
                    foreach ($placeBetResults as $placeBetResult) {
                        $placeBetResult->bet_result = 1; //1=win, 2=Lose
                        $placeBetResult->save();
                    }
                }

                if ($win_loss == 'back') {
                    $winLoss = 'lay';
                }
                if ($win_loss == 'lay') {
                    $winLoss = 'back';
                }

                $LoseResults = CricketPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $winLoss)->get();

                if (!empty($LoseResults)) {
                    foreach ($LoseResults as $LoseResult) {
                        $LoseResult->bet_result = 2; // 2=Lose
                        $LoseResult->save();
                    }
                }
            }

            if ($game_id == 'football') {
                echo '<pre>';

                $winnerSelection = FootballPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $win_loss)
                    ->join('admins', 'football_place_bet.user_id', '=', 'admins.id')
                    ->get();

                if (!empty($winnerSelection)) {
                    foreach ($winnerSelection as $winner) {
                        $total_balance = $winner->balance + $winner->bet_profit;

                        $admin = Admin::findOrFail($winner->user_id);
                        $admin->balance = $total_balance;
                        $admin->save();
                    }
                }

                $placeBetResults = FootballPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $win_loss)->get();

                if (!empty($placeBetResults)) {
                    foreach ($placeBetResults as $placeBetResult) {
                        $placeBetResult->bet_result = 1; //1=win, 2=Lose
                        $placeBetResult->save();
                    }
                }

                if ($win_loss == 'back') {
                    $winLoss = 'lay';
                }
                if ($win_loss == 'lay') {
                    $winLoss = 'back';
                }

                $LoseResults = FootballPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $winLoss)->get();

                if (!empty($LoseResults)) {
                    foreach ($LoseResults as $LoseResult) {
                        $LoseResult->bet_result = 2; // 2=Lose
                        $LoseResult->save();
                    }
                }
            }

            if ($game_id == 'tennis') {
                $winnerSelection = TennisPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $win_loss)
                    ->join('admins', 'tennis_place_bet.user_id', '=', 'admins.id')
                    ->get();

                if (!empty($winnerSelection)) {
                    foreach ($winnerSelection as $winner) {
                        $total_balance = $winner->balance + $winner->bet_profit;

                        $admin = Admin::findOrFail($winner->user_id);
                        $admin->balance = $total_balance;
                        $admin->save();
                    }
                }

                $placeBetResults = TennisPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $win_loss)->get();

                if (!empty($placeBetResults)) {
                    foreach ($placeBetResults as $placeBetResult) {
                        $placeBetResult->bet_result = 1; //1=win, 2=Lose
                        $placeBetResult->save();
                    }
                }

                if ($win_loss == 'back') {
                    $winLoss = 'lay';
                }
                if ($win_loss == 'lay') {
                    $winLoss = 'back';
                }

                $LoseResults = TennisPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $winLoss)->get();

                if (!empty($LoseResults)) {
                    foreach ($LoseResults as $LoseResult) {
                        $LoseResult->bet_result = 2; // 2=Lose
                        $LoseResult->save();
                    }
                }
            }


            if ($game_id == 'horse') {
                $winnerSelection = HorseRacingPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $win_loss)
                    ->join('admins', 'horse_racing_place_bet.user_id', '=', 'admins.id')
                    ->get();

                if (!empty($winnerSelection)) {
                    foreach ($winnerSelection as $winner) {
                        $total_balance = $winner->balance + $winner->bet_profit;

                        $admin = Admin::findOrFail($winner->user_id);
                        $admin->balance = $total_balance;
                        $admin->save();
                    }
                }

                $placeBetResults = HorseRacingPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $win_loss)->get();

                if (!empty($placeBetResults)) {
                    foreach ($placeBetResults as $placeBetResult) {
                        $placeBetResult->bet_result = 1; //1=win, 2=Lose
                        $placeBetResult->save();
                    }
                }

                if ($win_loss == 'back') {
                    $winLoss = 'lay';
                }
                if ($win_loss == 'lay') {
                    $winLoss = 'back';
                }

                $LoseResults = HorseRacingPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $winLoss)->get();

                if (!empty($LoseResults)) {
                    foreach ($LoseResults as $LoseResult) {
                        $LoseResult->bet_result = 2; // 2=Lose
                        $LoseResult->save();
                    }
                }
            }

            if ($game_id == 'greyhound') {
                $winnerSelection = GreyhoundRacingPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $win_loss)
                    ->join('admins', 'greyhound_racing_place_bet.user_id', '=', 'admins.id')
                    ->get();

                if (!empty($winnerSelection)) {
                    foreach ($winnerSelection as $winner) {
                        $total_balance = $winner->balance + $winner->bet_profit;

                        $admin = Admin::findOrFail($winner->user_id);
                        $admin->balance = $total_balance;
                        $admin->save();
                    }
                }

                $placeBetResults = GreyhoundRacingPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $win_loss)->get();

                if (!empty($placeBetResults)) {
                    foreach ($placeBetResults as $placeBetResult) {
                        $placeBetResult->bet_result = 1; //1=win, 2=Lose
                        $placeBetResult->save();
                    }
                }

                if ($win_loss == 'back') {
                    $winLoss = 'lay';
                }
                if ($win_loss == 'lay') {
                    $winLoss = 'back';
                }

                $LoseResults = GreyhoundRacingPlaceBet::where('match_id', $id)->where('bet_result', null)->where('back_lay', $winLoss)->get();

                if (!empty($LoseResults)) {
                    foreach ($LoseResults as $LoseResult) {
                        $LoseResult->bet_result = 2; // 2=Lose
                        $LoseResult->save();
                    }
                }
            }

            return "done";
        } catch (Exception $e) {
            return $e->getMessage();
        }
    }


    public function  errorfunction($error)
    {
        return  [
            'status' => 'fail',
            'error' => $error

        ];
    }

    public function all_games_bet($game, $match_id)
    {
        if ($game) {
            if ($game == "cricket") {
                $response = CricketPlaceBet::where('match_id', $match_id)->orderBy('id', 'desc')->get();
            }
            if ($game == "football") {
                $response = FootballPlaceBet::where('match_id', $match_id)->orderBy('id', 'desc')->get();
            }
            if ($game == "tennis") {
                $response = TennisPlaceBet::where('match_id', $match_id)->orderBy('id', 'desc')->get();
            }
            if ($game == "horse") {
                $response = HorseRacingPlaceBet::where('match_id', $match_id)->orderBy('id', 'desc')->get();
            }
            if ($game == "greyhound") {
                $response = GreyhoundRacingPlaceBet::where('match_id', $match_id)->orderBy('id', 'desc')->get();
            }
            return [
                'status' => 'success',
                'response' => $response,

            ];
        } else {
            return  [
                'status' => 'fail',
                'error' => 'fail_balance'

            ];
        }
    }

    public function all_games_bet_count_and_data()
    {
        $CricketPlaceBet = CricketPlaceBet::where('bet_result', null)->orderBy('id', 'desc')->get();
        $CricketPlaceBetcount = CricketPlaceBet::where('bet_result', null)->count();

        $FootballPlaceBet = FootballPlaceBet::where('bet_result', null)->orderBy('id', 'desc')->get();
        $FootballPlaceBetcount = FootballPlaceBet::where('bet_result', null)->count();

        $TennisPlaceBet = TennisPlaceBet::where('bet_result', null)->orderBy('id', 'desc')->get();
        $TennisPlaceBetcount = TennisPlaceBet::where('bet_result', null)->count();

        $HorseRacingPlaceBet = HorseRacingPlaceBet::where('bet_result', null)->orderBy('id', 'desc')->get();
        $HorseRacingPlaceBetcount = HorseRacingPlaceBet::where('bet_result', null)->count();

        $GreyhoundRacingPlaceBet = GreyhoundRacingPlaceBet::where('bet_result', null)->orderBy('id', 'desc')->get();
        $GreyhoundRacingPlaceBetcount = GreyhoundRacingPlaceBet::where('bet_result', null)->count();

        $data = [
            'CricketPlaceBet' =>   $CricketPlaceBet,
            'CricketPlaceBetcount' =>   $CricketPlaceBetcount,
            'FootballPlaceBet' =>   $FootballPlaceBet,
            'FootballPlaceBetcount' =>   $FootballPlaceBetcount,
            'TennisPlaceBet' =>   $TennisPlaceBet,
            'TennisPlaceBetcount' =>   $TennisPlaceBetcount,
            'HorseRacingPlaceBet' =>   $HorseRacingPlaceBet,
            'HorseRacingPlaceBetcount' =>   $HorseRacingPlaceBetcount,
            'GreyhoundRacingPlaceBet' =>   $GreyhoundRacingPlaceBet,
            'GreyhoundRacingPlaceBetcount' =>   $GreyhoundRacingPlaceBetcount,
        ];
        return response()->json([
            'status' => 'success',
            'response' => $data,
        ]);
    }
    public function all_games_bet_count()
    {
        $CricketPlaceBetcount = CricketPlaceBet::where('bet_result', null)->count();
        $FootballPlaceBetcount = FootballPlaceBet::where('bet_result', null)->count();
        $TennisPlaceBetcount = TennisPlaceBet::where('bet_result', null)->count();
        $HorseRacingPlaceBetcount = HorseRacingPlaceBet::where('bet_result', null)->count();
        $GreyhoundRacingPlaceBetcount = GreyhoundRacingPlaceBet::where('bet_result', null)->count();
        $total = $CricketPlaceBetcount + $FootballPlaceBetcount + $TennisPlaceBetcount + $HorseRacingPlaceBetcount + $GreyhoundRacingPlaceBetcount;
        $data = [
            'total' =>   $total,
            'CricketPlaceBetcount' =>   $CricketPlaceBetcount,
            'FootballPlaceBetcount' =>   $FootballPlaceBetcount,
            'TennisPlaceBetcount' =>   $TennisPlaceBetcount,
            'HorseRacingPlaceBetcount' =>   $HorseRacingPlaceBetcount,
            'GreyhoundRacingPlaceBetcount' =>   $GreyhoundRacingPlaceBetcount,
        ];

        return [
            'status' => 'success',
            'response' => $data,

        ];
    }
    public function all_games_bet_reject($id, $game)
    {
        if ($game == "Cricket") {
            $CricketPlaceBet = CricketPlaceBet::where('id', $id)->first();
            $user = Admin::where('id', $CricketPlaceBet->user_id)->first();
            $total = $user->balance + $CricketPlaceBet->bet_stake;
            Admin::where('id', $CricketPlaceBet->user_id)->update(['balance' => $total]);
            CricketPlaceBet::where('id', $id)->update(['bet_result' => '3']);
        }
        if ($game == "FootBall") {
            $CricketPlaceBet = FootballPlaceBet::where('id', $id)->first();
            $user = Admin::where('id', $CricketPlaceBet->user_id)->first();
            $total = $user->balance + $CricketPlaceBet->bet_stake;
            Admin::where('id', $CricketPlaceBet->user_id)->update(['balance' => $total]);
            FootballPlaceBet::where('id', $id)->update(['bet_result' => '3']);
        }
        if ($game == "Tennis") {
            $CricketPlaceBet = TennisPlaceBet::where('id', $id)->first();
            $user = Admin::where('id', $CricketPlaceBet->user_id)->first();
            $total = $user->balance + $CricketPlaceBet->bet_stake;
            Admin::where('id', $CricketPlaceBet->user_id)->update(['balance' => $total]);
            TennisPlaceBet::where('id', $id)->update(['bet_result' => '3']);
        }
        if ($game == "HorseRacing") {
            $CricketPlaceBet = HorseRacingPlaceBet::where('id', $id)->first();
            $user = Admin::where('id', $CricketPlaceBet->user_id)->first();
            $total = $user->balance + $CricketPlaceBet->bet_stake;
            Admin::where('id', $CricketPlaceBet->user_id)->update(['balance' => $total]);
            HorseRacingPlaceBet::where('id', $id)->update(['bet_result' => '3']);
        }
        if ($game == "GreyhoundRacing") {
            $CricketPlaceBet = GreyhoundRacingPlaceBet::where('id', $id)->first();
            $user = Admin::where('id', $CricketPlaceBet->user_id)->first();
            $total = $user->balance + $CricketPlaceBet->bet_stake;
            Admin::where('id', $CricketPlaceBet->user_id)->update(['balance' => $total]);
            GreyhoundRacingPlaceBet::where('id', $id)->update(['bet_result' => '3']);
        }
        return [
            'status' => 'success',
        ];
    }
}
