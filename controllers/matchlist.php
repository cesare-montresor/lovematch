<?php

class LOVEMATCH_CTRL_Matchlist extends OW_ActionController 
{  
    public function test()
    {
        $id1 = $currentUID = OW_Auth::getInstance()->getUserId();
        $id2 = BOL_UserService::getInstance()->findByUsername('kali')->id;
        
        $detail = LOVEMATCH_BOL_Astro::getInstance()->calculateMatch($id1,$id2);
        
        echo '<pre>';
        print_r($detail);
        echo '</pre>';
    }
    
    public function index() 
    { 
        $this->setPageTitle(OW::getLanguage()->text('lovematch', 'index_page_title')); 
        $this->setPageHeading(OW::getLanguage()->text('lovematch', 'index_page_heading')); 
        
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
            $matchList = LOVEMATCH_BOL_UsermatchDao::getInstance()->getUsermatchList($currentUID);
            $uids = [];
            foreach ($matchList as $match) {
                $uids[]=$match['uid'];
            }
            $infoList = BOL_QuestionService::getInstance()->getQuestionData($uids, ['username', 'realname']);
            
            $avatarList = BOL_AvatarService::getInstance()->getDataForUserAvatars($uids);
            $onlineList = BOL_UserService::getInstance()->findOnlineStatusForUserList($uids);
            
            $this->assign('matchList',$matchList);
            $this->assign('infoList',$infoList);
            $this->assign('avatarList',$avatarList);
            $this->assign('onlineList',$onlineList);
        }
        $this->assign('showList',$showList);
        $this->assign('maxScore', 380); //before 280
    }
    
    public function detail($params)
    {
        $this->assign('maxScore', 380);
        
        $this->setPageTitle(OW::getLanguage()->text('lovematch', 'index_page_title')); 
        $this->setPageHeading(OW::getLanguage()->text('lovematch', 'index_page_heading')); 
        
        $valid = TRUE;
        $currentUID = OW_Auth::getInstance()->getUserId();
        $currentUser = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($currentUID);
        
        if (is_null($currentUser) || $currentUser->complete == 'NO')
        {
            $valid = FALSE;
            $userEditUrl = OW::getRouter()->urlForRoute('base_edit');
            $this->assign('userEditUrl',$userEditUrl);
        }
        else
        {
            $user1 = BOL_UserDao::getInstance()->findById($currentUID);
            $user2 = BOL_UserService::getInstance()->findByUsername($params['username']);
            $match = LOVEMATCH_BOL_UsermatchDao::getInstance()->findMatchByUserIds($user1->id,$user2->id);
            

            
            $infoList = BOL_QuestionService::getInstance()->getQuestionData([$user1->id,$user2->id], ['username', 'realname']);
            $avatarList = BOL_AvatarService::getInstance()->getAvatarsUrlList([$user1->id,$user2->id],2);
			
            $detail = json_decode($match->raw_data,true);
            $category = array();
            foreach($detail as $line)
            {
                $category[$line['category']][] = $line; 
            }
            $this->assign('category', $category);
            
            $this->assign('score',$match->score);
            
            $this->assign('username1',$infoList[$user1->id]['username']);
            $this->assign('realname1',$infoList[$user1->id]['realname']);
            $this->assign('avatar1',$avatarList[$user1->id]);
            
            
            $this->assign('username2',$infoList[$user2->id]['username']);
            $this->assign('realname2',$infoList[$user2->id]['realname']);
            $this->assign('avatar2',$avatarList[$user2->id]);
            
        }
        $this->assign('valid',$valid);
		
		$isAuthorized = LOVEMATCH_BOL_Service::getInstance()->checkPrivacyDetails($user2->id);
        $this->assign('isAuthorized', $isAuthorized);
		
		$isAuthorizedViewChart = LOVEMATCH_BOL_Service::getInstance()->checkPrivacyChart($user2->id);
        $this->assign('isAuthorizedViewChart', $isAuthorizedViewChart);
    }
    
    public function horoscopeText($params)
    {
        $type = $params['type'];
        $key = $params['key'];
        
        $basepath =  OW_PluginManager::getInstance()->getPlugin('lovematch')->getStaticDir();
        $filename = $basepath . "txt/dst/$type.json";
        if (file_exists($filename))
        {
            $content = file_get_contents($filename);
            $data = json_decode($content,true);
            
            if (key_exists($key, $data))
            {
                header('Content-type: application/json');
                echo json_encode($data[$key]);
                exit();
            }
        }
        header('Content-type: application/json');
        echo json_encode(array('title'=>'Not found','data'=>'Not found'));
        exit();
    }
    
    public function horoscope($params)
    {
        $username = $params['username'];
        $user = BOL_UserService::getInstance()->findByUsername($username);

        LOVEMATCH_BOL_UserdataDao::getInstance()->updateHoroscopeData($user->id);
        $currentUser = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($user->id);
        
        $astrodata = json_decode($currentUser->raw_data,true);
        //print_r($astrodata);
        $planetList = $astrodata['planet'];
        $planetList['ascendant'] = $astrodata['other']['ascendant'];
        $this->assign('planetList', $planetList );
        $this->assign('houseList', $astrodata['house']);
        $this->assign('aspectList', $astrodata['aspect']);
        $urlChart = OW::getRouter()->urlForRoute('lovematch.horoscope.draw.natal',$params);
        $this->assign('urlChart', $urlChart);
        $aspectNameList = array(
            0=>'Conjunction',
            120=>'Trine',
            60=>'Sextile',
            90=>'Square',
            180=>'Opposition',
        );
        $this->assign('aspectNameList', $aspectNameList);
		
		
		$elementSign = [
            'aries'=>'fire',
            'taurus'=>'earth',
            'gemini'=>'air',
            'cancer'=>'water',
            'leo'=>'fire',
            'virgo'=>'earth',
            'libra'=>'air',
            'scorpio'=>'water',
            'sagittarius'=>'fire',
            'capricorn'=>'earth',
            'aquarius'=>'air',
            'pisces'=>'water'
        ];
		
		$gunasSign = [
            'aries'=>'cardinal',
            'taurus'=>'fixed',
            'gemini'=>'mutable',
            'cancer'=>'cardinal',
            'leo'=>'fixed',
            'virgo'=>'mutable',
            'libra'=>'cardinal',
            'scorpio'=>'fixed',
            'sagittarius'=>'mutable',
            'capricorn'=>'cardinal',
            'aquarius'=>'fixed',
            'pisces'=>'mutable'
        ];
		
		$elements = array('earth' => 0, 'water' => 0, 'fire' => 0, 'air' => 0);
		$gunas = array('cardinal' => 0, 'fixed' => 0, 'mutable' => 0);
		$p = array(
			'sun',
			'moon',
			'mercury',
			'venus',
			'mars',
			'jupiter',
			'saturn',
			'uranus',
			'neptune',
			'pluto',
			'ascendant'
		);
		foreach($planetList as $planet)
		{
			if(in_array($planet['name'], $p))
			{
				$points = 1;
				if(in_array($planet['name'], array('sun', 'moon', 'ascendant')))
				{
					$points = 3;
				}
				$s = $planet['sign'];
				$e = $elementSign[$s];
				$g = $gunasSign[$s];
				$elements[$e] += $points;
				$gunas[$g] += $points;
			}
		}
		$this->assign('elements', $elements);
		$this->assign('gunas', $gunas);
		
		$isAuthorized = LOVEMATCH_BOL_Service::getInstance()->checkPrivacyChart($user->id);
        $this->assign('isAuthorized', $isAuthorized);
    }
     
}
