<?php

namespace Hawk\Plugins\HBox;

class ZipEditor extends Editor {
    public static function display() {
        return View::make(Plugin::current()->getView('editors/zip.tpl'));
    }
}