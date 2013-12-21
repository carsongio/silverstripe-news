<?php

    class NewsAdmin extends ModelAdmin{
        public static $managed_models = array('News');
        static $url_segment = 'news';
        static $menu_title = 'News';

        public function getList() {
            $list = parent::getList();
            if(Session::get('SubsiteID') != 0)
                $list->where(array('SubsiteID', Session::get('SubsiteID')));
            else{

            }

            return $list;
        }
    }