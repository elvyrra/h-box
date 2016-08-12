<?php

namespace Hawk\Plugins\HBox;

class OfficeEditor extends Editor {
    public static function display() {
        return View::make(Plugin::current()->getView('editors/office.tpl'));
    }
}