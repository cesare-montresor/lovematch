<?php

OW::getRouter()->addRoute(new OW_Route('lovematch.test', 'matchlist/test', "LOVEMATCH_CTRL_Matchlist", 'test'));

OW::getRouter()->addRoute(new OW_Route('lovematch.index', 'matchlist', "LOVEMATCH_CTRL_Matchlist", 'index'));
OW::getRouter()->addRoute(new OW_Route('lovematch.detail.user', 'matchlist/detail/:username', "LOVEMATCH_CTRL_Matchlist", 'detail'));
OW::getRouter()->addRoute(new OW_Route('lovematch.horoscope.text', 'matchlist/horoscope/text/:type/:key', "LOVEMATCH_CTRL_Matchlist", 'horoscopeText'));

OW::getRouter()->addRoute(new OW_Route('lovematch.horoscope', 'matchlist/horoscope/:username', "LOVEMATCH_CTRL_Matchlist", 'horoscope'));
OW::getRouter()->addRoute(new OW_Route('lovematch.horoscope.draw.natal', 'matchlist/horoscope/draw/natal/:username', "LOVEMATCH_CTRL_Draw", 'horoscopeNatal'));
OW::getRouter()->addRoute(new OW_Route('lovematch.horoscope.draw.synastry', 'matchlist/horoscope/draw/synastry/:username', "LOVEMATCH_CTRL_Draw", 'horoscopeSynastry'));
OW::getRouter()->addRoute(new OW_Route('lovematch.horoscope.draw.composite', 'matchlist/horoscope/draw/composite/:username', "LOVEMATCH_CTRL_Draw", 'horoscopeComposite'));

OW::getRouter()->addRoute(new OW_Route('lovematch.admin.index', 'admin/plugins/lovematch', "LOVEMATCH_CTRL_Admin", 'index'));
OW::getRouter()->addRoute(new OW_Route('lovematch.admin.questionlist', 'admin/plugins/lovematch/questions', "LOVEMATCH_CTRL_Admin", 'questionlist'));
OW::getRouter()->addRoute(new OW_Route('lovematch.admin.calculateEverything', 'admin/plugins/lovematch/calc', "LOVEMATCH_CTRL_Admin", 'calculateeverything'));




LOVEMATCH_CLASS_EventHandler::getInstance()->init();

?>
