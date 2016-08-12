<?php

namespace Hawk\Plugins\HBox;

class AceEditor extends Editor {
    private static function getForm() {
        $form = new Form(array(
            'id' => 'hbox-edit-code-form',
            'method' => 'post',
            'attributes' => array(
                'ko-attr' => '{id : "hbox-edit-code-form-" + id}',
                'ko-submit' => 'function() { $root.save($data); }'
            ),
            'inputs' => array(
                new HtmlInput(array(
                    'name' => 'ace',
                    'value' => View::make(Plugin::current()->getView('editors/ace.tpl'))
                )),
            )
        ));

        return $form;
    }

    public static function display() {
        $form = self::getForm();

        return $form->display();
    }

    public static function save($file) {

    }
}