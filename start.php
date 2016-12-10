<?php

namespace Hawk\Plugins\HBox;

App::router()->prefix('/h-box', function() {
    App::router()->auth(App::session()->isAllowed('h-box.access-plugin'), function() {

        // Display the main page of H Box
        App::router()->get('h-box-index', '', array(
            'action' => 'MainController.index'
        ));


        // Get the content of a folder
        App::router()->get('h-box-folder-content', '/folders/{folderId}', array(
            'where' => array(
                'folderId' => '\d+'
            ),
            'action' => 'FolderController.content'
        ));


        // Create a new folder
        App::router()->post('h-box-create-folder', '/folders/{folderId}/create-folder', array(
            'where' => array(
                'folderId' => '\d+'
            ),
            'action' => 'FolderController.create'
        ));

        // Upload a new file
        App::router()->post('h-box-upload-file', '/folders/{folderId}/upload', array(
            'where' => array(
                'folderId' => '\d+'
            ),
            'action' => 'FileController.upload'
        ));

        // Display / edit a file
        App::router()->any('h-box-file', '/files/{fileId}', array(
            'where' => array(
                'fileId' => '\d+'
            ),
            'action' => 'FileController.edit'
        ));


        // Rename a folder or a file
        App::router()->post('h-box-rename-element', '/elements/{elementId}/rename', array(
            'where' => array(
                'elementId' => '\d+'
            ),
            'action' => 'ElementController.rename'
        ));


        // Delete a folder / file
        App::router()->delete('h-box-delete-element', '/elements/{elementId}', array(
            'where' => array(
                'elementId' => '\d+'
            ),
            'action' => 'ElementController.delete'
        ));


        // Download a file
        App::router()->get('h-box-download-element', '/elements/{elementId}/download', array(
            'where' => array(
                'elementId' => '\d+'
            ),
            'action' => 'ElementController.download'
        ));

        // Move a file / folder in another folder
        App::router()->post('h-box-move-element', '/elements/{elementId}/move', array(
            'where' => array(
                'elementId' => '\d+'
            ),
            'action' => 'ElementController.move'
        ));


        // Share a file / folder with a user / a role
        App::router()->post('h-box-share-element', '/elements/{elementId}/share', array(
            'where' => array(
                'elementId' => '\d+'
            ),
            'action' => 'ElementController.share'
        ));

        // Autocomplete users and roles an element can be shared with
        App::router()->get('h-box-share-element-autocomplete', '/elements/{elementId}/share-autocomplete-users', array(
            'where' => array(
                'elementId' => '\d+'
            ),
            'action' => 'ElementController.autocompleteForShare'
        ));
    });

    // Display a static file
    App::router()->get('h-box-static-file', '/files/{token}/static', array(
        'where' => array(
            'token' => '.+'
        ),
        'action' => 'FileController.staticContent'
    ));

});