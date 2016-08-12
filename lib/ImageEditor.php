<?php

namespace Hawk\Plugins\HBox;

class ImageEditor extends Editor {
    public static function display() {
        return View::make(Plugin::current()->getView('editors/image.tpl'));
    }
}