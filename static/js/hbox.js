/* global ko, $, app, Lang */
'use strict';

(function() {
    /**
     * This class describes the behavior of an element in Hbox
     * @param {Object} data  The initial data of the element
     */
    var HBoxElement = function(data) {
        this.name = ko.observable(data.name);
        this.id = parseInt(data.id, 10);
        this.type = data.type;
        this.ctime = ko.observable(data.ctime);
        this.mtime = ko.observable(data.mtime);
        this.parentId = parseInt(data.parentId, 10);
        this.parent = null;
        this.canShare = data.canShare;
        this.shared = ko.observableArray(data.shared);

        this.writable = ko.observable(data.writable);

        this.modifiedBy = ko.observable(data.modifiedBy);

        this.parents = function() {
            if(!this.parent) {
                return [];
            }

            return this.parent.parents().concat([this.parent]);
        }.bind(this);

        this.developed = ko.observable(!this.id);

        this.extension = ko.computed(function() {
            return this.name().split('.').pop().toLowerCase();
        }.bind(this));

        this.developedInMoveForm = ko.observable(!this.id);
        this.developedInMoveForm.extend({
            notify: 'always'
        });

        this.isFolder = this.type === 'folder';

        this.fileType = ko.computed(function() {
            if(this.isFolder) {
                return null;
            }

            for(var fileType in HBoxElement.fileTypes) {
                if(HBoxElement.fileTypes.hasOwnProperty(fileType)) {
                    var typeData = HBoxElement.fileTypes[fileType];

                    if(typeData.extensions.indexOf(this.extension()) !== -1) {
                        return fileType;
                    }
                }
            }

            return 'default';
        }.bind(this));

        this.icon = ko.computed(function() {
            if(this.isFolder) {
                return 'folder-open';
            }

            return HBoxElement.fileTypes[this.fileType()].icon;
        }.bind(this));

        this.template = ko.computed(function() {
            if(this.isFolder) {
                return null;
            }

            return HBoxElement.fileTypes[this.fileType()].template;
        }.bind(this));


        this.children = ko.observableArray([]);

        this.sortedChildren = {
            name :  ko.computed(function() {
                var copy = this.children().slice();

                copy.sort(function(elem1, elem2) {
                    if(elem1.type === elem2.type) {
                        return elem1.name().toLowerCase() < elem2.name().toLowerCase() ? -1 : 1;
                    }

                    return elem1.type === 'folder' ? -1 : 1;
                });

                return copy;
            }.bind(this)),

            mtime : ko.computed(function() {
                var copy = this.children().slice();

                copy.sort(function(elem1, elem2) {
                    return elem1.mtime() < elem2.mtime() ? -1 : 1;
                });

                return copy;
            }.bind(this))
        };

        this.saved = ko.observable(true);

        this.tab = {
            id : 'hbox-tab-file-' + this.id,
            content : ko.observable('')
        };

        // Contains the data used to display the file
        this.data = ko.observable();

        this.data.subscribe(function() {
            this.saved(false);
        }.bind(this));
    };

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
            ]
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
            template : null,
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
     * Display the tab containing the file editor
     */
    HBoxElement.prototype.showTab = function() {
        $('[data-toggle="tab"][href="#' + this.tab.id + '"]').tab('show');
    };


    /**
     * Update element data
     * @param {Object} data The data to update on the element
     */
    HBoxElement.prototype.setData = function(data) {
        this.name(data.name || this.name());
        this.mtime(data.mtime || this.mtime());
        this.modifiedBy(data.modifiedBy || this.modifiedBy());
        this.shared(data.shared || this.shared());
    };


    HBoxElement.prototype.share = function(user, rights) {
        var existingSharing = this.shared().find(function(share) {
            return share.user === user;
        });

        if(!existingSharing) {
            this.shared.push({
                user : user,
                rights : {
                    write : ko.observable(rights.write)
                }
            });
        }
    };


    var HBox = function(data) {
        var elements = [];

        data.forEach(function(item) {
            elements.push(new HBoxElement(item));
        });

        elements.forEach(function(parent) {
            elements.forEach(function(child) {
                if(child.parentId === parent.id) {
                    this.moveElement(child, parent);
                }
            }.bind(this));
        }.bind(this));

        this.rootElement = elements[0];

        // The element that is renamning / deleting
        this.processingElement = ko.observable(null);

        this.processingElement.extend({
            notify : 'always'
        });

        // Manage the dialog forms
        this.dialogs = {
            //  Form to upload a new file
            uploadFile : {
                form : app.forms['hbox-upload-file-form'],
                open : ko.observable(false),
                action : function() {
                    return app.getUri('h-box-upload-file', {
                        folderId : this.selectedFolder().id
                    });
                }.bind(this),
                onsuccess : function(elements) {
                    elements.elements.forEach(function(data) {
                        data.type = 'file';

                        var element = this.selectedFolder().children().find(function(child) {
                            return child.id === data.id;
                        });

                        if(element) {
                            element.mtime(data.mtime);
                        }
                        else {
                            element = new HBoxElement(data);
                        }

                        this.moveElement(element, this.selectedFolder());
                    }.bind(this));

                    this.selectedFolder().setData(elements.folder);
                }.bind(this)
            },

            // Form to create a new folder
            newFolder : {
                form : app.forms['hbox-create-folder-form'],
                open : ko.observable(false),

                action : function() {
                    return app.getUri('h-box-create-folder', {
                        folderId : this.selectedFolder().id
                    });
                }.bind(this),

                onsuccess : function(data) {
                    data.created.type = 'folder';

                    var element = new HBoxElement(data.created);

                    this.moveElement(element, this.selectedFolder());

                    // Update the mtime of the parent folder
                    this.selectedFolder().setData(data.parentFolder);
                }.bind(this)
            },

            // Form to rename a file / a folder
            renameElement : {
                form : app.forms['hbox-rename-element-form'],
                name : ko.observable(''),
                open : ko.observable(false),
                onsuccess : function(data) {
                    this.processingElement().setData(data);

                    this.processingElement(null);
                }.bind(this),
                action : function(element) {
                    this.processingElement(element);

                    this.dialogs.renameElement.name(element.name());

                    return app.getUri('h-box-rename-element', {
                        elementId : element.id
                    });
                }.bind(this)
            },

            // Form to confirm an element deletion
            deleteElement : {
                form : app.forms['hbox-delete-element-form'],
                open : ko.observable(false),
                action : function(element) {
                    this.processingElement(element);

                    return app.getUri('h-box-delete-element', {
                        elementId : element.id
                    });
                }.bind(this),
                onsuccess : function(data) {
                    var element = this.processingElement(),
                        parent = element.parent,
                        index = parent.children().indexOf(element);

                    parent.children.splice(index, 1);

                    parent.setData(data.parentFolder);

                    this.processingElement(null);
                }.bind(this)
            },

            // Form to move an element from it current parent folder to a new one
            moveElement : {
                form : app.forms['hbox-move-element-form'],
                open : ko.observable(false),
                parent : ko.observable(),
                parentId : ko.observable(),
                action : function(element) {
                    this.processingElement(element);

                    this.dialogs.moveElement.parent(undefined);
                    this.rootElement.developedInMoveForm(true);

                    return app.getUri('h-box-move-element', {
                        elementId : element.id
                    });
                }.bind(this),
                onsuccess : function(data) {
                    var element = this.processingElement(),
                        parent = this.dialogs.moveElement.parent();

                    element.parent.setData(data.oldParent);
                    this.moveElement(element, parent);
                    parent.setData(data.newParent);

                    this.processingElement(null);
                }.bind(this)
            },

            // Form to share an element
            shareElement : {
                form : app.forms['hbox-share-element-form'],
                open : ko.observable(false),
                autocompleteUrl : ko.observable(''),
                label : ko.observable(''),
                share : ko.observableArray([]),
                shareWith : ko.observable(),
                change : function(item) {
                    var dialog = this.dialogs.shareElement;

                    if(item) {
                        dialog.share.push({
                            user : item.label,
                            rights : {
                                write : false
                            }
                        });
                        dialog.shareWith('');
                    }
                }.bind(this),
                unshare : function(share) {
                    var dialog = this.dialogs.shareElement;
                    var index = dialog.share().indexOf(share);

                    dialog.share.splice(index, 1);
                }.bind(this),
                action : function(element) {
                    this.processingElement(element);
                    var dialog = this.dialogs.shareElement;

                    dialog.autocompleteUrl(app.getUri('h-box-share-element-autocomplete', {
                        elementId : element.id
                    }));

                    dialog.shareWith('');

                    var share = [];

                    element.shared().forEach(function(item) {
                        share.push({
                            user : item.user,
                            rights : {
                                write : item.rights.write
                            }
                        });
                    });
                    dialog.share(share);

                    dialog.label(Lang.get('h-box.share-form-write-' + element.type + '-label'));

                    return app.getUri('h-box-share-element', {
                        elementId : element.id
                    });
                }.bind(this),
                onsuccess : function(data) {
                    this.processingElement().shared(data.shared);

                    this.processingElement(null);
                }
            }
        };

        this.dialogs.moveElement.parent.subscribe(function(value) {
            this.dialogs.moveElement.parentId(value && value.hasOwnProperty('id') ? value.id : undefined);
        }.bind(this));

        Object.keys(this.dialogs).forEach(function(formName) {
            var options = this.dialogs[formName],
                form = options.form;

            options.open.extend({
                notify : 'always'
            });

            options.open.subscribe(function(value) {
                $('#' + form.id).parents('.modal').first().modal(value ? 'show' : 'hide');

                if (value) {
                    form.action = options.action(value);
                }
            });

            form.onsuccess = function(data) {
                options.onsuccess.call(this, data);

                $(form.node).get(0).reset();

                options.open(null);
            }.bind(this);
        }.bind(this));

        // The selected folder, used to display the content of the folder
        this.selectedFolder = ko.observable(this.rootElement);

        this.openFiles = ko.observableArray([]);

        this.sortOption = ko.observable({field : 'name', order : 1});

        this.selectedFolderContent = ko.computed(function() {
            var option = this.sortOption(),
                sorted = this.selectedFolder().sortedChildren[option.field]().slice();

            if(option.order === 1) {
                return sorted;
            }

            return sorted.reverse();
        }.bind(this));
    };


    HBox.prototype.moveElement = function(element, parent) {
        var previousParent = element.parent;

        parent.children.push(element);
        element.parent = parent;
        element.parentId = parent.id;

        if(previousParent) {
            var index = previousParent.children().indexOf(element);

            previousParent.children.splice(index, 1);
        }
    };


    /**
     * Change the sort options for the explorer
     * @param   {string} field The field to choose as sort option
     */
    HBox.prototype.selectSortOption = function(field) {
        var option = this.sortOption();

        if(option.field === field) {
            option.order = option.order === 1 ? -1 : 1;
        }
        else {
            option = {
                field : field,
                order : 1
            };
        }

        this.sortOption(option);
    };


    /**
     * Show the tab that displays the file explorer
     */
    HBox.prototype.showExplorerTab = function() {
        $('[href="#hbox-folder-content"]').tab('show');
    };


    /**
     * Select an element
     * @param  {HBoxElement} element The selected element
     */
    HBox.prototype.selectElement = function(element) {
        if(element.isFolder) {
            var reduce = element === this.selectedFolder() && element.developed();

            // Open the folder content
            this.selectedFolder(element);

            this.showExplorerTab();

            if(reduce) {
                element.developed(false);
            }
            else {
                element.developed(true);
            }
        }
        else if(this.openFiles().indexOf(element) !== -1) {
            element.showTab();
        }
        else {
            // Open the file in a new tab, to display / edit it
            $.get(app.getUri('h-box-file', {
                fileId : element.id
            }))
            .done(function(data) {
                this.openFiles.push(element);

                element.data(data);
                element.saved(true);
                element.showTab();
            }.bind(this));
        }
    };

    /**
     * Close a file that is editing
     * @param   {HBoxElement} element The element to close
     * @returns {boolean}             false
     */
    HBox.prototype.closeFile = function(element) {
        if(element.saved() || confirm(Lang.get('h-box.close-file-confirmation', {filename : element.name()}))) {
            var index = this.openFiles().indexOf(element);

            this.openFiles.splice(index, 1);
            element.tab.content('');

            if (this.openFiles().length) {
                // Select another files
                if(this.openFiles()[index]) {
                    this.selectElement(this.openFiles()[index]);
                }
                else {
                    this.selectElement(this.openFiles()[index - 1]);
                }
            }
            else {
                this.showExplorerTab();
            }
        }

        return false;
    };


    /**
     * Download a file or a folder
     * @param   {HBoxElement} element The file / folder to download
     */
    HBox.prototype.downloadElement = function(element) {
        window.open(app.getUri('h-box-download-element', {
            elementId : element.id
        }));
    };


    /**
     * Save a new version of a editable file
     *
     * @param {HBoxElement} element The file to save
     * @returns {boolean} False
     */
    HBox.prototype.save = function(element) {
        $.post(
            app.getUri('h-box-file', {
                fileId : element.id
            }),
            {
                data : element.data()
            }
        )
        .done(function(response, status, xhr) {
            if (xhr.status === 204) {
                element.saved(true);
            }
            else {
                app.notify(response.message);
            }
        })
        .fail(function(xhr, status, error) {
            app.notify(error.message);
        });

        return false;
    };

    var data = $('#hbox-all-elements').val();

    $('#hbox-all-elements').remove();
    var hboxManager = new HBox(JSON.parse(data));


    ko.applyBindings(hboxManager, document.getElementById('hbox-main-page'));

    window.hbox  = hboxManager;
})();