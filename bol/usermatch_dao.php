<?php

class LOVEMATCH_BOL_UsermatchDao extends OW_BaseDao
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
        return 'LOVEMATCH_BOL_Usermatch';
    }
 
    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'lovematch_usermatch';
    }
    
    public function deleteAll()
    {
        $sql = 'DELETE FROM ' . $this->getTableName();
        $this->dbo->delete($sql);
    }
    
    public function deleteByUserId($uid)
    {
        $sql = 'DELETE FROM ' . $this->getTableName() . 
                ' WHERE ' .
                '`uid1` = "' . $this->dbo->escapeString($uid). '" OR '.
                '`uid2` = "' . $this->dbo->escapeString($uid). '" ';
        $this->dbo->delete($sql);
    }
    
    public function getUsermatchList($searchUID,$count=null,$page=null)
    {
        //$currentUID = OW_Auth::getInstance()->getUserId();
        //$currentUID = 4;
        $sql = 'SELECT * FROM ' . $this->getTableName() .
                ' WHERE ' .
                '`uid1` = "' . $this->dbo->escapeString($searchUID). '" OR '.
                '`uid2` = "' . $this->dbo->escapeString($searchUID). '" '.
                ' ORDER BY score DESC ';
        if(!is_null($count))
        {
            $page = (is_null($page)?0:$page);
            $sql.= " LIMIT ".($count*$page).",".$count;
        }
        //echo $sql;
        $matchList = $this->dbo->queryForObjectList($sql, $this->getDtoClassName() );
        
        $userMatch = array();
        foreach ($matchList as $match)
        {
            $uid = ($match->uid1 == $searchUID?$match->uid2:$match->uid1);
            $userMatch[]=  array(
                "uid" => $uid,
                "score" => $match->score
            );
        }
        return $userMatch;
    }
    
    public function findMatchByUserIds($uid1,$uid2)
    {
        $id1 = min($uid1,$uid2);
        $id2 = max($uid1,$uid2);
        
        
        $example = new OW_Example();
        $example->andFieldEqual('uid1', $id1)
                ->andFieldEqual('uid2', $id2);
        
        $loveMatch = LOVEMATCH_BOL_UsermatchDao::getInstance()->findObjectByExample($example);
        return $loveMatch;
    }
    
    public function findMatchByUsernames($username1,$username2)
    {
        $user1 = BOL_UserService::getInstance()->findByUsername($username1);
        $user2 = BOL_UserService::getInstance()->findByUsername($username2);
        
        if(!is_null($user1) && !is_null($user2))
        {
            return findMatchForUserIds($user1->id,$user2->id);
        }
        return null;
    }

    public function calculateMatchBulk(){
        $users = LOVEMATCH_BOL_UserdataDao::getInstance()->findAll();
        $matchCount = 0;
        for($i=0; $i < count($users); $i++){
            if($users[$i]->complete == 'YES')
            {
                $matchCount+=$this->calculateUserMatchList($users[$i]->uid);
//                for($k=$i+1; $k < count($users); $k++){
//                    $res = LOVEMATCH_BOL_UsermatchDao::getInstance()->calculateUserMatch($users[$i], $users[$k]);
//                    if ($res) {
//                        $matchCount++;
//                    }
//                }
            }
        }
        return $matchCount;
    }
    
    
    public function calculateUserMatchList($uid)
    {
        $user = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($uid);
//        print_r($uid);
        $matchCount = 0;
        if ($user->complete == 'YES')
        {
            $gender = ($user->sex == 'male'?'female':'male');
            $users = LOVEMATCH_BOL_UserdataDao::getInstance()->getUserdataList($gender);
            
            for($i=0; $i < count($users); $i++)
            {
                if ($users[$i]->uid != $user->uid ) 
                {
                    $res = LOVEMATCH_BOL_UsermatchDao::getInstance()->calculateUserMatch($user, $users[$i]);
                    if ($res) {
                        $matchCount++;
                    }
                }
            }
        }
        return $matchCount;
    }
    
    
    public function calculateUserMatch($user1,$user2)
    {
        if ($user1->complete != 'YES' || $user2->complete != 'YES' )
        {
            return false;
        }
        
        $uid1 = min($user1->uid,$user2->uid);
        $uid2 = max($user1->uid,$user2->uid);
        
        
        $example = new OW_Example();
        $example->andFieldEqual('uid1', $uid1)
                ->andFieldEqual('uid2', $uid2);
        
        $loveMatch = LOVEMATCH_BOL_UsermatchDao::getInstance()->findObjectByExample($example);
        if (is_null($loveMatch))
        {
            $loveMatch = new LOVEMATCH_BOL_Usermatch();
            $loveMatch->uid1 = $uid1;
            $loveMatch->uid2 = $uid2;
        }
        $matchinfo = LOVEMATCH_BOL_Astro::getInstance()->calculateMatch($uid1,$uid2);
        $total = 0;
        foreach($matchinfo as $line)
        {
            $total += $line['total'];
        }
        
        $loveMatch->score = $total;
        $loveMatch->raw_data = json_encode($matchinfo);
        
        LOVEMATCH_BOL_UsermatchDao::getInstance()->save($loveMatch);
        return true;
    }
    
    
    
        
}
?>
