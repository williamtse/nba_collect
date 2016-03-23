<?php

use Phalcon\Mvc\Controller;
use Ares333\CurlMulti\Core;

class CollectController extends Controller {

    /**
     * 采集球员和球队信息
     */
    public function playersAction() {
        $curl = new Core ();
        $curl->maxThread = 26 + 26 * 8;
        $curl->taskPoolType = 'queue';
        $url = 'http://china.nba.com/static/data/league/playerlist_';
        $url2 = 'http://china.nba.com/static/data/league/historicalplayerlist_';
        $periods = ['1946-1949', '1950-1959', '1960-1969', '1970-1979', '1980-1989', '1990-1999', '2000-2009', '2010-2013'];
        $teams = [];
        $this->db->begin();
        $this->db->query('truncate nba_players');
        $this->db->query('truncate nba_history_players');
        $this->db->query('truncate nba_teams');
        for ($i = 65; $i < 91; $i++) {
            $curl->add(array(
                'url' => $url . chr($i) . '.json',
                'args' => array(
                    'i' => chr($i),
                    'db' => $this->db,
                    'teams' => &$teams
                )
                    ), 'cbPlayers');
            echo date('Y-m-d H:i:s') . ' playerlist_' . chr($i) . " added<br>";
            foreach ($periods as $period) {
                $curl->add(array(
                    'url' => $url2 . chr($i) . '_' . $period . '.json',
                    'args' => array(
                        'i' => chr($i),
                        'decade' => $period,
                        'db' => $this->db,
                        'teams' => &$teams
                    )
                        ), 'cbHistoricalPlayers');
                echo date('Y-m-d H:i:s') . $url2 . chr($i) . '_' . $period . '.json' . " added<br>";
            }
        }
        $curl->start();
        $this->db->commit();
    }

    /**
     * 采集比赛赛程、球队得分
     */
    public function gamesAction() {
        set_time_limit(0);
        $next_month = date('Y-m', strtotime('+1 month'));
        $month = '1996-10';
        if (!$this->request->getQuery('hole', 'string')) {
            $diff = 2;
        } else {
            $diff = $this->getMonthNum($next_month . '-01', $month . '-01');
        }
        $url = 'http://china.nba.com/static/data/season/schedule_';
        $curl = new Core ();
        $curl->maxThread = $diff;
        $curl->taskPoolType = 'queue';
        $this->db->begin();
        $this->db->query('truncate nba_games;');
        $this->db->query('truncate nba_team_scores;');
        $broadCasters = [];
        $games = [];
        $teamscores = [];
        while ($diff > 0) {
            $curl->add(array(
                'url' => $url . str_replace('-', '_', $next_month) . '.json',
                'args' => array(
                    'url' => $url . str_replace('-', '_', $next_month) . '.json',
                    'games' => &$games,
                    'teamscores' => &$teamscores,
                    'broadCasters' => &$broadCasters,
                    'month' => $next_month
                ),
                'opt' => array(
                    CURLOPT_FOLLOWLOCATION => 1
                ),
                    ), 'cbGames');
            obOutput($next_month . " added!");
            $next_month = date('Y-m', strtotime('-1 month', strtotime($next_month)));
            $diff--;
        }
        $curl->start();
        $sql1 = MultiCreateSql('nba_games', $games);
        $this->db->query($sql1);
        $this->db->commit();
    }
    /**
     * 最新的30只球队信息采集
     */
    public function teamsAction(){
        $url = 'http://china.nba.com/static/data/season/conferencestanding.json';
        $curl = new Core ();
        $curl->maxThread = 1;
        $curl->taskPoolType = 'queue';
        $teams = [];
        $standings = [];
        $curl->add(array(
            'url'=>$url,
            'args'=>array(
                'teams'=>&$teams,
                'standings'=>&$standings
            ),
        ),'cbTeams');
        $curl->start();
        $this->db->query('truncate nba_season_teams;');
        //储存球队
        $this->db->query(MultiCreateSql('nba_season_teams', $teams));
        //储存球队当前的得分
        $this->db->query('truncate nba_season_teams_standings;');
        $this->db->query(MultiCreateSql('nba_season_teams_standings', $standings));
    }
    //采集某个赛季球队和球员每场比赛的得分
    public function tongjiAction() {
        set_time_limit(0);
        ini_set("memory_limit","-1");
        $season = $this->request->getQuery('season','string');
        if(!$season) die('缺少赛季');
        $games = $this->db->query('select * from nba_games where utcMillis>='.strtotime(substr($season,0,4).'-10-01').'000;')->fetchAll();
        if (!empty($games)) {
            $url = 'http://china.nba.com/static/data/game/snapshot_';
            $curl = new Core ();
            $curl->maxThread = count($games);
            $curl->taskPoolType = 'queue';
            $teamTjs = [];
            $playerTjs = [];
            foreach ($games as $game) {
                obOutput(date('Y-m-d H:i:s',substr($game['utcMillis'],0,10)));
                $gameId = $game['gameId'];
                $curl->add(
                        array(
                    'url' => $url . $gameId . '.json',
                    'args' => array(
                        'teamTjs' => &$teamTjs,
                        'playerTjs' => &$playerTjs,
                        'url' => $url . $gameId . '.json',
                        'game'=>$game,
                    ),
                    'opt' => array(
                        CURLOPT_FOLLOWLOCATION => 1
                    ),
                        ), 'cbTongji'
                );
                obOutput($url . $gameId . ' added!');
            }
            $curl->start();
            $this->db->query('truncate nba_team_scores;');
            obOutput('truncate nba_team_scores');
            $sql1 = MultiCreateSql('nba_team_scores', $teamTjs);
            obOutput('insert datas to nba_team_scores done!');
            $this->db->query($sql1);
            obOutput('truncate nba_player_scores');
            $this->db->query('truncate nba_player_scores;');
            $sql2 = MultiCreateSql('nba_player_scores', $playerTjs);
            $this->db->query($sql2);
            obOutput('insert datas to nba_player_scores done!');
            
        }
    }

    private function getMonthNum($date1, $date2, $tags = '-') {
        $date1 = explode($tags, $date1);
        $date2 = explode($tags, $date2);
        return abs($date1[0] - $date2[0]) * 12 + $date1[1] - $date2[1];
    }

}
function cbTeams($r,$args){
    if ($r['info']['http_code'] == 200) {
        $content = json_decode($r['content'], true);
        $standingGroups = $content['payload']['standingGroups'];
        foreach ($standingGroups as $group){
            foreach($group['teams'] as $key=>$team){
                $profile = $team['profile'];
                $args['teams'][] = $profile;
                $standing = $team['standings'];
                $standing['teamId'] = $team['profile']['id'];
                $standing['ranking'] = $key+1;//排名
                $args['standings'][] = $standing;
            }
        }
    }
    echo 'success<br>';
    obOutput();
}
function cbTongji($r, $args) {
    if ($r['info']['http_code'] == 200) {
        $content = json_decode($r['content'], true);
        $awayTeam = $content['payload']['awayTeam'];
        $homeTeam = $content['payload']['homeTeam'];
        $game = $args['game'];
        $gameId = $game['gameId'];
        //awayTeam tongji
        $awayTeamId = $awayTeam['profile']['id'];
        $awayTeamScore = $awayTeam['score'];
        $awayTeamScore['teamId'] = $awayTeamId;
        $awayTeamScore['gameId'] = $gameId;
        $awayTeamScore['awayOrHome'] = 2;
        $awayTeamScore['isWinner'] = $game['awayScore']>$game['homeScore']?1:0;
        $args['teamTjs'][] = $awayTeamScore;
        //away team players score
        if(!empty($awayTeam['gamePlayers'])){
            foreach ($awayTeam['gamePlayers'] as $player) {
                $awayPlayerScore = array_merge($player['statTotal'],$player['boxscore']);
                $awayPlayerScore['gameId'] = $gameId;
                $awayPlayerScore['playerId'] = $player['profile']['playerId'];
                $awayPlayerScore['seasonType'] = $game['seasonType'];
                $awayPlayerScore['gameStatus'] = $game['status'];
                $args['playerTjs'][] = $awayPlayerScore;
            }
        }
        
        //homeTeam tongji
        $homeTeamId = $homeTeam['profile']['id'];
        $homeTeamScore = $homeTeam['score'];
        $homeTeamScore['teamId'] = $homeTeamId;
        $homeTeamScore['gameId'] = $gameId;
        $homeTeamScore['homeOrHome'] = 1;
        $homeTeamScore['isWinner'] = $game['homeScore']>$game['awayScore']?1:0;
        $args['teamTjs'][] = $homeTeamScore;
        //away team players score
        if(!empty($homeTeam['gamePlayers'])){
            foreach ($homeTeam['gamePlayers'] as $player) {
                $homePlayerScore = array_merge($player['statTotal'],$player['boxscore']);
                $homePlayerScore['gameId'] = $gameId;
                $homePlayerScore['playerId'] = $player['profile']['playerId'];
                $homePlayerScore['seasonType'] = $game['seasonType'];
                $homePlayerScore['gameStatus'] = $game['status'];
                $args['playerTjs'][] = $homePlayerScore;
            }
        }
    }
    obOutput($args['url'] . ' finished!');
}

function MultiCreateSql($tb, $arr) {
    $keys = '';
    $hasSetKeys = false;
    $inserts = [];
    foreach ($arr as $a) {
        if (!$hasSetKeys) {
            $keys = getKeys($a);
            $hasSetKeys = true;
        }
        $inserts[] = getValues($a);
    }
    $sql = 'insert into ' . $tb . $keys . 'values' . implode(',', $inserts);
    return $sql;
}
function obOutput($str=''){
    echo $str.'<br>';
    echo '<script>window.scrollTo(0,document.body.scrollHeight);</script>';
    ob_flush();
    flush();
}
function cbGames($r, $args) {
    if ($r['info']['http_code'] == 200) {
        $content = json_decode($r['content'], true);
        $dates = $content['payload']['dates'];
        $season = $content['payload']['season'];
        $_games = [];
        $broadCasters = $args['broadCasters'];
        if (!empty($dates)) {
            foreach ($dates as $date) {
                $games = $date['games'];
                $gameCount = $date['gameCount'];
//                mdum($args['url']);
//                mdum($utcMillis);exit();
                foreach ($games as $game) {
                    if (isset($game['profile']) && isset($game['boxscore']) && !isset($args['games']['#' . substr($game['profile']['gameId'], 2)])) {

                        $gameProfile = array_merge($game['profile'], $game['boxscore']);
                        $brcidstr = NULL;
                        if (!empty($game['broadcasters'])) {
                            $brcids = [];
                            foreach ($game['broadcasters'] as $brc) {
                                $brcids[] = $brc['id'];
                                if (!isset($broadCasters[$brc['id']])) {
                                    $broadCasters[$brc['id']] = $brc;
                                }
                            }
                            $brcidstr = implode(',', $brcids);
                        }
                        $gameProfile['broadcastersId'] = $brcidstr;
                        $gameProfile['id'] = null;
                        $gameProfile['season'] = $season['scheduleYearDisplay'];
                        $_games[] = $gameProfile;
                        $args['games']['#' . substr($game['profile']['gameId'], 2)] = $gameProfile;

                        //技术统计
                    }
                }
            }
        }
    }
    obOutput($args['month'], " finished! ; http_code:{$r['info']['http_code']}");
}

function mdum($arr) {
    echo '<pre>';
    var_dump($arr);
    echo '</pre>';
}

/**
 * 现役球员回调
 * @global type $playerModel
 * @param type $r
 * @param type $args
 */
function cbPlayers($r, $args) {
    $content = json_decode($r['content'], true);
    $players = $content['payload']['players'];
    if (!empty($players)) {
        $db = $args['db'];
        $teams = $args['teams'];
        $sql = 'insert into nba_players ';
        $values = [];
        $players_profiles = [];
        $hasGetKeys = FALSE;
        foreach ($players as &$player) {
            $profile = $player['playerProfile'];
            $teamProfile = $player['teamProfile'];
            if (!isset($teams[$teamProfile['code']])) {
                $team[$teamProfile['code']] = $teamProfile;
                insertTeam($db, $teamProfile);
            }
            $profile['pre'] = $args['i'];
            $profile['id'] = null;
            $profile['teamId'] = $teamProfile['id'];
            $players_profiles[] = $profile;
            $values[] = getValues($profile);
            if (!$hasGetKeys) {
                $sql.= getKeys($profile) . ' values';
                $hasGetKeys = TRUE;
            }
        }
        $sql.=implode($values, ',');
        $db->query($sql);
        echo date('Y-m-d H:i:s') . ' historicalplayerlist_' . $args ['i'] . " finished<br>";
    }
}

/**
 * 退役球员回调
 * @param type $r
 * @param type $args
 */
function cbHistoricalPlayers($r, $args) {
    $content = json_decode($r['content'], true);
    $players = $content['payload']['players'];
    if (!empty($players)) {
        $db = $args['db'];
        $teams = $args['teams'];
        $sql = 'insert into nba_history_players ';
        $values = [];
        $players_profiles = [];
        $hasGetKeys = FALSE;
        foreach ($players as &$player) {
            $profile = $player['playerProfile'];
            $teamProfile = $player['teamProfile'];
            if (!isset($teams[$teamProfile['code']])) {
                $teams[$teamProfile['code']] = $teamProfile;
                insertTeam($db, $teamProfile);
            }
            $profile['pre'] = $args['i'];
            $profile['decade'] = $args['decade'];
            $profile['id'] = null;
            $profile['teamId'] = $teamProfile['id'];
            $players_profiles[] = $profile;
            $values[] = getValues($profile);
            if (!$hasGetKeys) {
                $sql.= getKeys($profile) . ' values';
                $hasGetKeys = TRUE;
            }
        }
        $sql.=implode($values, ',');
        $db->query($sql);
        echo date('Y-m-d H:i:s') . ' historicalplayerlist_' . $args ['i'] . " finished<br>";
    }
}

function insertTeam($db, $teamProfile) {
    $res = $db->query('select count(*) as count from nba_teams where id=' . $teamProfile['id'])->fetch();
    if ($res['count'] == 0) {
        $sql = 'insert into nba_teams (id,abbr,city,cityEn,code,conference,displayConference,'
                . 'division,isAllStarTeam,isLeagueTeam,`name`,nameEn)values('
                . $teamProfile['id'] . ',"'
                . $teamProfile['abbr'] . '","'
                . $teamProfile['city'] . '","'
                . $teamProfile['cityEn'] . '","'
                . $teamProfile['code'] . '","'
                . $teamProfile['conference'] . '","'
                . $teamProfile['displayConference'] . '","'
                . $teamProfile['division'] . '","'
                . (!$teamProfile['isAllStarTeam'] ? 0 : 1) . '","'
                . (!$teamProfile['isLeagueTeam'] ? 0 : 1) . '","'
                . $teamProfile['name'] . '","'
                . $teamProfile['nameEn'] . '"'
                . ')';
        $db->query($sql);
    }
}

function getKeys($profile) {
    $keys = array_keys($profile);
    $values = '(`';
    $values.=implode($keys, '`,`');
    $values .= '`)';
    return $values;
}

function getValues($profile) {
    $values = '("';
    $values.=implode($profile, '","');
    $values .= '")';
    return $values;
}
