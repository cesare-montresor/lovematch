<?php

class LOVEMATCH_BOL_Question 
{
    
    /**
     * Singleton instance.
     *
     * @var LOVEMATCH_BOL_Service
     */
    private static $classInstance;
    private $sectionName = 'Lovematch';
    public $questions = array(
        array('name'=>'sun','label'=>'Sun','description'=>'Choose your Sun sign'),
        array('name'=>'ascendant','label'=>'Ascendant','description'=>'Choose your Ascendant'),
        array('name'=>'moon','label'=>'Moon','description'=>'Choose your Moon sign'),
        array('name'=>'mars','label'=>'Mars','description'=>'Choose your Mars sign'),
        array('name'=>'venus','label'=>'Venus','description'=>'Choose your Venus sign'),
    );
    public $starSigns = array('aries','taurus','gemini', 'cancer','leo','virgo','libra',
                        'scorpio','sagittarius','capricorn','aquarius','pisces');
 
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
    
    
    public function getQuestions()
    {
        return $this->questions;
    }
    
    public function addQuestionAstrology()
    {
        $section =  new BOL_QuestionSection();
        $section->name = $this->sectionName;
        $section->sortOrder = BOL_QuestionService::getInstance()->findLastSectionOrder() + 1;

        BOL_QuestionService::getInstance()->saveOrUpdateSection($section);

        $question = new BOL_Question();
        $question->removable = 0;
        $question->presentation = BOL_QuestionService::QUESTION_PRESENTATION_TEXT;
        $question->type = BOL_QuestionService::QUESTION_VALUE_TYPE_TEXT;
        $question->onEdit = 0;
        $question->onJoin = 0;
        $question->onSearch = 0;
        $question->onView = 0;
        $question->sectionName = $this->sectionName;
        $question->name = 'lovematch_time';
        $question->sortOrder = 0;
        $question->parent = '';
        BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);
        
        $question = new BOL_Question();
        $question->removable = 0;
        $question->presentation = BOL_QuestionService::QUESTION_PRESENTATION_TEXT;
        $question->type = BOL_QuestionService::QUESTION_VALUE_TYPE_TEXT;
        $question->onEdit = 0;
        $question->onJoin = 0;
        $question->onSearch = 0;
        $question->onView = 0;
        $question->sectionName = $this->sectionName;
        $question->name = 'lovematch_location';
        $question->sortOrder = 0;
        $question->parent = '';
        BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);
        

        // -- add location question to all account types
        $accountTypeList = BOL_QuestionService::getInstance()->findAllAccountTypes();

        $list = array();

        foreach( $accountTypeList as $accauntType )
        {
            /* @var $accauntType BOL_QuestionAccountType */
            $list[$accauntType->name] = $accauntType->name;
        }
        
        BOL_QuestionService::getInstance()->addQuestionListToAccountTypeList(array('lovematch_time','lovematch_location'), $list);
    }
    
    public function removeQuestionAstrology(){
        $questionService = BOL_QuestionService::getInstance();
        $questionService->deleteSection($this->sectionName);
        $questionList = $questionService->findQuestionByNameList(array('lovematch_time','lovematch_location'));
        $ids = array();
        foreach ($questionList as $question) {
            $ids[] = $question->id;
        }
        $questionService->deleteQuestion($ids);
    }
    
    public function showQuestionAstrology()
    {
        $questionService = BOL_QuestionService::getInstance();
        $questions = $questionService->findQuestionByNameList(array('lovematch_time','lovematch_location'));
        
        foreach ($questions as $question) {
            $question->onEdit = 1;
            $question->onJoin = 1;
            $question->onSearch = 1;
            $question->onView = 1;
            $questionService->saveOrUpdateQuestion($question);
        }
    }
    
    public function hideQuestionAstrology()
    {
        $questionService = BOL_QuestionService::getInstance();
        $questions = $questionService->findQuestionByNameList(array('lovematch_time','lovematch_location'));
        
        foreach ($questions as $question) {
            $question->onEdit = 0;
            $question->onJoin = 0;
            $question->onSearch = 0;
            $question->onView = 0;
            $questionService->saveOrUpdateQuestion($question);
        }
    }
    

    public function addquestion($sectionName, $questionName, $label, $description, $values){
            
        $allQuestions = BOL_QuestionService::getInstance()->findAllQuestions();
        $allQuestionsName = array();
        $allQuestionsId = array();
        for ($i=0; $i < count($allQuestions); $i++){
            array_push($allQuestionsName, $allQuestions[$i]->name);
            array_push($allQuestionsId, $allQuestions[$i]->id);
        }

        if (!in_array($questionName, $allQuestionsName))
        {
            $question = new BOL_Question();
            $question->name = $questionName;
            $question->sectionName = $sectionName;
            $question->type = BOL_QuestionService::QUESTION_VALUE_TYPE_SELECT;
            $question->presentation = BOL_QuestionService::QUESTION_PRESENTATION_SELECT;
            $question->onEdit = 0;
            $question->onJoin = 0;
            $question->onSearch = 0;
            $question->onView = 0;
            $question->sortOrder = ( (int) BOL_QuestionService::getInstance()->findLastQuestionOrder($question->sectionName) ) + 1;

            BOL_QuestionService::getInstance()->createQuestion($question, $label, $description, $values);
            BOL_LanguageService::getInstance()->addOrUpdateValue(OW::getLanguage()->getCurrentId(),
                     'base', 'questions_question_' . ($question->name ) . '_label', htmlspecialchars($question->name));
            $allAccountTypes = BOL_QuestionService::getInstance()->findAllAccountTypesWithLabels();
            
            print_r($allAccountTypes);
            BOL_QuestionService::getInstance()->addQuestionToAccountType($question->name, array_keys($allAccountTypes));
           
            return $question->id;
        }
        else {
            $i = array_search($questionName, $allQuestionsName);
            return $allQuestionsId[$i];

        }      
    }
    
    
    public function removequestion($array){
        BOL_QuestionService::getInstance()->deleteQuestion($array);
    }
    
    
    public function addsection($sectionName){
        $allSections = BOL_QuestionService::getInstance()->findAllSections();
        $allSectionsName = array();
        for ($i=0; $i < count($allSections); $i++){
            array_push($allSectionsName, $allSections[$i]->name);
        }
        if(!in_array($sectionName, $allSectionsName)){
            $questionSection = new BOL_QuestionSection();
            //$questionSection->name = md5(uniqid());
            $questionSection->name = $sectionName;
            $questionSection->sortOrder = (BOL_QuestionService::getInstance()->findLastSectionOrder()) + 1;
            BOL_QuestionService::getInstance()->saveOrUpdateSection($questionSection);
            BOL_LanguageService::getInstance()->addOrUpdateValue(OW::getLanguage()->getCurrentId(),
                     'base', 'questions_section_' . ( $questionSection->name ) . '_label', htmlspecialchars($sectionName));
        }
    }
    
    
    public function findQuestionIdByName($name){
        $id = BOL_QuestionService::getInstance()->findQuestionByName($name)->id;
        return $id;
    }
    
    
    public function addquestionlist(){
       $this->addSection($this->sectionName);
  
       foreach($this->questions as $question){
           self::getInstance()->addquestion($this->sectionName, $question['name'],  $question['label'], $question['description'], $this->starSigns);
       }
       
    }
    
    
    public function removeQuestionList(){
        $sectionName = 'Astro';
        BOL_QuestionService::getInstance()->deleteSection($sectionName);
        $questionsName = array();
        foreach($this->questions as $question){
            $questionsName[]=$question['name'];
        }
        $questionsId = array();
        foreach ($questionsName as $value) {
            $id =  self::getInstance()->findQuestionIdByName($value);
            array_push($questionsId, $id);
        }
        
        BOL_QuestionService::getInstance()->deleteQuestion($questionsId);
    }
    
  
    public function showquestions(){
        foreach($this->questions as $questionInfo){
            $question = BOL_QuestionService::getInstance()->findQuestionByName($questionInfo['name']);
            $question->onEdit = 1;
            $question->onJoin = 1;
            $question->onSearch = 1;
            $question->onView = 1;
            BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);
        }
    }
    
    
    public function hidequestions(){
         foreach($this->questions as $questionInfo){
            
            $question = BOL_QuestionService::getInstance()->findQuestionByName($questionInfo['name']);
            if(!is_null($question)){
            $question->onEdit = 0;
            $question->onJoin = 0;
            $question->onSearch = 0;
            $question->onView = 0;
            
            BOL_QuestionService::getInstance()->saveOrUpdateQuestion($question);
            }
        }
    }
    
    
}


?>
