/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  Administrator
 * Created: 2016-3-20
 */

DROP TABLE IF EXISTS `nba_history_players`;
create table nba_history_players(
id int(5) not null primary key auto_increment,
pre varchar(1),
`decade` varchar(10),
teamId int(11),
code varchar(50),
country varchar(50),
displayAffiliation varchar(100),
displayName varchar(50),
displayNameEn varchar(50),
dob int(30),
draftYear int(4),
experience int(3),
firstInitial varchar(30),
firstName varchar(30),
firstNameEn varchar(30),
height varchar(20),
jerseyNo int(4),
lastName varchar(30),
lastNameEn varchar(30),
playerId int(30),
`position` varchar(30),
schoolType varchar(30),
weight varchar(30) 
)ENGINE=InnoDB DEFAULT CHARSET=utf8;
