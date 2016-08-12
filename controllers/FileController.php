<?php

namespace Hawk\Plugins\HBox;


class FileController extends Controller {
    /**
     * Upload a new file
     */
    public function upload() {
        $form = new Form(array(
            'id' => 'hbox-upload-file-form',
            'attributes' => array(
                'ko-with' => 'dialogs.uploadFile'
            ),
            'fieldsets' => array(
                'form' => array(
                    new FileInput(array(
                        'name' => 'files[]',
                        'label' => Lang::get($this->_plugin . '.upload-file-form-file-label'),
                        'multiple' => true,
                        'required' => true
                    ))
                ),

                'submits' => array(
                    new SubmitInput(array(
                        'name' => 'valid',
                        'value' => Lang::get($this->_plugin . '.upload-file-form-upload-btn')
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
                'title' => Lang::get($this->_plugin . '.upload-file-title'),
                'icon' => 'upload',
                'page' => $form
            ));
        }
        elseif($form->check()) {
            try {
                $upload = Upload::getInstance('files');

                if(!$upload) {
                    throw new \Exception('File not uploaded');
                }

                // Get the uploaded file
                $files = $upload->getFiles();
                $elements = [];

                foreach($files as $file) {

                    // Move the file in the userfiles directory of the plugin
                    $filename = uniqid('file-');
                    $directory = $this->getPlugin()->getUserfilesDir();

                    $path = $directory . $filename;

                    $upload->move($file, $directory, $filename);

                    // Save the file in the database
                    $userId = App::session()->getUser()->id;
                    $element = new BoxElement(array(
                        'type' => 'file',
                        'parentId' => $this->folderId,
                        'name' => $file->basename,
                        'path' => $path,
                        'mimeType' => $file->mime,
                        'extension' => $file->extension,
                        'ownerId' => $userId,
                        'ctime' => time(),
                        'mtime' => time(),
                        'modifiedBy' => $userId
                    ));

                    $element->save();

                    $elements[] = $element->formatForJavaScript();
                }

                $form->addReturn($elements);
                return $form->response(Form::STATUS_SUCCESS);
            }
            catch(\Exception $e) {
                return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : null);
            }
        }
    }


    /**
     * Display / edit a file
     * @returns string The HTML result
     */
    public function edit() {
        $file = BoxElement::getById($this->fileId);

        if(App::request()->getMethod() === 'get') {
            // Get the content of the file to display / or edit
            if(!$file->isReadable()) {
                throw ForbiddenException(Lang::get($this->_plugin . '.read-file-forbidden-message'));
            }

            App::response()->setContentType('text/plain');

            switch(strtolower($file->extension)) {
                case 'bmp' :
                case 'gif' :
                case 'jpeg' :
                case 'jpg' :
                case 'tif' :
                case 'png' :
                case 'mp3' :
                case 'wav' :
                case 'pdf' :
                case 'avi' :
                case 'mp4' :
                case 'ppt' :
                case 'pptx' :
                case 'doc' :
                case 'docx' :
                case 'xls' :
                case 'xlsx' :
                    // return the URL to access the static file

                    // Create a uniqe encoded token to access the file
                    $tokenData = array(
                        'fileId' => $this->fileId,
                        'sessionId' => session_id(),
                        'userId' => App::session()->getUser()->id
                    );

                    $token = Crypto::aes256Encode(json_encode($tokenData));

                    return App::router()->getUrl('h-box-static-file', array(
                        'token' => $token
                    ));

                default :
                    return file_get_contents($file->path);
            }
        }
        else {
            // Store the new version of the file
            if(!$file->isWritable()) {
                throw ForbiddenException(Lang::get($this->_plugin . '.write-file-forbidden-message'));
            }

            try {
                file_put_contents($file->path, App::request()->getBody('data'));

                $file->set(array());

                $file->save();

                App::response()->setStatus(204);
            }
            catch(\Exception $e) {
                App::response()->setStatus(500);
                App::response()->setContentType('json');

                return array(
                    'message' => $e->getMessage()
                );
            }
        }
    }


    /**
     * Load the static content of a file
     * @return string The file content
     */
    public function staticContent() {
        $token = json_decode(Crypto::aes256Decode($this->token));

        $session = SessionModel::getById($token->sessionId);
        if(!$session) {
            throw new ForbiddenException(Lang::get($this->_plugin . '.read-file-forbidden-message'));
        }

        session_decode($session->data);

        if(!App::session()->getData('user.id')) {
            throw new ForbiddenException(Lang::get($this->_plugin . '.read-file-forbidden-message'));
        }
        $user = User::getById(App::session()->getData('user.id'));

        if(!$user || $user->id !== $token->userId) {
            throw new ForbiddenException(Lang::get($this->_plugin . '.read-file-forbidden-message'));
        }

        $file = BoxElement::getById($token->fileId);

        if(!$file->isReadable($user)) {
            throw ForbiddenException(Lang::get($this->_plugin . '.read-file-forbidden-message'));
        }


        App::response()->setContentType($file->mimeType);

        return file_get_contents($file->path);
    }
}