<?php

namespace Hawk\Plugins\HBox;

class EditorController extends Controller {
    /**
     * Display a file editor / displayer
     * @returns string The HTML result
     */
    public function displayEditor($type) {
        $method = 'display' . ucfirst($type);

        return $this->$method();
    }

    public function save() {}


    public function displayImage() {
        return $this->getPlugin()->getView('editors/image.tpl');
    }

    public function displayAudio() {
        return $this->getPlugin()->getView('editors/autio.tpl');
    }

    public function displayVideo() {
        return $this->getPlugin()->getView('editors/video.tpl');
    }

    public function displayAce() {
        $form = new Form(array(
            'id' => 'hbox-edit-code-form',
            'method' => 'post',
            'attributes' => array(
                'e-attr' => '{id : "hbox-edit-code-form-" + id}'
            )
            'inputs' => array(
                new SubmitInput(array(
                    'name' => 'valid',
                    'value' => Lang::get('main.valid-button')
                )),
                new TextareaInput(array(
                    'name' => 'code',
                    'hidden' => true,
                )),

                new HtmlInput(array(
                    'name' => 'ace',
                    'value' => '<div id="' . uniqid() . '" contenteditable e-ace="{language : extension, value : code}"></div>'
                )),
            )
        ));

        return $form->display();
    }

    public function displayPdf() {
        return $this->getPlugin()->getView('editors/pdf.tpl');
    }



    public function getContent($type) {
        swith
    }
}