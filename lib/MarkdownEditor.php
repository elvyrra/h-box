<?php

namespace Hawk\Plugins\HBox;

use Hawk\Plugins\HWidgets as HWidgets;

class MarkdownEditor extends Editor {
    public static function display() {
    	$input = new HWidgets\MarkdownInput(array(
    		'id' => 'hbox-md-editor',
    		'attributes' => array(
    			'e-value' => 'data',
    			'e-on' => '{
    				input : function() { saved = false; },
    				keydown: function(self,event) {
						if(event.ctrlKey && event.key === "s") {
							event.preventDefault();
							$root.save.call($root, self);
							return false;
						}
    				}
    			}'
    		)
    	));

        return View::make(Plugin::current()->getView('editors/markdown.tpl'), array(
        	'input' => $input->display()
        ));
    }
}