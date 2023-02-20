<?php

class LOVEMATCH_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    
    public function getMenu()
    {
        $items = array();
        
        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('lovematch', 'btn_settings_index'));
        $item->setIconClass('ow_ic_edit');
        $item->setKey('lovematch_index');
        $item->setUrl(OW::getRouter()->urlForRoute('lovematch.admin.index'));
        $item->setOrder(1);
        $items[] = $item;
        
        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('lovematch', 'btn_settings_calculate'));
        $item->setIconClass('ow_ic_edit');
        $item->setKey('lovematch_calc');
        $item->setUrl(OW::getRouter()->urlForRoute('lovematch.admin.calculateEverything'));
        $item->setOrder(2);
        $items[] = $item;
        
        $item = new BASE_MenuItem();
        $item->setLabel(OW::getLanguage()->text('lovematch', 'btn_settings_questions'));
        $item->setIconClass('ow_ic_files');
        $item->setKey('lovematch_question');
        $item->setUrl(OW::getRouter()->urlForRoute('lovematch.admin.questionlist'));
        $item->setOrder(3);
        $items[] = $item;
        
        return new BASE_CMP_ContentMenu($items);
    }
    
    public function index()
    {
        $this->addComponent('menu', $this->getMenu());
        
    }


    public function calculateeverything()
    {   
        LOVEMATCH_BOL_UsermatchDao::getInstance()->deleteAll();
        $matchCount = LOVEMATCH_BOL_UsermatchDao::getInstance()->calculateMatchBulk();
        
        $this->addComponent('menu', $this->getMenu());
        $this->assign('matchCount', $matchCount);
        //echo '</pre>';
    }
    
    public function questionlist()
    { 
        //We list all the questions
         $questionList = BOL_QuestionService::getInstance()->findAllQuestions();
         $questionsName = array();
         for($i=0; $i<count($questionList); $i++)
         { 
            array_push($questionsName, $questionList[$i]->name);
         }
         
         //We get the questions text
         $questionNameList = array();
         for($i=0; $i<count($questionsName); $i++)
         {
            $questionName = OW::getLanguage()->text( 'base', 'questions_question_' . $questionsName[$i] . '_label');
            array_push($questionNameList, $questionName );
         }
         print_r($questionNameList);
        
        //We create the form
//        private function text( $prefix, $key, array $vars = null )
//        {
//          return OW::getLanguage()->text($prefix, $key, $vars);
//        }
        $form = new Form('question_list');
        $this->addForm($form);

        $fieldQuestion = new Selectbox('Question');
        for ($i=0; $i< count($questionNameList);$i++ )
        {      
          $fieldQuestion->addOption($questionNameList[$i], $questionNameList[$i]);
        }
        $fieldQuestion->setRequired();
        $fieldQuestion->setLabel('Question');
        $fieldQuestion->setHasInvitation(false);
        $form->addElement($fieldQuestion);

        $this->addForm($form);
        
        
        $this->addComponent('menu', $this->getMenu());
        $this->assign('questionNameList',$questionNameList);
         
    }

}
?>
