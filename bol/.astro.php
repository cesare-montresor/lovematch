<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class LOVEMATCH_BOL_Astro
{
    private static $cmdPath = "/usr/local/bin/swetest";
    private static $geonameApiUrl = "http://api.geonames.org/timezoneJSON?lat=%s&lng=%s&username=%s";
    private static $geonameApiUsername = "cesare.montresor";
    public static $starSigns = array('aries','taurus','gemini', 'cancer','leo','virgo','libra',
                'scorpio','sagittarius','capricorn','aquarius','pisces');
    
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
    
    
    
    public function __construct()
    {
 		self::$cmdPath = OW::getPluginManager()->getPlugin('lovematch')->getStaticDir().'sweph/swetest';
    }
    
    public static function calculateTimeOffset($day,$month,$year,$hour,$minute,$lat,$lng)
    {
        $timezoneUrl = sprintf(self::$geonameApiUrl,  $lat,  $lng, self::$geonameApiUsername);
        echo $timezoneUrl;
        $timezoneRaw = file_get_contents($timezoneUrl);
        $timezoneData = json_decode($timezoneRaw, true);
        if(is_array($timezoneData))
        {
            $timezoneId = $timezoneData['timezoneId'];
            $dstOffset = $timezoneData['dstOffset'];
            $gmtOffset = $timezoneData['gmtOffset'];

            $currentTZ = date_default_timezone_get();
            
            date_default_timezone_set($timezoneId);
            $time = mktime($hour, $minute, 0, $month, $day, $year);
            $is_dst = date('I',$time);
            $timezone = ( $is_dst == 1 ? $dstOffset : $gmtOffset );
            
            date_default_timezone_set($currentTZ);
            return $timezone;
        }
        
        return false;
    }
    
    
    public function calculateHoroscope($day,$month,$year,$hour,$minute,$lat,$lng)
    {
        
        $cmdPlanets = "-p0123456789tAFDGHI";
        $cmdDate = sprintf("-b%d.%d.%d",$day,$month,$year);
        $cmdTime = sprintf("-ut%d.%d",$hour,$minute);
        
        $cmdHouse = sprintf("-house%f,%f,A",$lng,$lat); 
        $cmdGeo = sprintf("-geopos%f,%f,0",$lng,$lat);
        
        $cmdSeparator = '-g"|"';
        $cmdHeader = '-head';
        
        $cmd = self::$cmdPath ." $cmdPlanets $cmdDate $cmdTime $cmdHouse $cmdGeo $cmdSeparator $cmdHeader";
        //echo $cmd;
        exec($cmd,$output);
        //print_r($output);
        
        $data = array();
        
        $planet = array();
        $house = array();
        $aspect = array();
        $other = array();
        
        foreach($output as $line)
        {
            $col=  explode("|", $line);
            $colCount = count($col);
            $kind = 'other';
            
            if ( $colCount >= 2 ) 
            {
                $parts = explode('.', $col[1]);
                $col[1] = $parts[0];
                $tmp['name'] = strtolower(trim($col[0]));
                if($tmp['name']=='mean apogee')
                {
                    $tmp['name']='lilith';
                }
                else if($tmp['name']=='true node')
                {
                    $tmp['name']='north node';
                }
                $tmp['longitude'] =  trim($col[1]); 
                $tmp['longitude_dec'] =  $this->degToDec(trim($col[1]));
                $tmp['sign'] = $this->longitudeToSign(trim($col[1]));
                $tmp['longitude_sign']=  $this->longitudeInSign(trim($col[1]));
                
                if( $colCount == 2 && substr($tmp['name'],0,5) == 'house' )
                {
                    $kind = 'house';
                    $tmp['name'] = (int)substr($tmp['name'],5);
                    $tmp['planet'] = array();
                }
                else if ( $colCount == 5 ) 
                {
                    $kind = 'planet';
                    $tmp['latitude']=trim($col[2]);
                    $tmp['declination']=trim($col[3]);
                    $tmp['speed']=trim($col[4]);
                }
            }
            
            
            $data[$kind][$tmp['name']] = $tmp;
        }
        
        
        
        $keyPlanet = array_keys($data['planet']);
        for($i=0; $i<count($keyPlanet); $i++)
        {
            $planet = &$data['planet'][$keyPlanet[$i]];
            
            for($k=1; $k<=count($data['house']); $k++) 
            {   
                $house1 = &$data['house'][$k];
                if($k < count($data['house']))
                {
                    $house2 = $data['house'][$k+1];
                }
                else
                {
                    $house2 = $data['house'][1];
                }
                
                if ($planet['longitude_dec'] > $house1['longitude_dec'] && 
                    $planet['longitude_dec'] < $house2['longitude_dec'])
                {
                    $planet['house']=$k;
                    $house1['planet'][]=$planet['name'];
                }
                
            }
        }
//        echo '<pre>';
//        print_r($data);
//        echo '</pre>';
        
        
        
        $aspectItems = $data['planet'];
        $aspectItems['ascendant'] = $data['other']['ascendant'];
        
        $data['aspect'] = $this->calculateAspects($aspectItems);
        
        return $data;
    }
    
    private function degToDec($deg)
    {
        $part = explode("°", $deg);
        $deg = $part[0];
        $part = explode("'", $part[1]);
        $min = $part[0];
        $sec = $part[1];
        return $deg + ((($min*60)+($sec))/3600);
    }    
    
    private function deg($pos)
    {
        $col=explode("°", $pos);
        $deg = $col[0];
        $signPos = $deg/30;
        return self::$starSigns[$signPos];
    }
    
    private function longitudeToSign($pos)
    {
        $col=explode("°", $pos);
        $deg = $col[0];
        $signPos = $deg/30;
        return self::$starSigns[$signPos];
    }
    
    private function longitudeInSign($pos)
    {
        $col=explode("°", $pos);
        $deg = $col[0];
        $signInPos = ($deg%30)."°".$col[1];
        return $signInPos;
    }
    
    public function calculateMatch($userID1,$userID2)
    {
        return $this->calculateMatch_v1($userID1, $userID2);
    }
    
    public function calculateMatch_v3($userID1,$userID2)
    {   
        $sign = self::$starSigns;
        $scoreSign = array(
            0=> 5,
            2=> 3,
            4=> 3,
            6=> -3,
            3=> -5,
        );
        
        $scoreSign = array(
            0=> 10,
            2=> 5,
            4=> 8,
            6=> 2,
            3=> -2,
        );
        
        $compare = array(
            array('sun','sun','personality'), 
            array('moon','moon','emotions'), 
            array('as','as','emotions'), 
            array('sun','moon','emotions'), 
            array('moon','sun','emotions'), 
            array('sun','as','personality'), 
            array('as','sun','personality'), 
            array('as','moon','emotions'), 
            array('moon','as','emotions'), 
            array('sun','mars','love'), 
            array('venus','sun','love'), 
        );
        
        
        $userData1 = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($userID1);
        $userData2 = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($userID2);
        
        if ($userData1->sex == 'female' && $userData2->sex == 'male')
        {
            $tmp = $userData1;
            $userData1 = $userData2;
            $userData2 = $tmp;
        }
        
        $user1 = json_decode($userData1->raw_data,true);
        $data1 = $user1['planet'];
        
        $user2 = json_decode($userData2->raw_data,true);
        $data2 = $user2['planet'];
        
        $data1['as']=$user1['other']['ascendant'];
        $data1['as']['name']='as';
        $data2['as']=$user2['other']['ascendant'];
        $data2['as']['name']='as';
        
        
        $score = [];
        
        foreach($compare as $entry)
        {
            $p1 = $data1[$entry[0]];
            $p2 = $data2[$entry[1]];
            $s1 = $p1['sign'];
            $s2 = $p2['sign'];
            $category = $entry[2];

            $signRel = abs(array_search($s1, $sign)-array_search($s2, $sign));

            if(array_key_exists($signRel, $scoreSign))
            {
                $scoreLine = array(
                    'planet1' => $p1['name'],
                    'planet2' => $p2['name'],
                    'sign1' => $s1,
                    'sign2' => $s2,
                    'category' => $category,
                    'match_info' => $s1.', '.$s2,
                    'score'=>$scoreSign[$signRel],
                    'total'=>$scoreSign[$signRel]
                );
                $score[]=$scoreLine;
            }            
        }
        
        return $score;
    }
    
    public function calculateMatch_v2($userID1,$userID2)
    {   
        $sign = self::$starSigns;
        $scoreSign = array(
            0=> 5,
            2=> 3,
            4=> 3,
            6=> -3,
            3=> -5,
        );
        
        $governators = array(
            'aries' =>	array('mars'),
            'taurus' =>	array('venus'),
            'gemini' =>	array('mercury'),
            'cancer' =>	array('moon'),
            'leo' =>	array('sun'),
            'virgo' =>	array('mercury'),
            'libra' =>	array('venus'),
            'scorpio' => array('mars','pluto'),
            'sagittarius' => array('jupiter'),
            'capricorn' => array('saturn'),
            'aquarius' => array('saturn','uranus'),
            'pisces' =>	array('jupite','neptune')
        );
        
        $compare = array(
            array('sun','sun','personality'), 
            array('sun','moon','emotions'), 
            array('moon','moon','emotions'), 
            array('moon','mercury','emotions'), 
            array('mercury','mercury','mind'), 
            array('moon','venus','sexuality'), 
            array('venus','venus','sexuality'), 
            array('sun','venus','sexuality'), 
            array('mercury','venus','mind'), 
            array('sun','mars','energy'), 
            array('moon','mars','energy'), 
            array('mars','mars','energy'), 
            array('venus','mars','energy'), 
            array('venus','jupiter','sexuality'), 
            
            //23
            array('sun','uranus','personality'), 
            array('sun','neptune','personality'), 
            array('moon','uranus','emotions'), 
            array('moon','neptune','emotions'), 
            
            //24
            array('mars','uranus','energy'), 
            array('mars','neptune','energy'), 
            array('venus','uranus','sexuality'), 
            array('venus','neptune','sexuality'), 
            //25
            array('sun','pluto','personality'), 
            array('moon','pluto','emotions'), 
            
            //26
            array('venus','mars','sexuality'), 
            //27
            array('as','as','personality'),
            //31
            array('saturn','as','karma'),
            array('saturn','sun','karma'),
            array('saturn','moon','karma'),
            array('saturn','venus','karma'),
            //32
            array('saturn','as','karma'),
        
            //individual planets with custom scores
            array('sun','as','personality',array(0=>5)),
            array('moon','as','personality',array(0=>5)),
            array('mercury','as','personality',array(0=>5)),
            array('venus','as','personality',array(0=>5)),
            array('mars','as','personality',array(0=>5)),
            
            array('sun','ds','emotions',array(0=>5)),
            array('moon','ds','emotions',array(0=>5)),
            array('mercury','ds','emotions',array(0=>5)),
            array('venus','ds','emotions',array(0=>5)),
            array('mars','ds','emotions',array(0=>5)),
            
            array('sun','mc','karma',array(0=>3)),
            array('moon','mc','karma',array(0=>3)),
            array('mercury','mc','karma',array(0=>3)),
            array('venus','mc','karma',array(0=>3)),
            array('mars','mc','karma',array(0=>3)),
            
            array('sun','fc','emotions',array(0=>3)),
            array('moon','fc','emotions',array(0=>3)),
            array('mercury','fc','emotions',array(0=>3)),
            array('venus','fc','emotions',array(0=>3)),
            array('mars','fc','emotions',array(0=>3)),
                
            //36
            array('sun','nn','karma',array(0=>4,2=>3,4=>3)),
            array('moon','nn','karma',array(0=>4,2=>3,4=>3)),
            array('mercury','nn','karma',array(0=>4,2=>3,4=>3)),
            array('venus','nn','karma',array(0=>4,2=>3,4=>3)),
            array('mars','nn','karma',array(0=>4,2=>3,4=>3)),
            array('as','nn','karma',array(0=>4,2=>3,4=>3)),
            
            //37
            array('sun','ns','karma',array(0=>-4,3=>-2)),
            array('moon','ns','karma',array(0=>-4,3=>-2)),
            array('mercury','ns','karma',array(0=>-4,3=>-2)),
            array('venus','ns','karma',array(0=>-4,3=>-2)),
            array('mars','ns','karma',array(0=>-4,3=>-2)),
            array('as','ns','karma',array(0=>-4,3=>-2)),
            
            //38
            array('sun','vertex','personality',array(0=>4,6=>4)),
            array('moon','vertex','personality',array(0=>4,6=>4)),
            array('mercury','vertex','personality',array(0=>4,6=>4)),
            array('venus','vertex','personality',array(0=>4,6=>4)),
            array('mars','vertex','personality',array(0=>4,6=>4)),
            array('as','vertex','personality',array(0=>4,6=>4)),
            array('mc','vertex','personality',array(0=>4,6=>4)),
            
            //39
            array('sun','lilith','sexuality',array(0=>4)),
            array('moon','lilith','sexuality',array(0=>4)),
            array('mercury','lilith','sexuality',array(0=>4)),
            array('venus','lilith','sexuality',array(0=>4)),
            array('mars','lilith','sexuality',array(0=>4)),
        );  
        
        
        $govs = array(
            //28-29-30
            array('as','as','personality'), 
            array('ds','ds','emotions'), 
            array('as','ds','personality'), 
            // indi planet on midheaven is helping his career
            // for spirituality jupiter an midheaven and north node with ind planet mostly conjunction
        );
        
        
        
        $userData1 = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($userID1);
        $userData2 = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($userID2);
        
        $user1 = json_decode($userData1->raw_data,true);
        $data1 = $user1['planet'];
        
        $user2 = json_decode($userData2->raw_data,true);
        $data2 = $user2['planet'];
        
        $data1['as']=$user1['other']['ascendant'];
        $data1['as']['name']='as';
        $data2['as']=$user2['other']['ascendant'];
        $data2['as']['name']='as';
        
        $data1['vertex']=$user1['other']['vertex'];
        $data2['vertex']=$user2['other']['vertex'];
        
        $data1['ds']=$data1['as'];
        $data1['ds']['name'] = 'ds';
        $data1['ds']['longitude_dec']=($data1['ds']['longitude_dec']+180)%360;
        $data1['ds']['sign'] = $this->longitudeToSign($data1['ds']['longitude_dec']);
        
        $data1['mc']=$data1['as'];
        $data1['mc']['name'] = 'mc';
        $data1['mc']['longitude_dec']=($data1['mc']['longitude_dec']+270)%360;
        $data1['mc']['sign'] = $this->longitudeToSign($data1['mc']['longitude_dec']);
        
        $data1['fc']=$data1['as'];
        $data1['fc']['name'] = 'fc';
        $data1['fc']['longitude_dec']=($data1['fc']['longitude_dec']+90)%360;
        $data1['fc']['sign'] = $this->longitudeToSign($data1['fc']['longitude_dec']);
        
        $data2['ds']=$data2['as'];
        $data2['ds']['name'] = 'ds';
        $data2['ds']['longitude_dec']=($data2['ds']['longitude_dec']+180)%360;
        $data2['ds']['sign'] = $this->longitudeToSign($data2['ds']['longitude_dec']);
        
        $data2['mc']=$data2['ac'];
        $data2['mc']['name'] = 'mc';
        $data2['mc']['longitude_dec']=($data2['mc']['longitude_dec']+270)%360;
        $data2['mc']['sign'] = $this->longitudeToSign($data2['mc']['longitude_dec']);
        
        $data2['fc']=$data2['ac'];
        $data2['fc']['name'] = 'fc';
        $data2['fc']['longitude_dec']=($data2['fc']['longitude_dec']+90)%360;
        $data2['fc']['sign'] = $this->longitudeToSign($data2['fc']['longitude_dec']);
        //print_r($data1['north node']);
        $data1['nn'] = $data1['north node'];
        $data2['nn'] = $data2['north node'];
        
        $data1['ns'] = $data1['nn'];
        $data1['ns']['name'] = 'ns';
        $data1['ns']['longitude_dec']=($data1['ns']['longitude_dec']+180)%360;
        $data1['ns']['sign'] = $this->longitudeToSign($data1['ns']['longitude_dec']);
        
        $data2['ns'] = $data2['nn'];
        $data2['ns']['name'] = 'ns';
        $data2['ns']['longitude_dec']=($data2['ns']['longitude_dec']+180)%360;
        $data2['ns']['sign'] = $this->longitudeToSign($data2['ns']['longitude_dec']);
        
        $score = [];
        
        foreach($compare as $entry)
        {
            $c = array(array($entry[0],$entry[1]));
            if ($entry[0]!=$entry[1])
            {
                $c[]=array($entry[1],$entry[0]);
            }
            
            foreach($c as $round)
            {
                $p1 = $data1[$round[0]];
                $p2 = $data2[$round[1]];
                $s1 = $p1['sign'];
                $s2 = $p2['sign'];
                $category = $entry[2];
                
                if ($s1 == '')
                {
                    print_r($round);
                    die();
                }
                
                $signRel = abs(array_search($s1, $sign)-array_search($s2, $sign));
                
                if(array_key_exists($signRel, $scoreSign))
                {
                    $scoreLine = array(
                        'planet1' => $p1['name'],
                        'planet2' => $p2['name'],
                        'sign1' => $s1,
                        'sign2' => $s2,
                        'category' => $category,
                        'match_info' => $s1.', '.$s2,
                        'score'=>$scoreSign[$signRel],
                        'total'=>$scoreSign[$signRel]
                    );
                    $score[]=$scoreLine;
                }
            }
            
        }
        
        return $score;
    }
    
    public function calculateMatch_v1($userID1,$userID2)
    {   
        $ratio = [
            "aspect"=>1,
            "sign"=>0.8,
            "element"=>0.4
        ];
        
        
        
        $aspects = [
            0=> 5,
            60=> 3,
            120=> 4,
            180=> 2,
            90=> -2,
        ];
        
        $aspectSign = array(
            0=> 5,
            2=> 3,
            4=> 4,
            6=> 2,
            3=> -2,
        );
        
        $aspectsText = [
            0=> "Conjunction",
            60=> "Sextile",
            120=> "Trine",
            180=> "Opposition",
            90=> "Square"
        ];
        
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
        
        $elements = [
            ["earth","earth",5],
            ["water","water",5],
            ["fire","fire",5],
            ["air","air",5],
            
            ["air","earth",-2],
            ["fire","earth",-2],
            ["fire","water",-2],
            
            ["water","air",3],
            ["fire","air",3],
            ["water","earth",3]
        ];
        
        $compare = [
            ['moon','moon',10,'love'], 
            ['sun','moon',8,'love'], 
            ['sun','mars',8,'sex'], 
            ['venus','sun',8,'sex'], 
            
            
            ['sun','sun',7,'friend'], 
            ['asc','asc',5,'love'], 
            
            ['sun','asc',3,'friend'], 
            ['asc','sun',3,'friend'], 
            
            ['moon','asc',3,'other'], //can help to develop intuition, female moon though, help him to rise into personql evolution
            ['asc','moon',3,'other'], // so call it personal evolution
            
            ['moon','sun',3,'love'], 
            ['venus','mars',3,'sex'], 
            ['mars','venus',3,'sex'] 
            // indi planet on midheaven is helping his career
            // for spirituality jupiter an midheaven and north node with ind planet mostly conjunction
        ];
        
        $userData1 = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($userID1);
        $userData2 = LOVEMATCH_BOL_UserdataDao::getInstance()->findByUserId($userID2);
        
        if ($userData1->sex == 'female' && $userData2->sex == 'male')
        {
            $tmp = $userData1;
            $userData1 = $userData2;
            $userData2 = $tmp;
        }
        
        $tollerance = 5;
        $score = [];
        
        $maleData = json_decode($userData1->raw_data,true);
        $male = $maleData['planet'];
        $male['asc']=$maleData['other']['ascendant'];
        $male['desc']=$male['asc'];
        $male['desc']['name'] = 'descendant';
        $male['desc']['longitude_dec']=($male['desc']['longitude_dec']+180)%360;
        
        $femaleData = json_decode($userData2->raw_data,true);
        $female = $femaleData['planet'];
        $female['asc']=$femaleData['other']['ascendant'];
        $female['desc']=$female['asc'];
        $female['desc']['name'] = 'descendant';
        $female['desc']['longitude_dec']=($female['desc']['longitude_dec']+180)%360;
        
        foreach($compare as $entry)
        {
            
            $p1 = $male[$entry[0]];
            $p2 = $female[$entry[1]];
            $factor = $entry[2];
            $category = $entry[3];
            
            $scoreLine = [
                'planet1' => $p1['name'],
                'planet2' => $p2['name'],
                'sign1' => $p1['sign'],
                'sign2' => $p2['sign'],
                'factor' => $factor,
                'category' => $category,
            ];
            
            //Aspects
            $scoreLine['aspect'] = null;
            $a1 = $p1['longitude_dec'];
            $a2 = $p2['longitude_dec'];
            foreach($aspects as $aspect => $score_aspect)
            {
                $angle = abs($a1 - $a2);
                if(( $angle < $aspect + $tollerance ) && ( $angle > $aspect - $tollerance ))
                {
                    $scoreLine['aspect']=$aspect;
                    $scoreLine['aspect_text']=$aspectsText[$aspect];
                    $scoreLine['score_aspect']=$score_aspect;
                }
            }
            
            //Same sign
            
            $signRel = abs(array_search($p1['sign'], self::$starSigns)-array_search($p2['sign'], self::$starSigns));
            $scoreLine['sign_aspect'] = false;
            if(array_key_exists($signRel, $aspectSign))  
            {
                 $scoreLine['sign_aspect'] = true;
                 $scoreLine['sign_score'] = $aspectSign[$signRel];
            }
            
            //Element
            foreach($elements as $element)
            {
                $e1 = $elementSign[$p1['sign']];
                $e2 = $elementSign[$p2['sign']];
                if (( $e1 == $element[0] && $e2 == $element[1] ) || ( $e1 == $element[1] && $e2 == $element[0] ))
                {
                    $scoreLine['element1'] = $e1;
                    $scoreLine['element2'] = $e2;
                    $scoreLine['score_element']= $element[2];
                }
            }
            
            if (!is_null($scoreLine['aspect']))
            {
                $scoreLine['match']='aspect';
                $scoreLine['match_info']=$scoreLine['aspect_text'];
                $total = $scoreLine['score_aspect']*$scoreLine['factor']*$ratio['aspect'];
            }
            elseif ($scoreLine['sign_aspect'] === true)
            {
                $scoreLine['match']='sign compatibility';
                $scoreLine['match_info']= $p1['sign'] .' <-> '. $p2['sign'];
                
                $total = $scoreLine['sign_score']*$scoreLine['factor']*$ratio['sign'];
            }
            else
            {
                $scoreLine['match']='element';
                $scoreLine['match_info']= $scoreLine['element1'].' <-> '.$scoreLine['element2'];
                $total = $scoreLine['score_element']*$scoreLine['factor']*$ratio['element'];
            }

            $scoreLine['total'] = ceil($total);
            
            
            $score[]=$scoreLine;
        }
        
        return $score;
    }
    
    
    public function calculateAspects($data1,$data2=null)
    {
        $sameData = false;
        if (is_null($data2))
        {
            $data2 = $data1;
            $sameData = true;
        }
        
        $aspect = array();
        $aspectAngles = array(0,120,60,90,180);
        $orbSize = 5;
        $keys1 = array_keys($data1);
        for($i = 0; $i < count($data1); $i++)
        {
            $startFrom = ($sameData?$i+1:0);
            $keys2 = array_keys($data2);
            for($k = $startFrom; $k < count($data2); $k++)
            {
                $item1 = $data1[$keys1[$i]];
                $item2 = $data2[$keys2[$k]];
                $pos1 = $item1['longitude_dec'];
                $pos2 = $item2['longitude_dec'];
                $diff = abs($pos1 - $pos2);
                foreach($aspectAngles as $angle)
                {
                    $min = $angle - $orbSize;
                    $max = $angle + $orbSize;
                    if( $diff > $min && $diff < $max )
                    {
                        $aspect[] = array(
                            'angle' => $angle,
                            'diff' => $diff,
                            'item1' => $item1,
                            'item2' => $item2
                        ); 
                    }
                }
                
            } 
        }
        
        return $aspect;
    }
    
    public function calculateMidpoint($data1,$data2)
    {
        $midpoints = array();
        
        unset($data1['aspect']);
        unset($data2['aspect']);
        
        foreach($data1 as $catName=>$cat)
        {
            if(array_key_exists($catName, $data2) )
            {
                foreach($cat as $name1=>$item1)
                {
                    if(array_key_exists($name1, $data2[$catName]) )
                    {
                        $item2 = $data2[$catName][$name1];
                        $deg = ($item1['longitude_dec']+$item2['longitude_dec'])/2;
                        $midpoint = array(
                            'name' => $name1,
                            'longitude_dec' => $deg,
                            'sign' => $this->longitudeToSign($deg)
                        );
                        $midpoints[$catName][$name1]=$midpoint;   
                    }
                }
            }
        }
        
        $aspectItems = $midpoints['planet'];
        $aspectItems['ascendant'] = $midpoints['other']['ascendant'];
        
        $midpoints['aspect'] = $this->calculateAspects($aspectItems);
        
//        echo '<pre>';
//        print_r($midpoints);
//        echo '</pre>';
        
        
        return $midpoints;
    }

    
}




?>