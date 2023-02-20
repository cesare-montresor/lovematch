<?php

class LOVEMATCH_CTRL_Draw extends OW_ActionController 
{  
    public function test()
    {
        
        //$match = LOVEMATCH_BOL_UsermatchDao::getInstance()->findMatchByUserIds($user1->id,$user2->id);
        $user = BOL_UserService::getInstance()->findByUsername('cesarem');
        $currentUser = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($user->id);
        $data = json_decode($currentUser->raw_data,true);
        
        $chart = new LOVEMATCH_BOL_DrawChart();
        $chart->drawNatal($data,700);
        
        
        exit();
        
    }
    
    public function horoscopeNatal($params)
    {
        $user = BOL_UserService::getInstance()->findByUsername($params['username']);
        $currentUser = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($user->id);
        $data = json_decode($currentUser->raw_data,true);
        
        $chart = new LOVEMATCH_BOL_DrawChart();
        $chart->drawNatal($data,700);
        
        
        exit();
    }
    
    public function horoscopeSynastry($params)
    {
        $user1 = OW_Auth::getInstance()->getUserId();
        $user2 = BOL_UserService::getInstance()->findByUsername($params['username']);
        $userdata1 = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($user1);
        $userdata2 = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($user2->id);
        $data1 = json_decode($userdata1->raw_data,true);
        $data2 = json_decode($userdata2->raw_data,true);
       
        $chart = new LOVEMATCH_BOL_DrawChart();
        $chart->drawSynastry($data1,$data2,700);
        
        exit();
    }
    
    public function horoscopeComposite($params)
    {
        $user1 = OW_Auth::getInstance()->getUserId();
        $user2 = BOL_UserService::getInstance()->findByUsername($params['username']);
        $userdata1 = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($user1);
        $userdata2 = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($user2->id);
        $data1 = json_decode($userdata1->raw_data,true);
        $data2 = json_decode($userdata2->raw_data,true);
       
        $chart = new LOVEMATCH_BOL_DrawChart();
        $chart->drawComposite($data1,$data2,700);
    }
    
    
    
    
     
}
