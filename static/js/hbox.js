/* global ko, $, app, Lang */
'use strict';

(function() {
    /**
     * This class describes the behavior of an element in Hbox
     * @param {Object} data  The initial data of the element
     * @param {HBox} manager The hbox manager
     */
    var HBoxElement = function(data, manager) {
        this.manager = manager;

        this.name = ko.observable(data.name);
        this.id = parseInt(data.id, 10);
        this.type = data.type;
        this.ctime = ko.observable(data.ctime);
        this.mtime = ko.observable(data.mtime);
        this.parentId = ko.observable(parseInt(data.parentId, 10));
        this.extension = ko.observable((data.extension || '').toLowerCase());

        this.parent = ko.computed(function() {
            return this.manager.elements().find(function(element) {
                return element.id === this.parentId();
            }.bind(this));
        }.bind(this));

        this.parents = ko.computed(function() {
            if(!this.parent()) {
                return [];
            }

            return this.parent().parents().concat([this.parent()]);
        }.bind(this));

        this.developed = ko.observable(!this.id);

        this.developedInMoveForm = ko.observable(!this.id);

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


        this.children = ko.computed(function() {
            return this.manager.elements().filter(function(element) {
                return element.parentId() === this.id;
            }.bind(this));
        }.bind(this));

        // this.parent.subscribe(function(oldValue) {
        //     this.oldParent = oldValue;
        // }, this, 'beforeChange');

        // this.parent.subscribe(function(newParent) {
        //     if(this.oldParent) {
        //         this.oldParent.getChildren();

        //         delete this.oldParent;
        //     }

        //     newParent.getChildren();
        // }.bind(this));

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


    HBoxElement.prototype.showTab = function() {
        $('[data-toggle="tab"][href="#' + this.tab.id + '"]').tab('show');
    };

    // HBoxElement.prototype.getChildren = function() {
    //     var currentChildren = this.children();

    //     this.manager.
    //     this.children(
    //         this.manager.elements().filter(function(element) {
    //             return element.parentId() === this.id;
    //         }.bind(this))
    //     );
    // };


    var HBox = function(data) {
        debugger;
        this.elements = ko.observableArray([]);

        data.forEach(function(element) {
            this.addElement(new HBoxElement(element, this));
        }.bind(this));

        // this.elements().forEach(function(element) {
        //     element.getChildren();
        // });

        this.rootElement = this.elements()[0];

        // The element that is renamning / deleting
        this.processingElement = ko.observable(null);

        // Manage the dialog forms
        this.dialogs = {
            //  Form to upload a new file
            uploadFile : {
                form : app.forms['hbox-upload-file-form'],
                action : function() {
                    return app.getUri('h-box-upload-file', {
                        folderId : this.selectedFolder().id
                    });
                }.bind(this),
                onsuccess : function(elements) {
                    elements.forEach(function(element) {
                        element.type = 'file';

                        this.addElement(new HBoxElement(element, this));
                    }.bind(this));

                    return false;
                }.bind(this),
                open : ko.observable(false)
            },

            // Form to create a new folder
            newFolder : {
                form : app.forms['hbox-create-folder-form'],
                open : ko.observable(false),

                onsuccess : function(data) {
                    data.type = 'folder';

                    this.addElement(new HBoxElement(data, this));
                }.bind(this),

                action : function() {
                    return app.getUri('h-box-create-folder', {
                        folderId : this.selectedFolder().id
                    });
                }.bind(this)
            },

            // Form to rename a file / a folder
            renameElement : {
                form : app.forms['hbox-rename-element-form'],
                name : ko.observable(''),
                open : ko.observable(false),
                onsuccess : function(data) {
                    this.processingElement().name(data.name);

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
                onsuccess : function() {
                    var element = this.processingElement(),
                        index = this.elements().indexOf(element);

                    this.elements.splice(index, 1);

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
                onsuccess : function() {
                    var element = this.processingElement(),
                        parent = this.dialogs.moveElement.parent();

                    element.parentId(parent.id);

                    this.selectedFolder(parent);

                    this.processingElement(null);

                    return false;
                }.bind(this)
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


    HBox.prototype.addElement = function(element) {
        this.elements.push(element);

        // element.parent().getChildren();
    };

    HBox.prototype.removeElement = function(element) {


        // parent.getChildren();
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

    var hboxManager = new HBox(JSON.parse($('#hbox-all-elements').val()));

    // ko.applyBindings(hboxManager, document.getElementById('hbox-tree-widget'));
    // ko.applyBindings(hboxManager, document.getElementById('hbox-page-content'));
    ko.applyBindings(hboxManager, document.getElementById('hbox-main-page'));

    window.hbox  = hboxManager;
})();