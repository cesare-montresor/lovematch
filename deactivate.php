<?php

OW::getNavigation()->deleteMenuItem('lovematch', 'main_menu_item');

//remove 'My birthday' widget when plugin deactivated
BOL_ComponentAdminService::getInstance()->deleteWidget('LOVEMATCH_CMP_UsermatchWidget');

LOVEMATCH_BOL_Question::getInstance()->hideQuestionAstrology();
?>
