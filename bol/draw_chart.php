<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class LOVEMATCH_BOL_DrawChart
{
    var $image;
    var $data;
    var $user;
    var $symbolFont;
    var $textFont;
    var $size = 700;
    var $color;
    
    var $sizeSign = 22;
    var $sizePlanet = 14;
    
    var $sign = array('a','s','d','f','g','h','j','k','l','v','x','c');
    var $planet = array(
        "sun"=>'Q',
        "moon"=>'W',
        "mercury"=>'E',
        "venus"=>'R',
        "mars"=>'T',
        "jupiter"=>'Y',
        "saturn"=>'U',
        "uranus"=>'I',
        "neptune"=>'O',
        "pluto"=>'„',
        "north node"=>'{',
//        "chiron"=>'M',
//        "ceres"=>'C',
//        
//        "pallas"=>'V',
//        "juno"=>'B',
//        "vesta"=>'N',
//        "lilith"=>"`",
//        "south node"=>"}",
//        "part of fortune"=>'‰'
    );
    
    
    public function __construct() 
    {
        $this->symbolFont = OW_PluginManager::getInstance()->getPlugin('lovematch')->getStaticDir().'font/HamburgSymbols.ttf';
        $this->textFont = OW_PluginManager::getInstance()->getPlugin('lovematch')->getStaticDir().'font/NoticiaText-Regular.ttf';
    }
    
    
    private function colorRGB($red,$green,$blue,$alpha = 0)
    {
        return imagecolorallocatealpha($this->image, $red, $green, $blue, $alpha);
    }
    
    private function setupColors()
    {
        $this->color = [
            "transparent" => $this->colorRGB( 255, 255, 255, 127),
            "white" => $this->colorRGB( 255, 255, 255, 0),
            "white_80" => $this->colorRGB( 255, 255, 255, 26),
            "white_50" => $this->colorRGB( 255, 255, 255, 64),
        
            "lightbrown" => $this->colorRGB( 245, 234, 218),
            "darkgray" => $this->colorRGB( 100, 100, 100),
            "white" => $this->colorRGB( 255, 255, 255),
            "pure_blue" => $this->colorRGB( 0, 0, 255),
            "blue" => $this->colorRGB( 100, 100, 255),
            "red" => $this->colorRGB( 255, 100, 100),
            
        
            "magenta" => $this->colorRGB( 252, 129, 182),
            "scarlet" => $this->colorRGB( 230, 122, 122),
            "orange" => $this->colorRGB( 237, 135, 104),
            "clay" => $this->colorRGB( 242, 212, 153),
            "yellow" => $this->colorRGB( 237, 233, 157),
            "lime" => $this->colorRGB( 195, 214, 139),
            "green" => $this->colorRGB( 156, 217, 157),
            "aqua" => $this->colorRGB( 126, 196, 174),
            "lightblue" => $this->colorRGB( 134, 204, 203),
            "indigo" => $this->colorRGB( 98, 112, 163),
            "violet" => $this->colorRGB( 157, 101, 161),
            "cerise" => $this->colorRGB( 201, 91, 135),
        ];
    }
    
    public function drawNatal($data,$size)
    {   
        $this->angleOffset = 150+$data['other']['ascendant']['longitude_dec'];
        
        $radius_outer   = $size - ($size * 0.1);
        $radius_inner   = $size - ($size * 0.3);
        $radius_house   = $size - ($size * 0.5);
        $radius_center  = $size - ($size * 0.9);
        $radius_sign    = ( $radius_outer + $radius_inner ) / 2;
        $radius_planet  = $radius_inner;
        
        $center = $size/2;
        
        $this->image = imagecreatetruecolor($size, $size);
        $this->setupColors();
        
        imagesavealpha($this->image, true);
        imagefill($this->image, 0, 0, $this->color['transparent']);
        
        
        $this->drawSignWheel($center,$radius_outer,$radius_inner);
        $this->drawHouseWheel($center,180,$radius_house,$radius_inner,$this->color["magenta"]);
        $this->drawCenterDot($center,$radius_center);
        $this->drawZodiacSimbols($center,$radius_sign,$this->sizeSign);
        $this->drawAspects($center,$radius_house,$data['aspect']);
        $this->drawPlanets($center,$radius_house,$data['planet'],$this->color["darkgray"],'out');
        $this->outputImage();
    }
    
    public function drawSynastry($data1,$data2,$size)
    {   
        $asc1 = $data1['other']['ascendant']['longitude_dec'];
        $asc2 = $data2['other']['ascendant']['longitude_dec'];
        
        $this->angleOffset = 150+$asc1;
        $ascDiff = $asc2-$asc1;
        
        $radius_outer   = $size - ($size * 0.1);
        $radius_inner   = $size - ($size * 0.3);
        $radius_house1   = $size - ($size * 0.5);
        $radius_house2   = $size - ($size * 0.7);
        $radius_center  = $size - ($size * 0.9);
        $radius_sign    = ( $radius_outer + $radius_inner ) / 2;
        $radius_planet  = $radius_inner;
        
        $center = $size/2;
        
        $this->image = imagecreatetruecolor($size, $size);
        $this->setupColors();
        
        imagesavealpha($this->image, true);
        imagefill($this->image, 0, 0, $this->color['transparent']);
        
        
        $this->drawSignWheel($center,$radius_outer,$radius_inner);
        $this->drawHouseWheel($center,0,$radius_house1,$radius_inner,$this->color["darkgray"]);
        $this->drawHouseWheel($center,$ascDiff,$radius_house2,$radius_house1,$this->color["darkgray"]);
        $this->drawCenterDot($center, $radius_center);
        $this->drawZodiacSimbols($center,$radius_sign,$this->sizeSign);
        
        $this->drawPlanets($center,$radius_house1,$data1['planet'],$this->color["red"],'in');
        $this->drawPlanets($center,$radius_house1,$data2['planet'],$this->color["blue"],'out');
        
        $aspectItems1 = $data1['planet'];
        $aspectItems1['ascendant'] = $data1['other']['ascendant'];
        
        $aspectItems2 = $data2['planet'];
        $aspectItems2['ascendant'] = $data2['other']['ascendant'];
        
        $aspects = LOVEMATCH_BOL_Astro::getInstance()->calculateAspects($aspectItems1,$aspectItems2);
        
        $this->drawAspects($center, $radius_house2, $aspects);
        
        
        $this->outputImage();
    }
    
    public function drawComposite($data1,$data2,$size)
    {   
        $data = LOVEMATCH_BOL_Astro::getInstance()->calculateMidpoint($data1,$data2);
        $data['other']['ascendant']['longitude_dec'] += 180;
        $this->drawNatal($data, $size);
    }
    
    
    private function normalizeAngle($deg,$asc=true)
    {
        $startDeg = $asc?$this->angleOffset:0;
        $normDeg = 360-$deg+$startDeg;
        return $normDeg;
    }
        
    
    private function drawSignWheel($center,$radius_max,$radius_min)
    {
        $signColors=array("magenta","scarlet","orange","clay","yellow","lime","green","aqua","lightblue","indigo","violet","cerise");
        
        imagesetthickness($this->image, 1);
        for ($i = 0; $i < 12; $i++) 
        {
            $deg = $this->normalizeAngle($i*30);
            $rad = deg2rad($deg);
        
            $x=cos($rad)*$radius_max/2+$center;
            $y=sin($rad)*$radius_max/2+$center;
        
            
            $color = $this->color[$signColors[$i]];
            imagefilledarc($this->image, $center, $center, $radius_max, $radius_max, $deg, $deg+30, $color, IMG_ARC_PIE);
            
            imageline($this->image, $center, $center, $x, $y, $this->color['darkgray']);
        }
        imageellipse($this->image, $center, $center, $radius_max, $radius_max, $this->color["darkgray"]);
        imageellipse($this->image, $center, $center, $radius_max-1, $radius_max-1, $this->color["darkgray"]);
        imagefilledellipse($this->image, $center, $center, $radius_min, $radius_min, $this->color["lightbrown"]);
        imageellipse($this->image, $center, $center, $radius_min, $radius_min, $this->color["darkgray"]);
    }
        
    private function drawHouseWheel($center,$offset,$radius_min,$radius_max,$color)
    {
        //$numbers = array("I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
        $numbers = array("I","II","III","IV","V","VI","VII","VIII","IX","X","XI","XII");
        
        
        for ($i = 0; $i < 12; $i++) 
        {
            $deg = $this->normalizeAngle($offset+$i*30,false);
            $rad = deg2rad($deg);
            
            $x1=cos($rad)*($radius_min/2-1)+$center;
            $y1=sin($rad)*($radius_min/2-1)+$center;
            
            $x2=cos($rad)*($radius_max/2-1)+$center;
            $y2=sin($rad)*($radius_max/2-1)+$center;
            
            $x3=cos(deg2rad($deg - 15))*($radius_max/2+$radius_min/2)/2+$center;
            $y3=sin(deg2rad($deg - 15))*($radius_max/2+$radius_min/2)/2+$center;
            
            if($i%3!=0)
            {
                imagesetthickness($this->image, 1);
            }
            else
            {   
                imagesetthickness($this->image, 2);
            }
            imageellipse($this->image, $center, $center, $radius_min, $radius_min, $color);
            imageline($this->image, $x1, $y1, $x2, $y2, $color);
            
            $this->drawText($numbers[$i], 16, $x3, $y3, 0, $this->color["white_80"], $this->textFont);
            
        }
        imagesetthickness($this->image, 1);
    }
    
    private function drawCenterDot($center, $radius)
    {
        imagefilledellipse($this->image, $center, $center, $radius, $radius, $this->color["lightbrown"]);
        imagefilledellipse($this->image, $center, $center, $radius, $radius, $this->color["white"]);
        imageellipse($this->image, $center, $center, $radius, $radius, $this->color["darkgray"]);
    }
    
    private function drawText($text,$size,$x,$y,$angle,$color,$fontFile)
    {   
        $box = imageftbbox($size, $angle, $fontFile, $text);
        $box_center_x = ($box[0]+$box[2]+$box[4]+$box[6])/4;
        $box_center_y = ($box[1]+$box[3]+$box[5]+$box[7])/4;

        imagefttext($this->image, $size, $angle, $x-$box_center_x, $y-$box_center_y, $color, $fontFile, $text);
    }
    
    private function drawZodiacSimbols($center,$radius,$size)
    {
        for ($i = 0; $i < 12; $i++) 
        {   
            $deg = $this->normalizeAngle($i*30);
            $rad = deg2rad($deg+15);
            
            $x=cos($rad)*$radius/2+$center;
            $y=sin($rad)*$radius/2+$center;
            
            $this->drawText($this->sign[$i], $size, $x, $y, 270-$deg-15, $this->color["white_50"], $this->symbolFont);
        }
    }
    
    private function drawPlanets($center,$radius,$planets,$color,$direction='in')
    {
        if ($direction == 'in')
        {
            $planetOffset = -20;
            $dashOffset = -5;
        }
        else if ($direction == 'out')
        {
            $planetOffset = 20;
            $dashOffset = 5;
        }
        
        $radius = $radius/2;
        
        foreach($this->planet as $name=>$symbol)
        {
            if (key_exists($name, $planets))
            {
                $deg = $this->normalizeAngle($planets[$name]['longitude_dec']-30);
                $rad = deg2rad($deg);

                $x=cos($rad)*($radius+$planetOffset)+$center;
                $y=sin($rad)*($radius+$planetOffset)+$center;
                
                $this->drawText($symbol, $this->sizePlanet, $x, $y, 0, $color, $this->symbolFont);
                
                
                
                $x1=cos($rad)*$radius+$center;
                $y1=sin($rad)*$radius+$center;

                $x2=cos($rad)*($radius+$dashOffset)+$center;
                $y2=sin($rad)*($radius+$dashOffset)+$center;

                imageline($this->image, $x1, $y1, $x2, $y2 , $color);
            }
        }
    }
    
    private function drawAspects($center,$radius,$aspects)
    {
        foreach($aspects as $aspect)
        {
            $item1 = $aspect['item1'];
            $item2 = $aspect['item2'];
            if (array_key_exists($item1['name'], $this->planet) &&
                array_key_exists($item2['name'], $this->planet) )
            {
                $angle = &$aspect['angle'];
                $pos1 = $this->normalizeAngle($item1['longitude_dec']-30);
                $pos2 = $this->normalizeAngle($item2['longitude_dec']-30);

                $rad1 = deg2rad($pos1);
                $rad2 = deg2rad($pos2);

                $x1=cos($rad1)*($radius/2)+$center;
                $y1=sin($rad1)*($radius/2)+$center;

                $x2=cos($rad2)*($radius/2)+$center;
                $y2=sin($rad2)*($radius/2)+$center;


                if ( $angle == 0 )
                {
                    imagefilledellipse($this->image, ($x1+$x2)/2, ($y1+$y2)/2, 7, 7, $this->color["pure_blue"]);
                }
                else
                {
                    if( $angle == 60 )
                    {
                        $color = $this->color['blue'];
                    }
                    else if ($angle == 120 )
                    {
                        $color = $this->color['green'];
                    }
                    else if ($angle == 90 )
                    {
                        $color = $this->color['red'];
                    }
                    else if ($angle == 180 )
                    {
                        $color = $this->color['violet'];
                    }
                    imageline($this->image, $x1, $y1, $x2, $y2, $color);
                }
            }
        }
    }    

    public function outputImage()
    {
        header('Content-type: image/png');
        imagepng($this->image);
        imagedestroy($this->image);
        exit();
    }    
}

