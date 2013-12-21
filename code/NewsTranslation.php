<?php

    class NewsTranslation extends DataObject{

        static $db = array(
            'Title' => 'Varchar(255)',
            'Content' => 'HTMLText',
            'Locale' => 'DBLocale',
            'URLSegment' => 'Varchar(255)',
            "MetaTitle" => "Varchar(255)",
            "MetaDescription" => "Text",
            "MetaKeywords" => "Varchar(255)",
            'SEOBody'=>'HTMLText',
            'SEOFooter'=>'HTMLText'
        );

        static $has_one = array(
            "News" => "News"
        );

        public static $summary_fields = array(
            'Title' => 'Nome',
            'valueLocale' => 'Lingua',
            'URLSegment' => 'URL'
        );

        public static $indexes = array(
            "URLSegment" => true
        );

        function getCMSFields(){
            $fields = parent::getCMSFields();

            $fieldLang = new DropdownField(
                'Locale',
                'Lingua',
                $this->Locales(),
                '',
                null,
                'Seleziona una lingua'
            );

            $fields->addFieldToTab('Root.Main', $fieldLang);

            if( $this->exists() ){
                $fields->addFieldToTab('Root.Main', new HtmlEditorField('Content','Content'));

                $baseLink = Controller::join_links (
                    Director::absoluteBaseURL()
                );
                $url = (strlen($baseLink) > 36) ? "..." .substr($baseLink, -32) : $baseLink;
                $urlsegment = new NewsURLSegmentField("URLSegment", $this->fieldLabel('URLSegment'));
                $urlsegment->setURLPrefix($url);

                $helpText = $this->fieldLabel('LinkChangeNote');

                if(!URLSegmentFilter::$default_allow_multibyte) {
                    $helpText .= $helpText ? '<br />' : '';
                    $helpText .= _t('SiteTreeURLSegmentField.HelpChars', ' Special characters are automatically converted or removed.');
                }
                $urlsegment->setHelpText($helpText);

                $fields->addFieldToTab('Root.Metadata', new TextField("MetaTitle", "Meta title"));
                $fields->addFieldToTab('Root.Main', $urlsegment, 'Content');
                $fields->addFieldToTab('Root.Metadata', new TextareaField("MetaKeywords", "Meta keywords"));
                $fields->addFieldToTab('Root.Metadata', new TextareaField("MetaDescription", "Meta description"));

                $fields->addFieldsToTab("Root.Metadata", new TextareaField("SEOBody"));
                $fields->addFieldsToTab("Root.Metadata", new TextareaField("SEOFooter"));
            }else{
                $fields->removeByName("SEOFooter");
                $fields->removeByName("SEOBody");
                $fields->removeByName("MetaDescription");
                $fields->removeByName("MetaKeywords");

                $fields->removeByName("URLSegment");
                $fields->removeByName("MetaTitle");
                $fields->removeByName("Content");

            }

            $fields->add(new HiddenField("IDNT", "ID", $this->ID));
            $fields->removeByName("NewsID");

            return $fields;
        }

        public function onBeforeWrite(){
            parent::onBeforeWrite();
            if((!$this->URLSegment || $this->URLSegment == 'new-news') && $this->Title) {
                $this->URLSegment = $this->generateURLSegment($this->Title);
            } else if($this->isChanged('URLSegment', 2)) {
                $filter = URLSegmentFilter::create();
                $this->URLSegment = $filter->filter($this->URLSegment);
                if(!$this->URLSegment) $this->URLSegment = "news-$this->ID";
            }
            $count = 2;
            while(!$this->validURLSegment()) {
                $this->URLSegment = preg_replace('/-[0-9]+$/', null, $this->URLSegment) . '-' . $count;
                $count++;
            }
        }

        function Locales() {
            $languages = Translatable::get_allowed_locales();
            $traslateLang = array();
            foreach($languages as $lang => $label){
                $traslateLang[$label] = i18n::get_locale_name($label);
            }
            return $traslateLang;
        }

        public function valueLocale() {
            if( $this->Locale ){
                return i18n::get_locale_name($this->Locale);
            }else{
                return "lingua non selezionata";
            }
        }

        public function LangMetaTitle(){
            $t = "";
            if($this->MetaTitle)
                $t .= $this->MetaTitle;
            else
                $t .= $this->Title;

            $category = $this->Product()->Category();
            if( $category )
                $t .= " - ".$category->LangContent()->Title;
            return $t;
        }

        public function LinkOrCurrent($action = null) {
            return ( Director::urlParam("ID") == $this->ID) ? 'current' : 'link';
        }

        public function Link(){
            if($this->Locale){
                $locale = explode("_", strtolower($this->Locale));
                return Controller::curr()->Link("view/{$locale[1]}/$this->URLSegment");
            }
        }

        public function cutDescription($c = "Content", $l = 200) {
            $value = strip_tags($this->$c,'');
            $length=$l;
            if($value != ''){
                if (strlen($value)>$length) {
                    $pos = strpos($value, ' ', $length-3);
                    if ($pos) $value = substr($value, 0, $pos);
                }

                if ($value[strlen($value)-1]=='.') $value = substr($value, 0, strlen($value)-1);
                return $value;
            }
        }

        public function MetaTags() {
            $tags = "";
            $tags .= "<title>" . Convert::raw2xml(($this->MetaTitle)
                ? $this->MetaTitle
                : $this->Title) . "</title>\n";

            $charset = ContentNegotiator::get_encoding();
            $tags .= "<meta http-equiv=\"Content-type\" content=\"text/html; charset=$charset\" />\n";
            if($this->MetaKeywords != "") {
                $tags .= "<meta name=\"keywords\" content=\"" . Convert::raw2att($this->MetaKeywords) . "\" />\n";
            }
            if($this->MetaDescription != "") {
                $tags .= "<meta name=\"description\" content=\"" . Convert::raw2att($this->MetaDescription) . "\" />\n";
            }
            else{
                $tags .= "<meta name=\"description\" content=\"" . $this->cutDescription("Content",200) . "\" />\n";
            }
            return $tags;
        }

        public function generateURLSegment($title){
            $filter = URLSegmentFilter::create();
            $t = $filter->filter($title);
            if(!$t || $t == '-' || $t == '-1') $t = "product-$this->ID";
            $this->extend('updateURLSegment', $t, $title);
            return $t;
        }

        public function validURLSegment() {
            $existingPage = ProductTranslation::get()
                ->filter(array(
                        "URLSegment" => $this->URLSegment,
                        "Locale" => $this->Locale
                    )
                )->exclude(
                    array("ID" => $this->ID)
                )->First();

            if ($existingPage) {
                return false;
            }
            $votes = $this->extend('augmentValidURLSegment');
            if($votes) {
                return min($votes);
            }
            return true;
        }

    }