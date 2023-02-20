<?php

BOL_LanguageService::getInstance()->addPrefix('lovematch', 'Love Match');

//import of plugin language pack during installation
OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('lovematch')->getRootDir().'langs.zip', 'lovematch');

//creation of db
$sql = "CREATE TABLE `" . OW_DB_PREFIX . "lovematch_userdata` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `uid` INT(11) NOT NULL,
    `email` VARCHAR(200) NOT NULL,
    `sex` VARCHAR(10),
    
    `year` INT(4),
    `month` INT(2),
    `day` INT(2),
    `hour` INT(2),
    `minute` INT(2),
    `offset_tz` VARCHAR(5),
    
    `city` VARCHAR(200),
    `lat` VARCHAR(20),
    `lng` VARCHAR(20),
    `complete` VARCHAR(3) NOT NULL default 'NO',
    
    `sun` VARCHAR(20),
    `ascendant` VARCHAR(20),
    `moon` VARCHAR(20),
    `mars` VARCHAR(20),
    `venus` VARCHAR(20),
    
    `mercury` VARCHAR(20),
    `jupiter` VARCHAR(20),
    `saturn` VARCHAR(20),
    `uranus` VARCHAR(20),
    `neptune` VARCHAR(20),
    `true_node` VARCHAR(20),
    `mc` VARCHAR(20),
    
    `raw_data` TEXT,
    
    PRIMARY KEY (`id`)
)
ENGINE=MyISAM
ROW_FORMAT=DEFAULT";
 
OW::getDbo()->query($sql);

$sql = "CREATE TABLE `" . OW_DB_PREFIX . "lovematch_usermatch` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `uid1` INT(11) NOT NULL,
    `uid2` INT(11) NOT NULL,
    `score` INT(11) NOT NULL,
    `raw_data` TEXT,
    PRIMARY KEY (`id`)
)
ENGINE=MyISAM
ROW_FORMAT=DEFAULT";
 
OW::getDbo()->query($sql);

OW::getPluginManager()->addPluginSettingsRouteName('lovematch', 'lovematch.admin.index');


require_once OW_DIR_PLUGIN . 'lovematch' . DS . 'bol' . DS . 'question.php';
LOVEMATCH_BOL_Question::getInstance()->addQuestionAstrology();


if ( !OW::getConfig()->configExists('lovematch', 'google_map_api_key') )
{
    OW::getConfig()->addConfig('lovematch', 'google_map_api_key', '', 'Google Map API key');
}

if ( !OW::getConfig()->configExists('lovematch', 'geonames_api_key') )
{
    OW::getConfig()->addConfig('lovematch', 'geonames_api_key', '', 'Geonames API key');
}



?>
