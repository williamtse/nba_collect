/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
/**
 * Author:  Administrator
 * Created: 2016-3-20
 */

drop TABLE IF EXISTS `nba_schedule`;
create table nba_schedule(
id int(255) not null primary key auto_increment,
season varchar(9),#赛季
`type` varchar(10),#比赛类型，常规赛、季前赛、季后赛

)ENGINE=InnoDB DEFAULT CHARSET=utf8;