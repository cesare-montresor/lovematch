<?php

class LOVEMATCH_BOL_UserdataDao extends OW_BaseDao
{
 
    /**
     * Constructor.
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Singleton instance.
     *
     * @var LOVEMATCH_BOL_UserdataDao
     */
    private static $classInstance;
 
    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return LOVEMATCH_BOL_UserdataDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
 
        return self::$classInstance;
    }
 
    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'LOVEMATCH_BOL_Userdata';
    }
 
    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'lovematch_userdata';
    }
    
    public function deleteAll()
    {
        $sql = 'DELETE FROM ' . $this->getTableName();
        $this->dbo->delete($sql);
    }
    
    public function findByUserId($uid)
    {
        $example = new OW_Example();
        $example->andFieldEqual('uid', $uid);
        return LOVEMATCH_BOL_UserdataDao::getInstance()->findObjectByExample($example);
    }
    
    public function findByUserIdList(array $uid)
    {
        $example = new OW_Example();
        $example->andFieldInArray('uid', $uid);
        return LOVEMATCH_BOL_UserdataDao::getInstance()->findListByExample($example);
    }
    
    public function saveOrUpdateUserdata(array $data, $cascadeUpdate = true)
    {
        if( !isset($data['uid']) || !is_numeric($data['uid']) || (int)$data['uid']<0 )
        {
            return false;
        }
        //print_r($data);
        
        $updateField = array('sex','year','month','day','hour','minute','lat','lng','city');
        
        $needUpdate = FALSE;
        $complete = TRUE;
        
        $user = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($data['uid']);
        
        if ($user == null)
        {
            $needUpdate = TRUE;
            $user = new LOVEMATCH_BOL_Userdata();
            $user->uid = $data['uid'];
            $user->email = $data['email'];
        }
        
        foreach ($updateField as $field) 
        {
            if( $user->$field != $data[$field])
            {
                $user->$field = $data[$field];
                $needUpdate = TRUE;
                //echo 'Need update:'.$field."<br/>";
            }
        }
        
        if( $user->hour < 0 || $user->hour > 23 )
        {
            $complete = FALSE;
        }
        
        if( $user->minute < 0 || $user->minute > 59 )
        {
            $complete = FALSE;
        }
        
        if( empty($user->lat) || $user->lat < -90 || $user->lat > 90 )
        {
            $complete = FALSE;
        }
        
        if( empty($user->lng) || $user->lng < -180 || $user->lng > 180 )
        {
            $complete = FALSE;
        }
        
        if( empty($user->city) )
        {
            $complete = FALSE;
        }
        
        if ($needUpdate && $complete)
        {
            $birthtime = mktime($user->hour, $user->minute, 0, $user->month, $user->day, $user->year);
            if($birthtime === false)
            {
                $complete = FALSE;
                $user->offset_tz = '';
            }
            else
            {
                $timezone = LOVEMATCH_BOL_Astro::calculateTimeOffset($user->day, $user->month, $user->year, $user->day, $user->year, $user->lat, $user->lng);
                if ( $timezone === FALSE )
                {
                    $complete = FALSE;
                    $user->offset_tz = '';
                }
                else   
                {   
                    $user->offset_tz = $timezone;
                }
            }
        }
        
        $user->complete = $complete?'YES':'NO';
        
        
        if ( $needUpdate )   
        {
            //echo '<pre>';
            //print_r($user);
            //echo '</pre>';
            LOVEMATCH_BOL_UserdataDao::getInstance()->save($user);
            if ( $cascadeUpdate )
            {
                if ($complete)
                {
                    $this->updateHoroscopeData($user->uid);
                    LOVEMATCH_BOL_UsermatchDao::getInstance()->calculateUserMatchList($user->uid);
                }
                else
                {
                    LOVEMATCH_BOL_UsermatchDao::getInstance()->deleteByUserId($user->uid);
                }
             
            }
        }
        //die();
        return true;
    }

    public function updateHoroscopeData($uid)
    {
        $user = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($uid);
        if ($user->complete == 'YES') 
        {
            //echo '<pre>';
            $time = mktime($user->hour, $user->minute, 0, $user->month, $user->day, $user->year);
            $time_utc = $time - ($user->offset_tz * 60 * 60 );
            
            $day = date('d', $time_utc);
            $month = date('m', $time_utc);
            $year = date('Y', $time_utc);
            $hour = date('H', $time_utc);
            $minute = date('i', $time_utc);
            
            $astrodata = LOVEMATCH_BOL_Astro::getInstance()->calculateHoroscope($day,$month,$year,$hour,$minute,$user->lat,$user->lng);
            //print_r($astrodata);
            
            
            $user->sun = $astrodata['planet']['sun']['sign'];
            $user->ascendant = $astrodata['other']['ascendant']['sign'];
            $user->moon = $astrodata['planet']['moon']['sign'];
            $user->mars = $astrodata['planet']['mars']['sign'];
            $user->venus = $astrodata['planet']['venus']['sign'];
            
            $user->mercury = $astrodata['planet']['mercury']['sign'];
            $user->jupiter = $astrodata['planet']['jupiter']['sign'];
            $user->saturn = $astrodata['planet']['saturn']['sign'];
            $user->uranus = $astrodata['planet']['uranus']['sign'];
            $user->neptune = $astrodata['planet']['neptune']['sign'];
            $user->true_node = $astrodata['planet']['north node']['sign'];
            $user->mc = $astrodata['planet']['saturn']['sign'];
            $user->raw_data = json_encode($astrodata,JSON_PRETTY_PRINT);
            
            
            //print_r($user);
            LOVEMATCH_BOL_UserdataDao::getInstance()->save($user);
            //echo '</pre>';
            
        }
    }


    public function addUserdata( $uid, $email, $birthdate, $sex, $sun, $moon, $mars, $venus, $ascendant )
    {
        $example = new OW_Example();
        $example->andFieldEqual('uid', $uid);
        
        $user = LOVEMATCH_BOL_UserdataDao::getInstance()->findObjectByExample($example);
        if (is_null($user))
        {
            $user = new LOVEMATCH_BOL_Userdata();
            $user->uid = $uid;
        }
        
        $user->email = $email;
        $user->birthdate = $birthdate;
        $user->sex = $sex;
        $user->sun = $sun;
        $user->moon = $moon;
        $user->mars = $mars;
        $user->venus = $venus;
        $user->ascendant = $ascendant;
        $user->complete = 'YES';
        
        $starSign = LOVEMATCH_BOL_Astro::$starSigns;
        if (!in_array($user->sun, $starSign)) {
            $user->sun = '';
            $user->complete = 'NO';
        }
        if (!in_array($user->ascendant, $starSign)) {
            $user->ascendant = '';
            $user->complete = 'NO';
        }
        if (!in_array($user->moon, $starSign)) {
            $user->moon = '';
            $user->complete = 'NO';
        }
        if (!in_array($user->mars, $starSign)) {
            $user->mars = '';
            $user->complete = 'NO';
        }
        if (!in_array($user->venus, $starSign)) {
            $user->venus = '';
            $user->complete = 'NO';
        }
        
        LOVEMATCH_BOL_UserdataDao::getInstance()->save($user);
        
        return $user->complete == 'YES';
    }
    
    public function getUserIdList(){
        $userList = BOL_UserService::getInstance();
        $usersCount = $userList->count();
        $userIds = $userList->findList(0, $usersCount);
        $userIdList = array();
        foreach ( $userIds as $user )
        {
            if ( !in_array($user->id, $userIdList) )
            {
                array_push($userIdList, $user->id);
            }
        }
        return $userIdList;
    }
 
    public function getUserdataList($gender=null)
    {
        if($gender == 'male' || $gender == 'female')
        {
            $example = new OW_Example();
            $example->andFieldEqual('sex', $gender);

            return LOVEMATCH_BOL_UserdataDao::getInstance()->findListByExample($example);
        }
        else
        {
            return LOVEMATCH_BOL_UserdataDao::getInstance()->findAll();
        }
        
    }
}
