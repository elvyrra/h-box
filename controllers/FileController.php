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
                'e-with' => '$root.dialogs.uploadFile'
            ),
            'fieldsets' => array(
                'form' => array(
                    new FileInput(array(
                        'name' => 'files[]',
                        'label' => Lang::get($this->_plugin . '.upload-file-form-file-label'),
                        'multiple' => true,
                        'required' => true,
                        'attributes' => array(
                            'e-on' => '{change : $this.selectFile}'
                        )
                    )),

                    new CheckboxInput(array(
                        'name' => 'extract',
                        'label' => Lang::get($this->_plugin . '.upload-file-form-extract-label'),
                        'labelWidth' => 'auto',
                        'attributes' => array(
                            'e-value' => 'extract'
                        )
                    )),
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
                            'e-click' => 'open = null'
                        )
                    ))
                )
            )
        ));

        if(!$form->submitted()) {
            // Display the upload form
            $pageContent = View::make($this->getPlugin()->getView('new-file-form.tpl'), array(
                'form' => $form
            ));

            return array(
                'title' => Lang::get($this->_plugin . '.upload-file-title'),
                'icon' => 'upload',
                'page' => $pageContent
            );
        }
        elseif($form->check()) {
            $folder = BoxElement::getById($this->folderId);

            try {
                $upload = Upload::getInstance('files');

                if(!$upload) {
                    throw new \Exception('File not uploaded');
                }

                // Get the uploaded file
                $files = $upload->getFiles();
                $elements = [];

                if($form->getData('extract') && count($files) === 1 && in_array($files[0]->extension, array('zip'))) {
                    if(!$folder->isWritable()) {
                        // No file can be created in this folder by the user
                        throw new ForbiddenException(Lang::get($this->_plugin . '.write-folder-forbidden-message'));
                    }

                    $elements = $this->importArchive($files[0], $folder);
                }
                else {
                    foreach($files as $file) {
                        $element = BoxElement::getByExample(new DBExample(array(
                            'type' => 'file',
                            'parentId' => $this->folderId,
                            'name' => $file->basename
                        )));

                        if($element) {
                            // The element already exists, and must be updated
                            if(!$element->isWritable()) {
                                // The file is not writable by the user
                                throw new ForbiddenException(Lang::get($this->_plugin . '.write-file-forbidden-message'));
                            }

                            // Update an existing file
                            $path = $element->path;

                            $element->set(array(
                                'mimeType' => $file->mime
                            ));
                        }
                        else {
                            // Create a new file
                            if(!$folder->isWritable()) {
                                // No file can be created in this folder by the user
                                throw new ForbiddenException(Lang::get($this->_plugin . '.write-folder-forbidden-message'));
                            }

                            // Move the file in the userfiles directory of the plugin
                            $path = uniqid($this->getPlugin()->getUserfilesDir() . 'file-');

                            // Save the file in the database
                            $userId = App::session()->getUser()->id;
                            $element = new BoxElement(array(
                                'type' => 'file',
                                'parentId' => $this->folderId,
                                'name' => $file->basename,
                                'path' => $path,
                                'mimeType' => $file->mime,
                                'ownerId' => $userId,
                                'ctime' => time(),
                            ));
                        }

                        $upload->move($file, dirname($path), basename($path));

                        $element->save();

                        // Update the mtime of the parent folder
                        if($folder->id) {
                            $folder->save();
                        }

                        $elements[] = $element->formatForJavaScript();
                    }
                }

                $form->addReturn(array(
                    'elements' => $elements,
                    'folder' => $folder->formatForJavaScript()
                ));

                return $form->response(Form::STATUS_SUCCESS);
            }
            catch(\Exception $e) {
                return $form->response(Form::STATUS_ERROR, DEBUG_MODE ? $e->getMessage() : null);
            }
        }
    }


    /**
     * Import an archive
     * @import Object $file The file to extract
     * @returns array The created elements
     */
    private function importArchive($file, $folder) {
        // The created elements
        $elements = array();
        $zip = new \Ziparchive();
        $open = $zip->open($file->tmpFile);

        if(!$open) {
            // The file is not a valid archive file
            throw new BadRequestException(Lang::get($this->_plugin . '.extract-archive-bad-format-message'), array(
                'files[]' => Lang::get($this->_plugin . '.extract-archive-bad-format-message')
            ));
        }

        $zip->extractTo(TMP_DIR . $file->basename);

        $this->importFiles(TMP_DIR . $file->basename, $folder, $elements);

        return $elements;
    }

    /**
     * Import files from a folder
     * @param   string      $dirname   The folder name containing the files to import
     * @param   HBoxElement $folder    The HBox fodler where insert the imported files
     * @param   array       &$elements The elements that are created when importing
     */
    private function importFiles($dirname, $folder, &$elements) {
        $files = glob($dirname . '/*');
        $finfo = finfo_open(FILEINFO_MIME_TYPE);

        foreach($files as $file) {
            if(is_dir($file)) {
                // Create a folder
                $subFolder = new BoxElement(array(
                    'type' => BoxElement::ELEMENT_FOLDER,
                    'name' => basename($file),
                    'parentId' => $folder->id,
                    'ownerId' => App::session()->getUser()->id,
                    'ctime' => time()
                ));

                $subFolder->save();

                $elements[] = $subFolder->formatForJavaScript();

                // Update the mtime of the parent folder
                if($folder->id) {
                    $folder->save();
                }

                $this->importFiles($file, $subFolder, $elements);
            }
            else {
                // create a file
                // Move the file in the userfiles directory of the plugin
                $path = uniqid($this->getPlugin()->getUserfilesDir() . 'file-');

                // Save the file in the database
                $userId = App::session()->getUser()->id;

                $element = new BoxElement(array(
                    'type' => BoxElement::ELEMENT_FILE,
                    'parentId' => $folder->id,
                    'name' => basename($file),
                    'path' => $path,
                    'mimeType' => finfo_file($finfo, $file),
                    'ownerId' => $userId,
                    'ctime' => time(),
                ));

                rename($file, $path);

                $element->save();

                // Update the mtime of the parent folder
                if($folder->id) {
                    $folder->save();
                }

                $elements[] = $element->formatForJavaScript();
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

                case 'zip' :
                    $zip = new \zipArchive();
                    if($zip->open($file->path) !== true) {
                        return Lang::get($this->_plugin . '.extract-archive-bad-format-message');
                    }

                    $data = array();
                    $allContent = array();
                    App::response()->setContentType('json');

                    foreach(range(0, $zip->numFiles - 1) as $i) {
                        $info = $zip->statIndex($i);

                        $type = preg_match('#/$#', $info['name']) ? BoxElement::ELEMENT_FOLDER : BoxElement::ELEMENT_FILE;

                        // Get the parent folder
                        $dirname = dirname($info['name']);

                        if(empty($allContent[$dirname . '/'])) {
                            $dir = &$data;
                        }
                        else {
                            $dir = &$allContent[$dirname . '/']->content;
                        }

                        if($type == BoxElement::ELEMENT_FOLDER) {
                            $line = (object) array(
                                'type' => $type,
                                'name' => basename($info['name']),
                                'fullname' => $info['name'],
                                'content' => array(),
                                'uid' => uniqid(),
                                'developed' => false
                            );

                        }
                        else {
                            $line = (object) array(
                                'type' => $type,
                                'name' => basename($info['name']),
                                'fullname' => $info['name'],
                                'size' => $info['size'],
                                'uid' => uniqid()
                            );
                        }

                        $allContent[$line->fullname] = $line;
                        $dir[] = $line;
                    }

                    return $data;

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
            throw new ForbiddenException(Lang::get($this->_plugin . '.read-file-forbidden-message'));
        }


        App::response()->setContentType($file->mimeType);

        if(preg_match('/^(audio|video)/', $file->mimeType)) {
            $stream = new MediaStream($file);
            $stream->start();
            return;
        }
        return file_get_contents($file->path);
    }
}