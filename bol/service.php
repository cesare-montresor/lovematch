<?php

class LOVEMATCH_BOL_Service
{
    
    
    
    /**
     * Singleton instance.
     *
     * @var LOVEMATCH_BOL_Service
     */
    private static $classInstance;
 
    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return LOVEMATCH_BOL_Service
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
 
        return self::$classInstance;
    }
    
    private function __construct()
    {
 
    }
    
    public function  getUserData($uid)
    {
        if (!is_array($uid) ) {
            $uid = array($uid);
        }
        $userData = BOL_QuestionService::getInstance()->getQuestionData($uid, array( 'email', 'sex', 'birthdate', 'sun', 'moon', 'ascendant', 'mars', 'venus' ));
        $keys = array_keys($userData);
        for($i=0;$i<count($userData);$i++)
        {
            $key = $keys[$i];
            $lang = OW_Language::getInstance();
            $userData[$key]['sun'] = $lang->text('base','questions_question_sun_value_'.$userData[$key]['sun']); 
            $userData[$key]['ascendant'] = $lang->text('base','questions_question_ascendant_value_'.$userData[$key]['ascendant']); 
            $userData[$key]['moon'] = $lang->text('base','questions_question_moon_value_'.$userData[$key]['moon']); 
            $userData[$key]['mars'] = $lang->text('base','questions_question_mars_value_'.$userData[$key]['mars']); 
            $userData[$key]['venus'] = $lang->text('base','questions_question_venus_value_'.$userData[$key]['venus']); 
        }
        
        return $userData;
    }
    
    public function dataHasChanged($uid)
    {
        $result = FALSE;
        $userData = self::getInstance()->getUserData($uid);
        $userData = $userData[$uid];
        
        $userMatchData = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($uid);
        
        echo '<pre>';
        print_r($userData);
        echo '-----------------------';
        print_r($userMatchData);
        echo '</pre>';
        
        if ( is_null($userMatchData) ) {
            $result = TRUE;
        }
        if ($userData["sun"] != $userMatchData->sun) {
            $result = TRUE;
        }
        if ($userData["moon"] != $userMatchData->moon) {
            $result = TRUE;
        }
        if ($userData["ascendant"] != $userMatchData->ascendant) {
            $result = TRUE;
        }
        if ($userData["mars"] != $userMatchData->mars) {
            $result = TRUE;
        }
        if ($userData["venus"] != $userMatchData->venus) {
            $result = TRUE;
        }
        
        return $result;
    }
    
    public function copyData($uid,$userData)
    {
        print_r($userData);
        $email = $userData['email'];
        $birthdate = $userData['birthdate'];
        $sex = $userData['sex'];
        if($sex == '1'){
            $gender = 'male';
        }
        else{
            $gender = 'female';
        }
        
        $sun = $userData['sun']; 
        $moon = $userData['moon'];
        $mars = $userData['mars'];
        $venus = $userData['venus']; 
        $ascendant = $userData['ascendant'];
        
        
        
        
        //we insert this user in the lovematch_userdata table
        LOVEMATCH_BOL_UserdataDao::getInstance()->addUserdata($uid, $email, $birthdate, $sex, $sun, $moon, $mars, $venus, $ascendant);  
    } 
    
    public function copyDataById($uid)
    {
        $userData = self::getInstance()->getUserData($uid);
        $this->copyData($uid, $userData[$uid]);
    }

    
    public function copyDataBulk() 
    { 
        $users = BOL_UserDao::getInstance()->findAll();
        foreach($users as $users){
            $this->copyDataById($users->id);
        }
    }
	
	//check privacy settings 
    public function checkPrivacyChart($uid)
    {
        $isAuthorized = true;
        
        $objParams =  array(
                'action' => 'user_chart',
                'ownerId' => $uid,
                'viewerId' => OW::getUser()->getId()
        );
        
        try
        {
          OW::getEventManager()->getInstance()->call('privacy_check_permission', $objParams);
        }
        catch( RedirectException $e )
        {
            $isAuthorized = false;
        }
        
        return $isAuthorized;
    }
	
	public function checkPrivacyDetails($uid)
    {
        $isAuthorized = true;
        
        $objParams =  array(
                'action' => 'match_detail',
                'ownerId' => $uid,
                'viewerId' => OW::getUser()->getId()
        );
        
        try
        {
           OW::getEventManager()->getInstance()->call('privacy_check_permission', $objParams);
        }
        catch( RedirectException $e )
        {
            $isAuthorized = false;
        }
        
        return $isAuthorized;
    }
    
    
    
    
    
    
}

?>
