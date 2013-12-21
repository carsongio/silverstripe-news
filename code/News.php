<?php

    class News extends DataObject{

        static $db = array(
            "Title" => "Varchar(255)",
            "Date" => "Date"
        );

        static $has_many = array(
            "Gallery" => "NewsImage",
            "Translations" => "NewsTranslation"
        );

        static $searchable_fields = array(
            'Title'
        );

        public function getCMSFields(){
            $fields = parent::getCMSFields();

            $date = new DateField("Date", "News date");
            $date->setConfig('showcalendar', true);
            $fields->addFieldsToTab("Root.Main", $date);

            if($this->exists()){

                $gfct = GridFieldConfig_RecordEditor::create();
                $gft = new GridField("Translations", "NewsTranslation", $this->Translations(), $gfct);
                $fields->addFieldToTab("Root.Translations", $gft);

                $gallery = new SortableUploadField("Gallery");
                $gallery->getValidator()->setAllowedExtensions(array('jpg', 'jpeg', 'png', 'gif'));
                $fields->addFieldToTab('Root.Gallery', $gallery);
            }else{
                $fields->removeByName("Gallery");
            }


            return $fields;
        }

        public function onBeforeDelete(){
            parent::onBeforeDelete();
            if($this->Translations()){
                $trans = $this->Translations();
                if($trans)foreach($trans as $tran){
                    $tran->delete();
                }
            }
        }

        public function getTranslation(){
            $locale = Translatable::get_current_locale();
            return NewsTranslation::get()->filter(array('Locale' => $locale,'NewsID' => $this->ID))->First();
        }

    }