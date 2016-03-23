drop TABLE IF EXISTS `nba_season_teams`;
create table nba_season_teams(
abbr varchar(30),
city varchar(30),
cityEn varchar(30),
code varchar(30),
conference varchar(30),
displayAbbr varchar(30),
displayConference varchar(30),
division varchar(30),
id int(20) not null primary key,
isAllStarTeam boolean,
isLeagueTeam boolean,
`name` varchar(30),
nameEn varchar(30)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;