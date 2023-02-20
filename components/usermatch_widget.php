<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class LOVEMATCH_CMP_UsermatchWidget extends BASE_CMP_UsersWidget
{
    public function __construct(BASE_CLASS_WidgetParameter $params)
    {
        $data = $this->getData($params);
        $showList = TRUE;
        $currentUID = OW_Auth::getInstance()->getUserId();
        $currentUser = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($currentUID);
        
        $showWidget = $data['entityId'] == $currentUID;
        if ( !$showWidget )
        {
        	$this->setVisible(false);
            return array();
        }
        
        if (is_null($currentUser) || $currentUser->complete == 'NO')
        {
            $showList = FALSE;
            $userEditUrl = OW::getRouter()->urlForRoute('base_edit');
            $this->assign('userEditUrl',$userEditUrl);
        }
        else
        {
            $matchList = LOVEMATCH_BOL_UsermatchDao::getInstance()->getUsermatchList($currentUID,$data['count']);
            $uids = [];
            foreach ($matchList as $match) {
                $uids[]=$match['uid'];
            }
            $infoList = BOL_QuestionService::getInstance()->getQuestionData($uids, ['username', 'realname']);
            
            $avatarList = BOL_AvatarService::getInstance()->getDataForUserAvatars($uids);
            $onlineList = BOL_UserService::getInstance()->findOnlineStatusForUserList($uids);
//            print_r($matchList);
//            print_r($infoList);
//            print_r($avatarList);
            
            $this->assign('matchList',$matchList);
            $this->assign('infoList',$infoList);
            $this->assign('avatarList',$avatarList);
            $this->assign('onlineList',$onlineList);
        }
        $this->assign('showList',$showList);
        $this->assign('maxScore', 380);
        
        
    }
    
    public function getData(BASE_CLASS_WidgetParameter $params) {
        $data = [];
        $data['entityId'] = $params->additionalParamList['entityId'];
        $data['count'] = $params->customParamList['count'];
        return $data;
    }
    
    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => 'number',
            'label' => OW::getLanguage()->text('lovematch', 'matchlist_match_widget_settings_count'),
            'value' => '5'
        );
        
        return $settingList;
    }
    
    public static function getStandardSettingValueList()
    {
        return array(
        	self::SETTING_WRAP_IN_BOX => true,
        	self::SETTING_SHOW_TITLE => true,
        	self::SETTING_ICON => self::ICON_HEART,
        	self::SETTING_TITLE => OW::getLanguage()->text('lovematch', 'best_match_widget_title')
        );
    }
}