<?php

namespace Hawk\Plugins\HBox;

abstract class Editor {
    abstract public static function display();

    public static function save($file){}

    public function getData($file) {

    }

    public function getStaticContent($file) {

    }
}