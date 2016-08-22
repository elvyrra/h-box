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

            $elementWithSameName = BoxElement::getByExample(new DBExample(array(
                'parentId' => $element->parentId,
                'name' => $form->getData('name'),
                'type' => $element->type,
                'id' => array(
                    '$ne' => $element->id
                )
            )));

            if($elementWithSameName) {
                return $form->response(Form::STATUS_CHECK_ERROR, Lang::get($this->_plugin . '.rename-element-name-exists-error', array('type' => $element->type)));
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
                $parent = BoxElement::getById($element->parentId);

                if(!$element->isWritable()) {
                    throw ForbiddenException(Lang::get($this->_plugin . '.delete-' . $element->type . '-forbidden-message'));
                }

                $element->delete();

                // Update the mtime of the parent folder
                if($parent->id) {
                    $parent->save();
                }

                $form->addReturn(array(
                    'parentFolder' => $parent->formatForJavaScript()
                ));

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
            $parent = BoxElement::getById($parentId);
            $oldParent = BoxElement::getById($element->parentId);

            if(!$element->isWritable()) {
                throw ForbiddenException(Lang::get($this->_plugin . '.read-' . $element->type . '-forbidden-message'));
            }

            if(!$parent->isWritable()) {
                throw ForbiddenException(Lang::get($this->_plugin . '.read-' . $parent->type . '-forbidden-message'));
            }

            if(!$oldParent->isWritable()) {
                throw ForbiddenException(Lang::get($this->_plugin . '.read-' . $oldParent->type . '-forbidden-message'));
            }

            $existing = BoxElement::getByExample(new DBExample(array(
                'type' => $element->type,
                'name' => $element->name,
                'parentId' => $parentId
            )));

            if($existing) {
                // Cannot move the file in a folder if another element exists with the same name in the new parent folder
                return $form->response(Form::STATUS_ERROR, Lang::get($this->_plugin . '.move-element-name-exists-error', array(
                    'type' => $element->type
                )));
            }

            try {
                $element->set(array(
                    'parentId' => $parent->id
                ));

                $element->save();

                // Update the old and new parent folders mtime
                if($oldParent->id) {
                    $oldParent->save();
                }

                $parent->save();

                $form->addReturn(array(
                    'oldParent' => $oldParent->formatForJavaScript(),
                    'newParent' => $parent->formatForJavaScript()
                ));

                return $form->response(Form::STATUS_SUCCESS);
            }
            catch(\Exception $e) {
                return $form->response(Form::STATUS_ERROR, $e->getMessage());
            }
        }
    }


    public function share() {
        $form = new Form(array(
            'id' => 'hbox-share-element-form',
            'attributes' => array(
                'ko-with' => '$root.dialogs.shareElement',
            ),
            'fieldsets' => array(
                'with' => array(
                    new TextInput(array(
                        'name' => 'with',
                        'label' => Lang::get($this->_plugin . '.share-form-with-label'),
                        'attributes' => array (
                            'ko-autocomplete' => '{source : autocompleteUrl, change : change}',
                            'ko-value' => 'shareWith',
                            'autocomplete' => 'false'
                        )
                    )),
                ),

                'shared' => array(
                    'legend' => Lang::get($this->_plugin . '.share-form-shared-legend'),

                    new HtmlInput(array(
                        'value' => View::make($this->getPlugin()->getView('edit-element-sharing.tpl'))
                    ))
                ),



                'submits' => array(
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
            $this->addKeysToJavaScript(
                $this->_plugin . '.share-form-write-file-label',
                $this->_plugin . '.share-form-write-folder-label'
            );

            return Dialogbox::make(array(
                'icon' => 'share-alt',
                'title' => Lang::get($this->_plugin . '.share-form-title'),
                'page' => $form
            ));
        }
        else {
            try {
                $element = BoxElement::getById($this->elementId);
                if(!$element) {
                    throw new PageNotFound();
                }

                $me = App::session()->getUser();
                if($element->ownerId !== $me->id && !$me->isAllowed('admin.all')) {
                    throw new ForbiddenException(Lang::get($this->_plugin . '.share-' . $element->type . '-forbidden-message'));
                }

                $usernames = App::request()->getBody('users');
                if(!empty($usernames)) {

                    $canWrite = App::request()->getBody('canWrite');


                    $users = User::getListByExample(new DBExample(array(
                        'username' => array(
                            '$in' => $usernames
                        )
                    )));

                    foreach($users as $user) {
                        if(!$user || !$user->isAllowed($this->_plugin . '.access-plugin')) {
                            throw new ForbiddenException(Lang::get($this->_plugin . '.share-element-user-forbidden-message', array(
                                'username' => $user->username
                            )));
                        }
                    }

                    $notifRecipients = array();
                    $oldPermissions = $element->permissions['users'];
                    $element->permissions['users'] = [];

                    foreach($users as $user) {
                        if(empty($oldPermissions[$user->id])) {
                            $notifRecipients[] = $user;
                        }

                        $element->permissions['users'][$user->id] = array(
                            'read' => true,
                            'write' => !empty($canWrite[$user->username])
                        );

                        $element->save();
                    }

                    foreach($notifRecipients as $recipient) {
                        $mail = new Mail();
                        $mail->from($me->email, $me->getDisplayName());
                        $mail->to($recipient->email);
                        $mail->subject(Lang::get($this->_plugin . '.share-email-title', array(
                            'username' => $me->username
                        )));
                        $mail->title(Lang::get($this->_plugin . '.share-email-title', array(
                            'username' => $me->username
                        )));
                        $mail->content(Lang::get($this->_plugin . '.share-email-title', array(
                            'username' => $me->username,
                            'type' => $element->type,
                            'name' => $element->name
                        )));
                        $mail->send();
                    }
                }
                else {
                    $element->permissions['users'] = [];
                    $element->save();
                }

                $form->addReturn($element->formatForJavaScript());

                return $form->response(Form::STATUS_SUCCESS);
            }
            catch(\Exception $e) {
                return $form->response(Form::STATUS_ERROR, $e->getMessage());
            }
        }
    }


    public function autocompleteForShare() {
        $element = BoxElement::getById($this->elementId);

        $matchingUsers = User::getListByExample(new DBExample(array(
            'username' => array(
                '$like' => '%' . App::request()->getParams('q') . '%'
            ),
            'active' => 1
        )));

        $users = array_filter($matchingUsers, function($user) use($element){
            if(!$user->isAllowed('h-box.access-plugin')) {
                Utils::debug('cannot access : ' . $user->username);
                return false;
            }

            return true;
        });

        User::getListByExample(new DBExample(array(
            'username' => array(
                '$like' => '%' . App::request()->getParams('q') . '%'
            )
        )));

        App::response()->setContentType('json');

        return array_map(function($user) {
            return array(
                'type' => 'users',
                'id' => (int) $user->id,
                'label' => $user->username,
            );
        }, $users);
    }
}