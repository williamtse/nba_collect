/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  Administrator
 * Created: 2016-3-20
abbr: "ORL"
city: "奥兰多"
cityEn: "Orlando"
code: "magic"
conference: "Eastern"
displayAbbr: "魔术"
displayConference: "东部"
division: "东南分区"
id: "1610612753"
isAllStarTeam: false
isLeagueTeam: true
name: "魔术"
nameEn: "Magic"
 */

drop TABLE IF EXISTS `nba_teams`;
create table nba_teams(
id int(11) not null primary key,
abbr varchar(30),
city varchar(30),
cityEn varchar(30),
code varchar(30),
conference varchar(30),
displayConference varchar(30),
division varchar(30),
isAllStarTeam boolean,
isLeagueTeam boolean,
`name` varchar(30),
nameEn varchar(30)
)ENGINE=InnoDB DEFAULT CHARSET=utf8;