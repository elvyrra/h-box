<input type="hidden" id="hbox-all-elements" value="{{ $allElements }}" />

<!-- ko with: $root.selectedFolder -->
<ol class="breadcrumb pull-left">
    <!-- ko foreach: parents() -->
    <li class="text-primary">
        <a ko-click="$root.selectElement.bind($root)" ko-text="name" class="pointer"></a>
    </li>
    <!-- /ko -->

    <li class="ative" ko-text="name"></li>
</ol>
<!-- /ko -->

<div class="pull-right folder-actions">
    <!-- ko with: $root.selectedFolder -->
        <span ko-if="id">
            <span class="icon-stack icon pointer" ko-click="function(element) { $root.dialogs.renameElement.open(element); }">
                {icon icon="pencil" size="stack-2x" title="{text key='h-box.rename-element-title'}"}
            </span>

            <span class="icon-stack icon pointer" ko-click="function(element) { $root.dialogs.deleteElement.open(element); }">
                {icon icon="times" size="stack-2x" title="{text key='h-box.delete-element-title'}"}
            </span>

            <span class="icon-stack icon pointer" ko-click="function(element) { $root.dialogs.moveElement.open(element); }">
                {icon icon="arrows" size="stack-2x" title="{text key='h-box.move-element-title'}"}
            </span>

            <span class="icon-stack icon pointer" ko-click="function(element) { $root.dialogs.shareElement.open(element); }" ko-visible="canShare">
                {icon icon="share-alt-square" size="stack-2x" title="{text key='h-box.share-element-title'}"}
            </span>
        </span>

        <span class="icon-stack icon pointer" ko-click="$root.downloadElement.bind($root)">
            {icon icon="download" size="stack-2x" title="{text key='h-box.download-element-title'}"}
        </span>
    <!-- /ko -->

    <span class="icon-stack icon pointer" ko-click="function() { dialogs.newFolder.open(true); }" title="{text key='h-box.create-folder-title'}">
        {icon icon="folder-open-o" size="stack-2x"}
        {icon icon="plus" size="lg" class="pull-left text-primary"}
    </span>

    <span class="icon-stack icon pointer" ko-click="function() { dialogs.uploadFile.open(true); }" title="{text key='h-box.upload-file-title'}">
        {icon icon="file-o" size="stack-2x" }
        {icon icon="plus-circle" size="lg" class="pull-left"}
    </span>

</div>

<div class="clearfix"></div>

<table class="list table table-hover">
    <thead>
        <!-- FIRST LINE, CONTAINING THE LABELS OF THE FIELDS AND THE SEARCH AND SORT OPTIONS -->
        <tr class="list-title-line" >
            <th></th>
            <th class="list-column-title" >
                <div class="list-label-sorts pointer" ko-click="function() {selectSortOption('name')}">
                    <span class="list-title-label list-title-label-name pull-left" title="{text key='h-box.folder-content-name-label'}">{text key='h-box.folder-content-name-label'}</span>
                    <!-- ko if : sortOption().field === 'name' -->
                    <i class="icon" ko-class="sortOption().order === 1 ? 'icon-caret-up' : 'icon-caret-down'"></i>
                    <!-- /ko -->
                </div>
            </th>
            <th class="list-column-title" >
                <div class="list-label-sorts pointer" ko-click="function() {selectSortOption('mtime')}">
                    <span class="list-title-label list-title-label-name pull-left" title="{text key='h-box.folder-content-mtime-label'}" >{text key='h-box.folder-content-mtime-label'}</span>
                    <!-- ko if : sortOption().field === 'mtime' -->
                    <i class="icon" ko-class="sortOption().order === 1 ? 'icon-caret-up' : 'icon-caret-down'"></i>
                    <!-- /ko -->
                </div>
            </th>
            <th class="list-column-title" >
                <div class="list-label-sorts">
                </div>
            </th>
        </tr>
    </thead>

    <!-- THE CONTENT OF THE LIST RESULTS -->
    <tbody>
        <!-- ko foreach: selectedFolderContent -->
        <tr class="list-line" ko-value="id">
            <td>
                {icon icon="share-alt"
                    class="text-primary"
                    ko-visible="shared().length"
                    title="{text key='h-box.shared-element-title'}"
                }
            </td>
            <td class="list-cell list-cell-name pointer" ko-click="$root.selectElement.bind($root)">
                <i class="icon" ko-class="'icon-' + icon()"></i>
                <span ko-text="name"></span>
            </td>
            <td class="list-cell list-cell-mtime">
                <span ko-text="mtime"></span> (<span ko-text="modifiedBy"></span>)
            </td>
            <td class="list-cell list-cell-actions">
                {icon icon="pencil"
                    class="text-primary"
                    title="{text key='h-box.rename-element-title'}"
                    ko-click="function(element) { $root.dialogs.renameElement.open(element); }"
                }


                {icon icon="times"
                    class="text-danger"
                    title="{text key='h-box.delete-element-title'}"
                    ko-click="function(element) { $root.dialogs.deleteElement.open(element); }"
                }

                {icon icon="arrows"
                    class="text-warning"
                    title="{text key='h-box.move-element-title'}"
                    ko-click="function(element) { $root.dialogs.moveElement.open(element); }"
                }

                {icon icon="download"
                    title="{text key='h-box.download-element-title'}"
                    ko-click="$root.downloadElement.bind($root)"
                }

                {icon icon="share-alt-square"
                    class="text-info"
                    title="{text key='h-box.share-element-title'}"
                    ko-visible="canShare"
                    ko-click="function(element) { $root.dialogs.shareElement.open(element); }"
                }
            </td>
        </tr>
        <!-- /ko -->
    </tbody>
</table>



