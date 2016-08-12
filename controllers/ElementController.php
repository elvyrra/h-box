<?php

namespace Hawk\Plugins\HBox;

class ElementController extends Controller {
    /**
     * Rename a file or a folder. This method displays or treats the form to rename an element,
     * depending on the HTTP request
     */
    public function rename() {
        $form = new Form(array(
            'id' => 'hbox-rename-element-form',
            'model' => 'BoxElement',
            'attributes' => array(
                'ko-with' => 'dialogs.renameElement'
            ),
            'fieldsets' => array(
                'form' => array(
                    new TextInput(array(
                        'name' => 'name',
                        'required' => true,
                        'label' => Lang::get($this->_plugin . '.rename-element-name-label'),
                        'attributes' => array(
                            'ko-value' => 'name'
                        )
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
                        'attributes' => array (
                            'ko-click' => 'function() {open(null);}'
                        )
                    ))
                )
            )
        ));

        if(!$form->submitted()) {
            return Dialogbox::make(array(
                'title' => Lang::get($this->_plugin . '.rename-element-form-title'),
                'icon' => 'pencil',
                'page' => $form
            ));
        }
        elseif($form->check()) {
            $element = BoxElement::getById($this->elementId);

            if(!$element->isWritable()) {
                throw ForbiddenException(Lang::get($this->_plugin . '.delete-' . $element->type . '-forbidden-message'));
            }

            try {
                $element->set(array(
                    'name' => $form->getData('name'),
                ));


                $element->save();
                $form->addReturn($element->formatForJavaScript());

                return $form->response(Form::STATUS_SUCCESS);
            }
            catch(\Exception $e) {
                return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : null);
            }
        }
    }

    /**
     * Delete a folder or a file
     */
    public function delete() {
        $form = new Form(array(
            'id' => 'hbox-delete-element-form',
            'method' => 'delete',
            'attributes' => array(
                'ko-with' => 'dialogs.deleteElement'
            ),
            'fieldsets' => array(
                'form' => array(
                    new HtmlInput(array(
                        'value' => '<p>' . Lang::get($this->_plugin . '.delete-element-confirmation') . '</p>'
                    )),
                ),
                'submits' => array(
                    new DeleteInput(array(
                        'name' => 'confirm',
                        'value' => Lang::get('main.delete-button'),
                        'nl' => true
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
                'title' => Lang::get($this->_plugin . '.delete-element-form-title'),
                'icon' => 'times-circle',
                'page' => $form
            ));
        }
        else {
            try {
                $element = BoxElement::getById($this->elementId);

                if(!$element->isRemovable()) {
                    throw ForbiddenException(Lang::get($this->_plugin . '.delete-' . $element->type . '-forbidden-message'));
                }

                $element->delete();

                return $form->response(Form::STATUS_SUCCESS);
            }
            catch(\Exception $e) {
                return $form->response(Form::STATUS_ERROR);
            }
        }
    }


    /**
     * Download a file or a folder. If a folder is downloaded, then a zip file is created and sent to the client
     */
    public function download() {
        $element = $this->elementId ? BoxElement::getById($this->elementId) : BoxElement::getRootElement();

        if(!$element || !$element->isReadable()) {
            throw ForbiddenException(Lang::get($this->_plugin . '.read-' . $element->type . '-forbidden-message'));
        }

        if ($element->isFolder()) {
            // Download a full folder. Create a zip file with all the folder content and send it
            $filename = $element->archive(TMP_DIR);

            $data = file_get_contents($filename);
            $outFilename = $element->name . '.zip';

            unlink($filename);
        }
        else {
            // Download a file.
            $data = file_get_contents($element->path);
            $outFilename = $element->name;
        }

        $response = App::response();
        $response->setContentType('application/octet-stream');
        $response->header('Content-Transfer-Encoding', 'Binary');
        $response->header('Content-Disposition', 'attachment; filename="' . $outFilename . '"');

        return $data;
    }



    /**
     * Move an element from a folder to another one
     */
    public function move() {
        $form = new Form(array(
            'id' => 'hbox-move-element-form',
            'fieldsets' => array(
                'form' => array(
                    new HiddenInput(array(
                        'name' => 'parentId',
                        'attributes' => array(
                            'ko-value' => 'dialogs.moveElement.parentId'
                        )
                    )),
                    new HtmlInput(array(
                        'value' => View::make($this->getPlugin()->getView('move-element-form.tpl'))
                    ))
                ),
                'submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get('main.valid-button'),
                        'attributes' => array(
                            'ko-disable' => '!$root.dialogs.moveElement.parent()'
                        )
                    )),

                    new ButtonInput(array(
                        'name' => 'cancel',
                        'value' => Lang::get('main.cancel-button'),
                        'attributes' => array (
                            'ko-click' => 'function() {dialogs.moveElement.open(null);}'
                        )
                    ))
                )
            )
        ));

        if(!$form->submitted()) {
            return Dialogbox::make(array(
                'title' => Lang::get($this->_plugin . '.move-element-form-title'),
                'icon' => 'arrows-alt',
                'page' => $form
            ));
        }
        elseif($form->check()) {
            $element = BoxElement::getById($this->elementId);

            $parentId = $form->getData('parentId');
            $parent = $parentId ? BoxElement::getById($parentId) : BoxElement::getRootElement();

            if(!$element->isWritable()) {
                throw ForbiddenException(Lang::get($this->_plugin . '.read-' . $element->type . '-forbidden-message'));
            }

            if(!$parent->isWritable()) {
                throw ForbiddenException(Lang::get($this->_plugin . '.read-' . $parent->type . '-forbidden-message'));
            }

            try {
                $element->set(array(
                    'parentId' => $parent->id
                ));

                $element->save();

                $form->addReturn('parentId', $parent->id);

                return $form->response(Form::STATUS_SUCCESS);
            }
            catch(\Exception $e) {
                return $form->response(Form::STATUS_ERROR);
            }
        }
    }
}