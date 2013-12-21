<?php

class NewsURLSegmentField extends TextField {
    /**
     * @var string
     */
    protected $helpText, $urlPrefix;

    static $allowed_actions = array(
        'suggest'
    );

    public function Value() {
        return rawurldecode($this->value);
    }

    public function Field($properties = array()) {
        Requirements::javascript(NEWS_DIR . '/javascript/NewsURLSegmentField.js');
        Requirements::add_i18n_javascript(CMS_DIR . '/javascript/lang', false, true);
        Requirements::css(CMS_DIR . "/css/screen.css");
        return parent::Field($properties);
    }

    public function suggest($request) {
        if(!$request->getVar('value')) return $this->httpError(405);
        $news = $this->getNews();
        $news->URLSegment = $news->generateURLSegment($request->getVar('value'));
        $count = 2;
        while(!$news->validURLSegment()) {
            $news->URLSegment = preg_replace('/-[0-9]+$/', null, $news->URLSegment) . '-' . $count;
            $count++;
        }

        Controller::curr()->getResponse()->addHeader('Content-Type', 'application/json');
        return Convert::raw2json(array('value' => $news->URLSegment));
    }

    /**
     * @return News
     */
    public function getNews() {
        $idField = $this->getForm()->Fields()->dataFieldByName('IDNT');
        $localeField = $this->getForm()->Fields()->dataFieldByName('Locale');
        $prod = NewsTranslation::get()
            ->filter(
            array(
                "ID" => $idField->Value(),
                "Locale" => $localeField->Value()
            )
        )->First();
        return ($idField && $idField->Value()) ? $prod : singleton('NewsTranslation');
    }

    /**
     * @param string the secondary text to show
     */
    public function setHelpText($string){
        $this->helpText = $string;
    }

    /**
     * @return string the secondary text to show in the template
     */
    public function getHelpText(){
        return $this->helpText;

    }

    /**
     * @param the url that prefixes the page url segment field
     */
    public function setURLPrefix($url){
        $this->urlPrefix = $url;
    }

    /**
     * @return the url prefixes the page url segment field to show in template
     */
    public function getURLPrefix(){
        return $this->urlPrefix;
    }


    public function Type() {
        return 'text urlsegment';
    }

}
