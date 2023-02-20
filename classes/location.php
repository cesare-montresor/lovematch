<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


class LOVEMATCH_CLASS_Location extends FormElement
{
    /**
     * Constructor.
     *
     * @param string $name
     */
    public function __construct( $name )
    {
        parent::__construct($name);
        
        $this->addAttribute('type', 'text');
        $this->addAttribute('name', $name);
        $this->addAttribute('class', '');
        $this->addAttribute('autocomplete', 'off');
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);
        $plugin = OW::getPluginManager()->getPlugin('lovematch');
        
        $googleMapApiKey = "AIzaSyDTC9SqCpE4czt0j3DDI-Z-rfthNFCA51Q";
        
        //OW::getDocument()->addScript("https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js");
//        OW::getDocument()->addScript("http://maps.googleapis.com/maps/api/js?key=".$googleMapApiKey);
        
        OW::getDocument()->addScript($plugin->getStaticJsUrl().'location_form.js');
        OW::getDocument()->addStyleSheet($plugin->getStaticCssUrl().'location_form.css');
        
        $city = isset($this->value['city'])?$this->value['city']:'';
        $lat = isset($this->value['lat'])?$this->value['lat']:'';
        $lng = isset($this->value['lng'])?$this->value['lng']:'';
        
        
        $this->attributes['value']=$city;
        
        $basename = $this->attributes['name'];
        
        $html = '';
        $attribute = $this->attributes;
        $attribute['name'] = $basename.'[city]';
        $attribute['id'] = $basename.'_city';
        $html.= UTIL_HtmlTag::generateTag('input', $attribute);
        
        
        
        $attribute = array(
            'id' => $basename.'_result',
            'style' => 'display:none');
        $html.= UTIL_HtmlTag::generateTag('div', $attribute,TRUE);
        
        $attribute = array(
            'id' => $basename.'_loading',
            'src' => $plugin->getStaticUrl().'img/ajax-loader.gif',
            'style' => 'display:none');
        $html.= UTIL_HtmlTag::generateTag('img', $attribute);
        
        $attribute = array(
            'type' => 'hidden',
            'id' => $basename.'_lng',
            'name' => $basename.'[lng]',
            //'style' => 'display:none',
            'value' => $lng);
        $html.= UTIL_HtmlTag::generateTag('input', $attribute);
        
        $attribute = array(
            'type' => 'hidden',
            'id' => $basename.'_lat',
            'name' => $basename.'[lat]',
            //'style' => 'display:none',
            'value' => $lat);
        $html.= UTIL_HtmlTag::generateTag('input', $attribute);
        
        
        
//        echo '<pre>';
//        echo debug_print_backtrace();
//        echo '</pre>';
        return $html;
        
    }
    
    public function escapeValue($value)
    {
        return $value;
    }
} 