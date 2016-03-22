/* 
 */
#比赛
drop table if exists nba_games;
create table nba_games(
id int(255) not null primary key auto_increment,
gameId varchar(20) not null ,
broadcastersId int(20) comment '媒体id',
arenaLocation varchar(50) comment '比赛地点',
arenaName varchar(30) comment '比赛场馆',
awayTeamId int(20),
homeTeamId int(20),
`number` int(20),
scheduleCode varchar(50),
seasonType tinyint(1),
`sequence` int(2),
utcMillis varchar(13) comment '比赛时间，js时间戳',
attendance varchar(20),
awayScore int(3),
gameLength varchar(30),
homeScore int(3),
officialsDisplayName1 varchar(50),
officialsDisplayName2 varchar(50),
officialsDisplayName3 varchar(50),
`period` tinyint(2),
periodClock varchar(30),
status tinyint(2),
statusDesc varchar(40),
ties tinyint(2)
)engine=InnoDb default charset=utf8;

#球队比赛得分情况
drop table if exists nba_team_scores;
create table nba_team_scores(
gameId int(30) not null,
teamId int(30),
awayOrHome tinyint(1) comment '主客场 1:主场 2：客场',
assists int(3) comment '助攻',
biggestLead int(3),
blocks int(3) comment '盖帽',
blocksAgainst int(3),
defRebs int(3) comment '防守',
disqualifications int(3),
ejections int(3),
fastBreakPoints int(3),
fga int(3) comment '出手',
fgm int(3) comment '命中',
fgpct decimal(5,1) comment '%',
fouls int(3) comment '犯规',
fta int(3) comment '罚球出手',
ftm int(3) comment '罚球命中',
ftpct decimal(5,1) comment '罚球',
flagrantFouls int(3),
fullTimeoutsRemaining int(3),
mins int(3),
offRebs int(3),
ot1Score int(3),
ot2Score int(3),
ot3Score int(3),
ot4Score int(3),
ot5Score int(3),
ot6Score int(3),
ot7Score int(3),
ot8Score int(3),
ot9Score int(3),
ot10Score int(3),
pointsInPaint int(3),
pointsOffTurnovers int(3),
q1Score int(3),
q2Score int(3),
q3Score int(3),
q4Score int(3),
rebs int(3),
score int(3),
seconds int(3),
shortTimeoutsRemaining int(3),
steals int(3) comment '抢断',
technicalFouls int(3) comment '技术犯规',
tpa int(3) comment '三分出手',
tpm int(3) comment '三分命中',
tppct decimal(5,1) comment '三分百分比',
turnovers int(3) comment '失误'
)engine=InnoDb default charset=utf8;

#比赛中球员的得分情况
drop table if exists nba_player_scores;
create table nba_player_scores(
id int(255) not null primary key auto_increment,
gameId int(30) not null,
assists int(3) comment '助攻',
blocks int(3) comment '盖帽',
defRebs int(3) comment '防守',
fga int(3) comment '出手',
fgm int(3) comment '命中',
fgpct decimal(5,1) comment '%',
fouls int(3) comment '犯规',
fta int(3) comment '罚球出手',
ftm int(3) comment '罚球命中',
ftpct decimal(5,1) comment '罚球' ,
mins int(3) comment '分钟',
offRebs int(3) comment '进攻',
points int(3) comment '得分',
rebs int(3) comment '篮板',
secs int(3) comment '分钟秒',
steals int(3) comment '抢断',
tpa int(3) comment '三分出手',
tpm int(3) comment '三分命中',
tppct decimal(5,1) comment '三分百分比',
turnovers int(3) comment '失误'
)engine=InnoDb default charset=utf8;

drop table if exists nba_broadcasters;
create table nba_broadcasters(
id int(30) primary key auto_increment,
media varchar(30) comment '媒体',
`name` varchar(50) comment '媒体名称',
`range` varchar(100) comment '',
`type` varchar(100),
url varchar(255)
)engine=InnoDb default charset=utf8 comment '比赛直播媒体';

