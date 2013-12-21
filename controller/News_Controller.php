<?php

    class NewsPage extends Page{

    }

    class NewsPage_Controller extends Page_Controller{

        public static $allowed_actions = array("view");

        function init(){
            parent::init();
        }

        public function index(){

            $data = array(

            );

            return $this->customise($data)->renderWith(array("NewsPage", "Page"));
        }

        public function view(SS_HTTPRequest $request){
            $params = $request->allParams();
            $news = News::get()
                ->leftJoin("NewsTranslation", "News.ID = NewsID")
                ->filter(
                    array(
                        "Locale" => Translatable::get_current_locale(),
                        "URLSegment" => $params["OtherID"]
                    )
                )->first();

            if(!$news)
                $this->httpError("404");

            $data = array(
                "News" => $news,
                "MetaTags" => $news->getTranslation()->MetaTags()
            );

            return $this->customise($data);
        }

        public function PaginatedNews(){
            $news = News::get()
                ->leftJoin("NewsTranslation","News.ID = NewsID")
                ->filter(array("Locale" => Translatable::get_current_locale()));
            $pages = new PaginatedList($news, $this->request);
            return $pages->setPageLength(2);
        }

        public function getLocaleFromURL($locale = null){
            if($locale){
                $langs = Translatable::get_allowed_locales();
                if($langs)foreach($langs as $lang){
                    $l = explode("_",$lang);
                    if($locale == strtolower($l[1]))
                        return $lang;
                }
            }
            return Translatable::default_locale();
        }
    }