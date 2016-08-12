<?php

namespace Hawk\Plugins\HBox;

class FolderController extends Controller {
    /**
     * Create a new folder
     */
    public function create() {
        $form = new Form(array(
            'id' => 'hbox-create-folder-form',
            'model' => 'BoxElement',
            'reference' => array(
                'id' => 0
            ),
            'attributes' => array(
                'ko-with' => 'dialogs.newFolder'
            ),
            'fieldsets' => array(
                'form' => array(
                    new TextInput(array(
                        'name' => 'name',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.create-folder-name-label')
                    ))
                ),

                'submits' => array (
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button')
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'attributes' => array(
                            'ko-click' => 'function() {open(null);}'
                        )
                    ))
                )
            )
        ));

        if(!$form->submitted()) {
            return Dialogbox::make(array(
                'title' => Lang::get($this->_plugin . '.create-folder-title'),
                'icon' => 'folder-open-o',
                'page' => $form
            ));
        }
        elseif($form->check()) {
            try {
                $folder = new BoxElement(array(
                    'type' => BoxElement::ELEMENT_FOLDER,
                    'name' => $form->getData('name'),
                    'parentId' => $this->folderId,
                    'ownerId' => App::session()->getUser()->id,
                    'ctime' => time(),
                    'mtime' => time(),
                    'modifiedBy' => App::session()->getUser()->id
                ));

                $folder->save();

                $form->addReturn($folder->formatForJavaScript());

                return $form->response(Form::STATUS_SUCCESS);
            }
            catch(\Exception $e) {
                return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : '');
            }
        }
    }

    public function edit() {

    }
}