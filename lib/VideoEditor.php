<?php

namespace Hawk\Plugins\HBox;

class VideoEditor extends Editor {
    public static function display() {
        return View::make(Plugin::current()->getView('editors/video.tpl'));
    }
}