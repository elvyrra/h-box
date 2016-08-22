<?php

namespace Hawk\Plugins\HBox;

/**
 * This model manage the files and folders in the plugin h-box
 */
class BoxElement extends Model {
    protected static $tablename = 'HBoxElement';

    protected static $fields = array(
        'id' => array(
            'type' => 'int(11)',
            'auto_increment' => true
        ),

        'type' => array(
            'type' => 'enum("file", "folder")',
            'default' => self::ELEMENT_FILE
        ),

        // The parent element containing the element
        'parentId' => array(
            'type' => 'int(11)',
            'default' => 0
        ),

        // The file name
        'name' => array(
            'type' => 'varchar(256)'
        ),

        // The path of the registered file, relative to userfiles/plugins/h-box
        'path' => array(
            'type' => 'varchar(4096)'
        ),

        'mimeType' => array(
            'type' => 'varchar(128)'
        ),

        'extension' => array(
            'type' => 'varchar(16)'
        ),

        'ownerId' => array(
            'type' => 'int(11)'
        ),

        'ctime' => array(
            'type' => 'int(11)'
        ),

        'mtime' => array(
            'type' => 'int(11)'
        ),

        'modifiedBy' => array(
            'type' => 'int(11)'
        ),

        'permissions' => array(
            'type' => 'text',
            'null' => true
        )
    );


    protected static $constraints = array(
        'ownerId' => array(
            'type' => 'foreign',
            'fields' => array(
                'ownerId'
            ),
            'references' => array(
                'model' => 'User',
                'fields' => array(
                    'id'
                )
            )
        ),
    );


    const ELEMENT_FILE = 'file';

    const ELEMENT_FOLDER = 'folder';

    private static $instances = array();

    public function __construct($data = array()) {
        parent::__construct($data);

        if(isset($this->permissions) && is_string($this->permissions)) {
            $this->permissions = json_decode($this->permissions, true);
        }
    }


    public static function getById($id) {
        if($id) {
            return parent::getById($id);
        }

        return self::getRootElement();
    }

    /**
     * Returns the elements that are shared with the current logged user
     * @returns [type] [description]
     */
    public static function getSharedElements() {

    }


    /**
     * Get the subelements of a folder element
     *
     * @return Array The list of all the elements contained in this folder
     */
    public function getElements($fields = array()) {
        if($this->type !== 'folder') {
            return array();
        }

        return self::getListByExample(
            new DBExample(array(
                'parentId' => $this->id
            )),
            null,
            $fields
        );
    }


    /**
     * Get the visible sub elements of a folder element
     *
     * @param User $user The user to search the readable files for
     *
     * @return Array The list of readable hbox elements for the user $user
     */
    public function getReadableElements($fields = array(), \Hawk\User $user = null) {
        if(!$user) {
            $user = App::session()->getUser();
        }

        $elements = $this->getElements($fields);

        return array_filter($elements, function($element) use($user){
            return $element->isReadable($user);
        });
    }

    /**
     * Get the rights on the element for a given user
     */
    public function getPermissions(\Hawk\User $user = null, $prop = null) {
        if(!$user) {
            $user = App::session()->getUser();
        }

        if(!$this->id || $user->isAllowed('admin.all') || $this->ownerId === $user->id) {
            // The main admin can perform any action on any element
            $permissions = array(
                'read' => true,
                'write' => true,
            );
        }
        elseif(!empty($this->permissions['users'][$user->id])) {
            // Permissions are defined for the user
            $permissions = $this->permissions['users'][$user->id];
            $permissions['read'] = true;
        }
        elseif(!empty($this->permissions['roles'][$user->roleId])) {
            // Permissions are defined for the user's role
            $permissions = $this->permissions['roles'][$user->roleId];
            $permissions['read'] = true;
        }
        elseif($this->parentId) {
            $parent = self::getById($this->parentId);

            $permissions = array(
                'read' => $parent->isReadable($user),
                'write' => $parent->isWritable($user)
            );
        }
        else {
            // No permissions are defined for the user, the user does not have any permission on the element
            $permissions = array(
                'read' => false,
                'write' => false
            );
        }

        return $prop ? $permissions[$prop] : $permissions;
    }

    /**
     * Check if an element is readable by a user
     */
    public function isReadable(\Hawk\User $user = null) {
        return $this->getPermissions($user, 'read');
    }

    /**
     * Check if an element is writable by a user
     */
    public function isWritable(\Hawk\User $user = null) {
        return $this->getPermissions($user, 'write');
    }

    /**
     * Prepare the data to be inserted in the database when saving an elements
     * @returns array The data to insert
     */
    protected function prepareDatabaseData() {
        $insert = parent::prepareDatabaseData();

        if(!empty($insert['permissions'])) {
            $insert['permissions'] = json_encode($insert['permissions']);
        }

        return $insert;
    }

    public function save() {
        $this->mtime = time();
        $this->modifiedBy = App::session()->getUser()->id;
        $this->extension = pathinfo($this->name, PATHINFO_EXTENSION);

        parent::save();
    }

    /**
     * Delete the model data from the database
     *
     * @return true if the data has been sucessfully removed from the database, false in other cases
     */
    public function delete() {
        foreach($this->getElements() as $subelement) {
            $subelement->delete();
        }

        return parent::delete();
    }


    public function isFolder() {
        return $this->type === 'folder';
    }


    public function isFile() {
        return $this->type === 'file';
    }


    public function getParents() {
        if($this->id === 0) {
            // The root element does not have parents
            return array();
        }

        $parent = BoxElement::getById($this->parentId);
        $parents = array(self::getRootElement());

        if($parent) {
            $parents += $parent->getParents();
            $parents[] = $parent;
        }

        return $parents;
    }


    public static function getRootElement() {
        return new self(array(
            'id' => 0,
            'type' => 'folder',
            'name' => Lang::get('h-box.root-folder-name'),
            'parentId' => null
        ));
    }


    public function formatForJavaScript() {
        $output = (object) array(
            'id' => (int) $this->id,
            'type' => $this->type,
            'parentId' => $this->parentId === null ? null : (int) $this->parentId,
            'name' => $this->name,
            'owner' => !empty($this->ownerId) ? User::getById($this->ownerId)->username : '',
            'modifiedBy' => !empty($this->modifiedBy) ? User::getById($this->modifiedBy)->username : '',
            'writable' => $this->isWritable()
        );


        if(!empty($this->extension)) {
            $output->extension = $this->extension;
        }
        if(!empty($this->ctime)) {
            $output->ctime = date(Lang::get('main.time-format'), (int) $this->ctime);
        }
        if(!empty($this->mtime)) {
            $output->mtime = date(Lang::get('main.time-format'), (int) $this->mtime);
        }

        $user = App::session()->getUser();
        $output->shared = [];

        if($this->id && ($user->isAllowed('admin.all') || $user->id === $this->ownerId)) {
            $output->canShare = true;
            if(!empty($this->permissions['users'])) {
                foreach($this->permissions['users'] as $userId => $rights) {
                    $output->shared[] = array(
                        'user' => User::getById($userId)->username,
                        'rights' => $rights
                    );
                }
            }
        }

        return $output;
    }

    public function archive($directory) {
        $zip = new \ZipArchive;
        $zip->open($directory . '/' . uniqid() . '.zip', \ZipArchive::CREATE);

        $this->addToArchive($zip);

        $filename = $zip->filename;
        $zip->close();
        return $filename;
    }

    private function addToArchive($archive, $prefix = '') {
        if($this->isReadable()) {
            if($this->isFolder()) {
                $dirname = $prefix . $this->name;
                $archive->addEmptyDir($dirname);

                foreach($this->getElements() as $element) {
                    $element->addToArchive($archive, $dirname . '/');
                }
            }
            else {
                $archive->addFile($this->path, $prefix . $this->name);
            }
        }
    }
}