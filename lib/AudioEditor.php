<?php

namespace Hawk\Plugins\HBox;

class AudioEditor extends Editor {
    public static function display() {
        return View::make(Plugin::current()->getView('editors/audio.tpl'));
    }
}