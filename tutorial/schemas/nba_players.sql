/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  Administrator
 * Created: 2016-3-20
code: "quincy_acy"
country: "美国"
displayAffiliation: "Baylor/United States"
displayName: "昆西 埃希 "
displayNameEn: "Quincy Acy"
dob: "655185600000"
draftYear: "2012"
experience: "3"
firstInitial: "昆西"
firstName: "昆西"
firstNameEn: "Quincy"
height: "2米01"
jerseyNo: "13"
lastName: "埃希 "
lastNameEn: "Acy"
playerId: "203112"
position: "前锋"
schoolType: "College"
weight: "108,9 公斤"
 */
DROP TABLE IF EXISTS `nba_players`;
create table nba_players(
id int(5) not null primary key auto_increment,
pre varchar(1),
code varchar(50),
teamId int(11),
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
