<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class LOVEMATCH_CLASS_Time extends FormElement
{
    protected $hour;
    protected $minute;
    
    
    public function __construct( $name )
    {
        parent::__construct($name);
        
        $this->addAttribute('name', $name);
        $this->addAttribute('class', '');
        $this->addAttribute('autocomplete', 'off');
    }
    
    public function renderInput( $params = null )
    {
        parent::renderInput($params);
        $plugin = OW::getPluginManager()->getPlugin('lovematch');
        
        $hour = (isset($this->value['hour'])?$this->value['hour']:'-1');
        $minute = (isset($this->value['minute'])?$this->value['minute']:'-1');
        
        $html = '';
        $optionsString = UTIL_HtmlTag::generateTag('option', array('value'=>'-1'), true, 'Hour');
        for($i=0;$i<24;$i++)
        {
            $arributes = array('value' => $i);
            if($i == $hour)
            {
                $arributes['selected']='selected';
            }
            $optionsString.= UTIL_HtmlTag::generateTag('option', $arributes, true, $i);
        }
        $attribute = array('name' => $this->attributes['name'].'[hour]');
        $html .= UTIL_HtmlTag::generateTag('select', $attribute, true, $optionsString);
        
        
        
        $optionsString = UTIL_HtmlTag::generateTag('option', array('value'=>'-1'), true, 'Minute');
        for($i=0;$i<60;$i++)
        {
            $arributes = array('value' => $i);
            if($i == $minute)
            {
                $arributes['selected']='selected';
            }
            $optionsString.= UTIL_HtmlTag::generateTag('option', $arributes, true, $i);
        }
        
        $attribute = array('name' => $this->attributes['name'].'[minute]');
        $html .= UTIL_HtmlTag::generateTag('select', $attribute, true, $optionsString);
        
        
        return $html;
    }
    
    public function escapeValue($value)
    {
        return $value;
    }
}
