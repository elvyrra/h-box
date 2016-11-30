<?php
/**
 * Installer.class.php
 */

namespace Hawk\Plugins\HBox;

/**
 * This class describes the behavio of the installer for the plugin h-box
 */
class Installer extends PluginInstaller{
    /**
     * Install the plugin. This method is called on plugin installation, after the plugin has been inserted in the database
     */
    public function install() {
        BoxElement::createTable();

        Permission::add($this->_plugin . '.access-plugin', 1, 0);
    }

    /**
     * Uninstall the plugin. This method is called on plugin uninstallation, after it has been removed from the database
     */
    public function uninstall() {
        BoxElement::dropTable();

        $permissions = $this->getPlugin()->getPermissions();
        foreach($permissions as $permission) {
            $permission->delete();
        }
    }

    /**
     * Activate the plugin. This method is called when the plugin is activated, just after the activation in the database
     */
    public function activate(){
        MenuItem::add(array(
            'plugin' => $this->_plugin,
            'name' => 'index',
            'labelKey' => $this->_plugin . '.menu-title',
            'action' => 'h-box-index',
            'icon' => 'archive'
        ));
    }

    /**
     * Deactivate the plugin. This method is called when the plugin is deactivated, just after the deactivation in the database
     */
    public function deactivate(){
        $items = $this->getPlugin()->getMenuItems();

        foreach($items as $item) {
            $item->delete();
        }
    }

    /**
     * Configure the plugin. This method contains a page that display the plugin configuration. To treat the submission of the configuration
     * you'll have to create another method, and make a route which action is this method. Uncomment the following function only if your plugin if
     * configurable.
     */
    /*
    public function settings(){

    }
    */

   public function v2_1_0() {
        $items = $this->getPlugin()->getMenuItems();

        foreach($items as $item) {
            $item->icon = 'archive';
            $item->save();
            break;
        }
   }
}