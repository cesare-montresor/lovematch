<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class LOVEMATCH_CMP_UsermatchList extends OW_Component
{
    function __construct() {
        //parent::__construct();
        
        $showList = TRUE;
        $currentUID = OW_Auth::getInstance()->getUserId();
        $currentUser = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($currentUID);
        
        if (is_null($currentUser) || $currentUser->complete == 'NO')
        {
            $showList = FALSE;
            $userEditUrl = OW::getRouter()->urlForRoute('base_edit');
            $this->assign('userEditUrl',$userEditUrl);
        }
        else
        {
            $matchList = LOVEMATCH_BOL_UsermatchDao::getInstance()->getUsermatchList($currentUID,5);
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
    }
    
    
}