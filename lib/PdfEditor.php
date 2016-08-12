<?php

namespace Hawk\Plugins\HBox;

class PdfEditor extends Editor {
    public static function display() {
        return View::make(Plugin::current()->getView('editors/pdf.tpl'));
    }
}