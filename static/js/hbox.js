'use strict';

require(['app', 'jquery', 'lang', 'emv'], (app, $, Lang, EMV) => {
    /**
     * This class describes the behavior of an element in Hbox
     * @param {Object} data  The initial data of the element
     */
    class HBoxElement extends EMV {
        /**
         * Constructor
         * @param  {Object} data The initial data of the element
         */
        constructor(data) {
            data.saved = true;
            data.data = undefined;
            data.tabContent = '';
            data.developed = !data.id;
            data.developedInMoveForm = !data.id;
            data.isFolder = data.type === 'folder';
            data.contentLoaded = !data.id;

            super({
                data : data,
                computed : {
                    extension : function() {
                        return this.name.split('.').pop().toLowerCase();
                    },
                    fileType : function() {
                        if(this.isFolder) {
                            return null;
                        }

                        for(var fileType in HBoxElement.fileTypes) {
                            if(HBoxElement.fileTypes.hasOwnProperty(fileType)) {
                                var typeData = HBoxElement.fileTypes[fileType];

                                if(typeData.extensions.indexOf(this.extension) !== -1) {
                                    return fileType;
                                }
                            }
                        }

                        return 'default';
                    },
                    language : function() {
                        if(this.fileType !== 'code') {
                            return 'text';
                        }

                        return HBoxElement.fileTypes.code.languages[this.extension] || this.extension;
                    },
                    icon : function() {
                        if(this.isFolder) {
                            return 'folder-open';
                        }

                        return HBoxElement.fileTypes[this.fileType].icon;
                    },
                    template : function() {
                        if(this.isFolder) {
                            return null;
                        }

                        return HBoxElement.fileTypes[this.fileType].template;
                    },
                    tabId : function() {
                        return 'hbox-tab-file-' + this.id;
                    },
                    sortFunction : function() {
                        return this['sortChildrenBy' + this.$root.sortOption.field];
                    }
                }
            });

            // Contains the data used to display the file
            this.$watch('data', () => {
                this.saved = false;
            });

            this.$watch(['developed', 'developedInMoveForm'], (value, oldValue) => {
                if(value && !oldValue && this.isFolder && !this.contentLoaded) {
                    this.loadContent();
                }
            });
        }

        /**
         * This method is used to filter if an element is a child of the element instance
         * @param   {HBoxElement} element The element to test
         * @returns {bool}                True if the element is a child, else False
         */
        childFilter(element) {
            return element.parentId === this.id;
        }

        /**
         * Get the element children
         * @returns {Array} The list of children elements
         */
        children() {
            return this.$root.elements.filter(this.childFilter.bind(this));
        }

        /**
         * Get the parent folder
         * @returns {HBOXElement} The parent folder
         */
        parent() {
            return this.$root.elements.find((element) => {
                return element.id === this.parentId;
            });
        }

        /**
         * Get all the element parents
         * @returns {Array} The list of the element parents
         */
        parents() {
            if(!this.parent()) {
                return [];
            }

            return this.parent().parents().concat([this.parent()]);
        }

        /**
         * Display the tab containing the file editor
         */
        showTab() {
            $('[data-toggle="tab"][href="#' + this.tabId + '"]').tab('show');
        }


        /**
         * Update element data
         * @param {Object} data The data to update on the element
         */
        setData(data) {
            this.name = data.name || this.name;
            this.mtime = data.mtime || this.mtime;
            this.modifiedBy = data.modifiedBy || this.modifiedBy;
            this.shared = data.shared || this.shared;
        }

        /**
         * Share the element with a user
         * @param  {Object} user   The user to share to
         * @param  {Object} rights The rights to give to the user on the element
         */
        share(user, rights) {
            var existingSharing = this.shared.find((share) => {
                return share.user === user;
            });

            if(!existingSharing) {
                this.shared.push({
                    user : user,
                    rights : {
                        write : rights.write
                    }
                });
            }
        }

        /**
         * Function to sort the element children by name
         * @param   {HBoxElement} elem1 The first children
         * @param   {HBoxElement} elem2 The second childre
         * @returns {int}       -1 if elem1 must be placed before elem2, else 1
         */
        sortChilrenByname(elem1, elem2) {
            if(elem1.type === elem2.type) {
                return elem1.name.toLowerCase() < elem2.name.toLowerCase() ? -1 : 1;
            }

            return elem1.type === 'folder' ? -1 : 1;
        }

        /**
         * Function to sort the element children by mtime
         * @param   {HBoxElement} elem1 The first children
         * @param   {HBoxElement} elem2 The second childre
         * @returns {int}       -1 if elem1 must be placed before elem2, else 1
         */
        sortChildrenBymtime(elem1, elem2) {
            return elem2.mtime - elem2.mtime;
        }

        /**
         * Load the content of a folder
         * @returns {Deffered} Resolved when the query succeed
         */
        loadContent() {
            return $.getJSON(app.getUri('h-box-folder-content', {
                folderId : this.id
            }))

            .done((response) => {
                this.contentLoaded = true;

                // Create a new element
                response.forEach((line) => {
                    this.$root.elements.push(new this.constructor(line));
                });
            });
        }
    }

    // The box icons
    HBoxElement.fileTypes = {
        image : {
            icon : 'file-image-o',
            template : 'image',
            extensions : [
                'bmp',
                'gif',
                'ico',
                'jpeg',
                'jpg',
                'png',
                'tif'
            ]
        },
        audio : {
            icon : 'file-audio-o',
            template : 'audio',
            extensions : [
                'mp3',
                'wav'
            ]
        },
        video : {
            icon : 'file-video-o',
            template : 'video',
            extensions : [
                'avi',
                'mp4'
            ]
        },
        code : {
            icon : 'file-code-o',
            template : 'ace',
            extensions : [
                'c',
                'cpp',
                'css',
                'html',
                'java',
                'js',
                'less',
                'php',
                'pl',
                'tpl'
            ],
            languages : {
                c : 'c_cpp',
                cpp : 'c_cpp',
                js : 'javascript',
                pl : 'perl',
                tpl : 'html'
            }
        },
        word : {
            icon : 'file-word-o',
            template : 'office',
            extensions : [
                'doc',
                'docx'
            ]
        },
        powerpoint : {
            icon : 'file-powerpoint-o',
            template : 'office',
            extensions : [
                'ppt',
                'pptx'
            ]
        },
        excel : {
            icon : 'file-excel-o',
            template : 'office',
            extensions : [
                'xls',
                'xlsx'
            ]
        },
        archive : {
            icon : 'file-archive-o',
            template : 'zip',
            extensions : [
                'gz',
                'rar',
                'tar',
                'zip'
            ]
        },
        pdf : {
            icon : 'file-pdf-o',
            template : 'pdf',
            extensions : [
                'pdf'
            ]
        },
        default : {
            icon : 'file-o',
            template : 'ace',
            extensions : []
        }
    };

    /**
     * This class manages the dialog forms in hbox
     */
    class HBoxDialogForm extends EMV {
        /**
         * Constructor
         * @param   {Object} data The initial dialog form data
         */
        constructor(data) {
            super(data);

            const form = app.forms[this.form];
            const modal = $('#' + form.id).parents('.modal').first();

            this.$watch('open', (value) => {
                modal.modal(value ? 'show' : 'hide');

                if (value) {
                    form.action = this.action(value);
                }
            });

            modal.on('hide.bs.modal', () => {
                this.open = null;
            });

            form.onsuccess = (data) => {
                this.onsuccess.call(this.$root, data);

                $(form.node).get(0).reset();

                this.open = null;
            };
        }
    }

    /**
     * This class manages Hbox client behavior
     */
    class HBox extends EMV {
        /**
         * Constructor
         * @param {Object} data The initial HBox data
         */
        constructor(data) {
            const elements = data.map((item) => new HBoxElement(item));

            super({
                data : {
                    elements : elements,
                    rootElement : elements[0],
                    // The selected folder, used to display the content of the folder
                    selectedFolder : elements[0],
                    // The element that is renamning / deleting
                    processingElement : null,
                    openFiles : [],
                    sortOption : {
                        field : 'name',
                        order : 1
                    },
                    // Definition of all dialog forms properties
                    dialogs : {
                        //  Form to upload a new file
                        uploadFile : new HBoxDialogForm({
                            form : 'hbox-upload-file-form',
                            open : false,
                            files : [],
                            extract : false,
                            computed : {
                                value : function() {
                                    return this.files[0];
                                },
                                canExtract : function() {
                                    if(this.files.length !== 1) {
                                        return false;
                                    }

                                    const file = this.files[0];

                                    return ['zip'].indexOf(file.split('.').pop()) !== -1;
                                }
                            },
                            selectFile : (form, event) => {
                                form.files = Array.from(event.target.files).map((file) => {
                                    return file.name;
                                });
                            },
                            action : function() {
                                this.files = [];
                                this.extract = false;
                                return app.getUri('h-box-upload-file', {
                                    folderId : this.$root.selectedFolder.id
                                });
                            },
                            onsuccess : (data) => {
                                data.elements.forEach((data) => {
                                    var element = this.selectedFolder.children().find((child) => {
                                        return child.id === data.id;
                                    });

                                    if(element) {
                                        // Update the element
                                        element.setData(data);
                                    }
                                    else {
                                        // Create a new element
                                        element = new HBoxElement(data);

                                        this.elements.push(element);
                                    }
                                });

                                // Update the parent folder mtime
                                this.selectedFolder.setData(data.folder);
                            }
                        }),

                        // Form to create a new folder
                        newFolder : new HBoxDialogForm({
                            form : 'hbox-create-folder-form',
                            open : false,
                            action : () => {
                                return app.getUri('h-box-create-folder', {
                                    folderId : this.selectedFolder.id
                                });
                            },

                            onsuccess : (data) => {
                                data.created.type = 'folder';

                                const element = new HBoxElement(data.created);

                                element.parentId = this.selectedFolder.id;

                                this.elements.push(element);
                                // Update the mtime of the parent folder
                                this.selectedFolder.setData(data.parentFolder);

                                // Display the new created folder
                                this.selectedFolder = element;
                            }
                        }),

                        // Form to rename a file / a folder
                        renameElement : new HBoxDialogForm({
                            form : 'hbox-rename-element-form',
                            name : '',
                            open : false,
                            action : (element) => {
                                this.processingElement = element;

                                this.dialogs.renameElement.name = element.name;

                                return app.getUri('h-box-rename-element', {
                                    elementId : element.id
                                });
                            },
                            onsuccess : (data) => {
                                this.processingElement.setData(data);

                                this.dialogs.renameElement.name = '';

                                this.processingElement = null;
                            }
                        }),

                        // Form to confirm an element deletion
                        deleteElement : new HBoxDialogForm({
                            form : 'hbox-delete-element-form',
                            open : false,
                            action : (element) => {
                                this.processingElement = element;

                                return app.getUri('h-box-delete-element', {
                                    elementId : element.id
                                });
                            },
                            onsuccess : (data) => {
                                const element = this.processingElement;
                                const parent = element.parent();
                                const index = this.elements.indexOf(element);

                                parent.setData(data.parentFolder);

                                this.elements.splice(index, 1);

                                this.processingElement = null;
                            }
                        }),

                        // Form to move an element from it current parent folder to a new one
                        moveElement : new HBoxDialogForm({
                            form : 'hbox-move-element-form',
                            open : false,
                            parent : undefined,
                            parentId : undefined,
                            action : (element) => {
                                this.processingElement = element;

                                this.dialogs.moveElement.parent = undefined;
                                this.rootElement.developedInMoveForm = true;

                                return app.getUri('h-box-move-element', {
                                    elementId : element.id
                                });
                            },
                            onsuccess : (data) => {
                                const element = this.processingElement;
                                const oldParent = element.parent();
                                const newParent = this.elements.find((element) => {
                                    return element.id === parseInt(this.dialogs.moveElement.parentId, 10);
                                });

                                oldParent.setData(data.oldParent);

                                this.moveElement(element, newParent);
                                newParent.setData(data.newParent);

                                this.processingElement = null;
                            }
                        }),

                        // Form to share an element
                        shareElement : new HBoxDialogForm({
                            form : 'hbox-share-element-form',
                            open : false,
                            autocompleteUrl : '',
                            label : '',
                            share : [],
                            shareWith : undefined,
                            change : (item) => {
                                var dialog = this.dialogs.shareElement;

                                if(item) {
                                    dialog.share.push({
                                        user : item.label,
                                        rights : {
                                            write : false
                                        }
                                    });
                                    dialog.shareWith = '';
                                }
                            },
                            unshare : (share) => {
                                var dialog = this.dialogs.shareElement;
                                var index = dialog.share.indexOf(share);

                                dialog.share.splice(index, 1);
                            },
                            action : (element) => {
                                this.processingElement = element;
                                var dialog = this.dialogs.shareElement;

                                dialog.autocompleteUrl = app.getUri('h-box-share-element-autocomplete', {
                                    elementId : element.id
                                });

                                dialog.shareWith = '';

                                var share = [];

                                element.shared.forEach(function(item) {
                                    share.push({
                                        user : item.user,
                                        rights : {
                                            write : item.rights.write
                                        }
                                    });
                                });
                                dialog.share = share;

                                dialog.label = Lang.get('h-box.share-form-write-' + element.type + '-label');

                                return app.getUri('h-box-share-element', {
                                    elementId : element.id
                                });
                            },
                            onsuccess : function(data) {
                                this.processingElement.shared = data.shared;

                                this.processingElement = null;
                            }
                        })
                    }
                },
                computed : {
                    rootElement : function() {
                        return this.elements[0];
                    },
                    sortFunction : function() {
                        return this[`sortChilrenBy${this.sortOption.field}`];
                    }
                }
            });

            this.dialogs.moveElement.$watch('parent', (value) => {
                this.dialogs.moveElement.parentId = value && value.hasOwnProperty('id') ? value.id : undefined;
            });

            this.elements.forEach((element) => {
                let parent = this.elements.find((parent) => {
                    return parent.id === element.parentId;
                });

                if(parent) {
                    this.moveElement(element, parent);
                }
            });
        }

        /**
         * Move an element from a container to another
         * @param  {HBoxElement} element The element to move
         * @param  {HBoxElement} parent  The new element container
         */
        moveElement(element, parent) {
            element.parentId = parent.id;
        }


        /**
         * Change the sort options for the explorer
         * @param   {string} field The field to choose as sort option
         */
        selectSortOption(field) {
            var option = this.sortOption;

            if(option.field === field) {
                option.order = option.order === 1 ? -1 : 1;
            }
            else {
                option = {
                    field : field,
                    order : 1
                };
            }

            this.sortOption = option;
        }

        /**
         * Function to sort the element children by name
         * @param   {HBoxElement} elem1 The first children
         * @param   {HBoxElement} elem2 The second childre
         * @returns {int}       -1 if elem1 must be placed before elem2, else 1
         */
        sortChilrenByname(elem1, elem2) {
            if(elem1.type === elem2.type) {
                return elem1.name.toLowerCase() < elem2.name.toLowerCase() ? -1 : 1;
            }

            return elem1.type === 'folder' ? -1 : 1;
        }

        /**
         * Function to sort the element children by mtime
         * @param   {HBoxElement} elem1 The first children
         * @param   {HBoxElement} elem2 The second childre
         * @returns {int}       -1 if elem1 must be placed before elem2, else 1
         */
        sortChildrenBymtime(elem1, elem2) {
            return elem2.mtime - elem2.mtime;
        }


        /**
         * Show the tab that displays the file explorer
         */
        showExplorerTab() {
            $('[href="#hbox-folder-content"]').tab('show');
        }


        /**
         * Select an element
         * @param  {HBoxElement} element The selected element
         */
        selectElement(element) {
            if(element.isFolder) {
                const showFolderContent = (folder) => {
                    var reduce = folder === this.selectedFolder && folder.developed;

                    // Open the folder content
                    this.selectedFolder = folder;

                    this.showExplorerTab();

                    if(reduce) {
                        folder.developed = false;
                    }
                    else {
                        folder.developed = true;
                    }
                };

                if(!element.contentLoaded) {
                    element.loadContent()

                    .done(() => {
                        showFolderContent(element);
                    });
                }
                else {
                    showFolderContent(element);
                }
            }
            else if(this.openFiles.indexOf(element) !== -1) {
                element.showTab();
            }
            else {
                // Open the file in a new tab, to display / edit it
                $.get(app.getUri('h-box-file', {
                    fileId : element.id
                }))
                .done((data) => {
                    this.openFiles.push(element);

                    element.data = data;
                    element.saved = true;
                    element.showTab();
                });
            }
        }

        /**
         * Close a file that is editing
         * @param   {HBoxElement} element The element to close
         * @returns {boolean}             false
         */
        closeFile(element) {
            if(element.saved || confirm(Lang.get('h-box.close-file-confirmation', {filename : element.name}))) {
                var index = this.openFiles.indexOf(element);

                this.openFiles.splice(index, 1);
                element.tabContent = '';

                if (this.openFiles.length) {
                    // Select another files
                    if(this.openFiles[index]) {
                        this.selectElement(this.openFiles[index]);
                    }
                    else {
                        this.selectElement(this.openFiles[index - 1]);
                    }
                }
                else {
                    this.showExplorerTab();
                }
            }

            return false;
        }


        /**
         * Download a file or a folder
         * @param   {HBoxElement} element The file / folder to download
         */
        downloadElement(element) {
            window.open(app.getUri('h-box-download-element', {
                elementId : element.id
            }));
        }


        /**
         * Save a new version of a editable file
         *
         * @param {HBoxElement} element The file to save
         * @returns {boolean} False
         */
        save(element) {
            $.post(
                app.getUri('h-box-file', {
                    fileId : element.id
                }),
                {
                    data : element.data
                }
            )
            .done(function(response, status, xhr) {
                if (xhr.status === 204) {
                    element.saved = true;
                }
                else {
                    app.notify(response.message);
                }
            })
            .fail(function(xhr, status, error) {
                app.notify(error.message);
            });

            return false;
        }
    }

    var data = $('#hbox-all-elements').val();

    $('#hbox-all-elements').remove();

    const hboxManager = new HBox(JSON.parse(data));

    hboxManager.$apply(document.getElementById('hbox-main-page'));
});
