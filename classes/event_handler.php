<?php

class LOVEMATCH_CLASS_EventHandler
{
    /**
     * Class instance
     *
     * @var LOVEMATCH_CLASS_EventHandler
     */
    private static $classInstance;

    
    /**
     * Returns class instance
     *
     * @return LOVEMATCH_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    
    public function addProfileToolbarAction( BASE_CLASS_EventCollector $event )
    {
        
        if ( !OW::getUser()->isAuthenticated() )
        {
            return;
        }
        
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];

        if ( OW::getUser()->getId() == $userId )
        {
            return;
        }
        
        $linkId = uniqid("lovematch-");
        $lang = OW::getLanguage();
        $user = BOL_UserService::getInstance()->getUserName((int) $params['userId']);

        if ( BOL_UserService::getInstance()->isBlocked(OW::getUser()->getId(), (int) $params['userId']) )
        {
            $script =
            '$("#' . $linkId . '").click(function(){
                window.OW.error(' . json_encode($lang->text('base', 'user_block_message')) . ');
            });
            ';
        }
        else
        {
            $url = OW::getRouter()->urlForRoute('lovematch.detail.user', ['username'=>$user]);
            
            $script =
            '$("#' . $linkId . '").click(function(){
                document.location.href="'.$url.'";
            });
            ';
        }

        if ( !empty($script) )
        {
            OW::getDocument()->addOnloadScript($script);
        }
        
        $resultArray = array(
        BASE_CMP_ProfileActionToolbar::DATA_KEY_LABEL => OW::getLanguage()->text('lovematch', 'profile_toolbar_item_matchme'),
        BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_HREF => 'javascript://',
        BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ID => $linkId,
        BASE_CMP_ProfileActionToolbar::DATA_KEY_ITEM_KEY => "lovematch.matchme",
        BASE_CMP_ProfileActionToolbar::DATA_KEY_LINK_ORDER => 0
        );

        $event->add($resultArray);
        
    }
    
    public function addQuickLink( BASE_CLASS_EventCollector $event )
    {
        $searchID = OW_Auth::getInstance()->getUserId();
        $service = LOVEMATCH_BOL_UsermatchDao::getInstance();
        
        
        $userMatchList = $service->getUsermatchList($searchID);
        $count = count($userMatchList);
        if ( $count > 0 )
        {
            $url = OW::getRouter()->urlForRoute('lovematch.index');
            $event->add(array(
                BASE_CMP_QuickLinksWidget::DATA_KEY_LABEL => OW::getLanguage()->text('lovematch', 'my_matchlist'),
                BASE_CMP_QuickLinksWidget::DATA_KEY_URL => $url,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT => $count,
                BASE_CMP_QuickLinksWidget::DATA_KEY_COUNT_URL => $url,
            ));
        }
    }
    
    
    function questionsFieldInit( OW_Event $e )
    {
        $params = $e->getParams();
        
        if ( $params['fieldName'] == 'lovematch_time' )
        {
            $formElement = new LOVEMATCH_CLASS_Time($params['fieldName']);   
            $e->setData($formElement);
        }
        else if ( $params['fieldName'] == 'lovematch_location' )
        {
            $formElement = new LOVEMATCH_CLASS_Location($params['fieldName']);
            $e->setData($formElement);
        }
    }
    
    function questionsFieldGetValue( OW_Event $e )
    {
        $params = $e->getParams();
        $currentUser = OW::getUser();
        $showInfo =  $params['userId'] == $currentUser->getId() || $currentUser->isAdmin();
        
        if ( $params['fieldName'] == 'lovematch_time' )
        {
            $v = $params['value']; 
            if(!$showInfo)
            {
                $data = ' - private - ';
            }
            elseif ( !isset($v['hour']) || !isset($v['minute']) || $v['hour'] == -1 || $v['minute'] == -1 )
            {
                $data = '--:--';
            }
            else
            {
                $data = implode(':',$params['value']);
            }
            
            $e->setData($data);
        }
        else if ( $params['fieldName'] == 'lovematch_location' )
        {
            if ($showInfo)
            {
                $out = $params['value']['city'];
            }
            else
            {
                $out = ' - private - ';
            }
            
            $uid = $params['userId'];
            $userinfo = $infoList = BOL_QuestionService::getInstance()->getQuestionData([$uid], ['username']);
            $userinfo = $userinfo[$uid];
            
            $templatePath = OW_PluginManager::getInstance()->getPlugin('lovematch')->getViewDir().'controllers/matchlist_horoscope.html';
            $ctrl = new LOVEMATCH_CTRL_Matchlist();
            $ctrl->setTemplate($templatePath);
            
            $ctrl->horoscope($userinfo);
            
            $out.= $ctrl->render();
            $e->setData($out);
            
            
        }
        
    }
    
    function questionsGetData( OW_Event $e )
    {
        
        $params = $e->getParams();
        $data = $e->getData();
        
        if ( in_array('lovematch_time', $params['fieldsList']) || in_array('lovematch_location', $params['fieldsList']) )
        {
            $results = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserIdList($params['userIdList']);
            foreach($results as $person)
            {
                $data[$person->uid]['lovematch_time'] = array( 
                    'hour' => $person->hour,
                    'minute' => $person->minute
                );
                $data[$person->uid]['lovematch_location'] = array(
                    'city' => $person->city,
                    'lat' => $person->lat,
                    'lng' => $person->lng    
                );
            }
        }
        
        
        
        $e->setData($data);
    }
    
    public function questionsSaveData( OW_Event $e )
    {
        $params = $e->getParams();
        $data = $e->getData();
        
        $userdata = array(
            'uid' => $params["userId"],
            'email' => $data["email"],
            'sex' => ($data["sex"]==1 ? 'male':'female' ),
            'hour'=> $data['lovematch_time']['hour'],
            'minute'=> $data['lovematch_time']['minute'],
            'lat'=> $data['lovematch_location']['lat'],
            'lng'=> $data['lovematch_location']['lng'],
            'city'=> $data['lovematch_location']['city']
        );
        
        $birthdate = date_parse_from_format('Y/m/d',$data['birthdate']);
        $userdata['year'] = $birthdate !== false ? $birthdate['year']:'';
        $userdata['month'] = $birthdate !== false ? $birthdate['month']:'';
        $userdata['day'] = $birthdate !== false ? $birthdate['day']:'';
        
        
        
        LOVEMATCH_BOL_UserdataDao::getInstance()->saveOrUpdateUserdata($userdata);
        
        $e->setData($data);
    }
    
    public function onUserEdit(OW_Event $event) {
        $url = OW::getRouter()->urlForRoute('base_member_profile');
        OW::getApplication()->redirect($url);
    }
	
	
	public function privacyAddAction( BASE_CLASS_EventCollector $astro )
    {
        $language = OW::getLanguage();

        $action = array(
            'key' => 'user_chart',
            'pluginKey' => 'lovematch',
            'label' => $language->text('lovematch', 'privacy_action_user_chart'),
            'description' => 'Display or hide your personal astro chart',
            'defaultValue' => 'everybody'
        );
		
		$action_detail = array(
            'key' => 'match_detail',
            'pluginKey' => 'lovematch',
            'label' => $language->text('lovematch', 'privacy_action_match_detail'),
            'description' => 'Display or hide your matching details (aspects, compatibility of planets, elements...)',
            'defaultValue' => 'everybody'
        );

        $astro->add($action);
		$astro->add($action_detail);
    }


    public function init()
    {
        $em = OW::getEventManager();
        
        $em->bind(OW_EventManager::ON_USER_EDIT, array($this, 'onUserEdit'));
        $em->bind(BASE_CMP_ProfileActionToolbar::EVENT_NAME, array($this, 'addProfileToolbarAction'));
        $em->bind(BASE_CMP_QuickLinksWidget::EVENT_NAME, array($this, 'addQuickLink'));
        
        $em->bind('base.questions_field_init', array($this, 'questionsFieldInit'));
        $em->bind('base.questions_field_get_value', array($this, 'questionsFieldGetValue'));
        
        $em->bind('base.questions_save_data', array($this, 'questionsSaveData'));
        
        $em->bind('base.questions_get_data', array($this, 'questionsGetData'));
		$em->bind('plugin.privacy.get_action_list', array($this, 'privacyAddAction'));
		
//        OW::getEventManager()->bind('base.question.search_sql', array($this, 'questionSearchSql'));
//
        
//        
        //OW::getEventManager()->bind('base.questions_field_add_fake_questions', array($this, 'addFakeQuestions'));
        
    }
    
}
?>
