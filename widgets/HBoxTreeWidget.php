<?php

namespace Hawk\Plugins\HBox;

/**
 * This widget displays the folder structure accessible for the current user
 */
class HBoxTreeWidget extends Widget {
    /**
     * Display the folder structure
     * @returns string The HTML result
     */
    public function display() {
        $this->addCss($this->getPlugin()->getCssUrl('tree-widget.less'));

        return View::make($this->getPlugin()->getView('tree-widget.tpl'));
    }
}