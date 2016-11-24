<?php

namespace Hawk\Plugins\HBox;

class AceEditor extends Editor {
    public static function display() {
        return View::make(Plugin::current()->getView('editors/ace.tpl'));
    }
}