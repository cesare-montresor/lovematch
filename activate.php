<?php
use BOL_ComponentAdminService;

OW::getNavigation()->addMenuItem(OW_Navigation::MAIN, 'lovematch.index', 'lovematch', 'main_menu_item', OW_Navigation::VISIBLE_FOR_ALL);

$adminComponent = BOL_ComponentAdminService::getInstance();
$widget = $adminComponent->addWidget('LOVEMATCH_CMP_UsermatchWidget', false);

$placeWidget = $adminComponent->addWidgetToPlace($widget,BOL_ComponentAdminService::PLACE_DASHBOARD);
$adminComponent->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$placeWidget = $adminComponent->addWidgetToPlace($widget,BOL_ComponentAdminService::PLACE_PROFILE);
$adminComponent->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);

$placeWidget = $adminComponent->addWidgetToPlace($widget,BOL_ComponentAdminService::PLACE_INDEX);
$adminComponent->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_LEFT);



LOVEMATCH_BOL_Question::getInstance()->showQuestionAstrology();