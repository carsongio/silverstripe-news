<?php

    class NewsImage extends Image{

        static $db = array(
            'Title'=>'Varchar(255)',
            'Content'=>'HTMLText'
        );

        static $has_one = array(
            'News'=>'News'
        );

        public function getCMSFields(){
            $fields = parent::getCMSFields();
            $fields->removeByName('Name');
            $fields->removeByName('OwnerID');
            $fields->removeByName('ParentID');

            $fields->addFieldToTab('Root.Main', new NumericField('SortOrder'));
            $fields->addFieldToTab('Root.Main', new TextField('Title', 'Titolo'));
            $fields->addFieldToTab('Root.Main', new HtmlEditorField('Content', 'Contenuto'));

            return $fields;
        }
    }