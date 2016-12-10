<div e-with="$root.selectedFolder">
    <ol class="breadcrumb pull-left">
        <li class="text-primary" e-each="parents()">
            <a e-click="$root.selectElement.bind($root)" class="pointer">${name}</a>
        </li>

        <li class="active">${name}</li>
    </ol>

    <div class="pull-right folder-actions">
        <span e-if="id">
            <span class="icon-stack icon pointer" e-click="$root.dialogs.renameElement.open = $this">
                {icon icon="pencil" size="stack-2x" title="{text key='h-box.rename-element-title'}"}
            </span>

            <span class="icon-stack icon pointer" e-click="$root.dialogs.deleteElement.open = $this">
                {icon icon="times" size="stack-2x" title="{text key='h-box.delete-element-title'}"}
            </span>

            <span class="icon-stack icon pointer" e-click="$root.dialogs.moveElement.open = $this">
                {icon icon="arrows" size="stack-2x" title="{text key='h-box.move-element-title'}"}
            </span>

            <span class="icon-stack icon pointer" e-click="$root.dialogs.shareElement.open = $this" e-show="canShare">
                {icon icon="share-alt-square" size="stack-2x" title="{text key='h-box.share-element-title'}"}
            </span>
        </span>

        <span class="icon-stack icon pointer" e-click="$root.downloadElement.bind($root)">
            {icon icon="download" size="stack-2x" title="{text key='h-box.download-element-title'}"}
        </span>

        <span class="icon-stack icon pointer" e-click="$root.dialogs.newFolder.open = true" title="{text key='h-box.create-folder-title'}">
            {icon icon="folder-open-o" size="stack-2x"}
            {icon icon="plus" size="lg" class="pull-left text-primary"}
        </span>

        <span class="icon-stack icon pointer" e-click="$root.dialogs.uploadFile.open = true" title="{text key='h-box.upload-file-title'}">
            {icon icon="file-o" size="stack-2x" }
            {icon icon="plus-circle" size="lg" class="pull-left"}
        </span>

    </div>

    <div class="clearfix"></div>

    <table class="list table table-hover">
        <thead>
            <!-- FIRST LINE, CONTAINING THE LABELS OF THE FIELDS AND THE SEARCH AND SORT OPTIONS -->
            <tr class="list-title-line" >
                <th>{icon icon="level-up" size="lg" class="pointer" e-click="$root.selectElement($this.parent())" e-show="id"}</th>
                <th class="list-column-title" >
                    <div class="list-label-sorts pointer" e-click="$root.selectSortOption('name')">
                        <span class="list-title-label list-title-label-name pull-left" title="{text key='h-box.folder-content-name-label'}">{text key='h-box.folder-content-name-label'}</span>

                        <i class="icon" e-if="$root.sortOption.field === 'name'" e-class="$root.sortOption.order === 1 ? 'icon-caret-up' : 'icon-caret-down'"></i>
                    </div>
                </th>
                <th class="list-column-title" >
                    <div class="list-label-sorts pointer" e-click="$root.selectSortOption('mtime')">
                        <span class="list-title-label list-title-label-name pull-left" title="{text key='h-box.folder-content-mtime-label'}" >{text key='h-box.folder-content-mtime-label'}</span>
                        <i class="icon" e-if="$root.sortOption.field === 'mtime'" e-class="$root.sortOption.order === 1 ? 'icon-caret-up' : 'icon-caret-down'"></i>
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
            <tr class="list-line" e-each="{$data : children(), $sort : $root.sortFunction, $order : $root.sortOption.order}">
                <td>
                    {icon icon="share-alt"
                        class="text-primary"
                        e-show="shared.length"
                        title="{text key='h-box.shared-element-title'}"
                    }
                </td>
                <td class="list-cell list-cell-name pointer" e-click="$root.selectElement.bind($root)">
                    <i class="icon icon-${ icon }"></i>
                    <span>${name}</span>
                </td>
                <td class="list-cell list-cell-mtime">
                    ${mtime} (${modifiedBy})
                </td>
                <td class="list-cell list-cell-actions">
                    {icon icon="pencil"
                        class="text-primary"
                        title="{text key='h-box.rename-element-title'}"
                        e-click="function(element) { $root.dialogs.renameElement.open = element; }"
                    }


                    {icon icon="times"
                        class="text-danger"
                        title="{text key='h-box.delete-element-title'}"
                        e-click="function(element) { $root.dialogs.deleteElement.open = element; }"
                    }

                    {icon icon="arrows"
                        class="text-warning"
                        title="{text key='h-box.move-element-title'}"
                        e-click="function(element) { $root.dialogs.moveElement.open = element; }"
                    }

                    {icon icon="download"
                        title="{text key='h-box.download-element-title'}"
                        e-click="$root.downloadElement.bind($root)"
                    }

                    {icon icon="share-alt-square"
                        class="text-info"
                        title="{text key='h-box.share-element-title'}"
                        e-show="canShare"
                        e-click="function(element) { $root.dialogs.shareElement.open = element; }"
                    }
                </td>
            </tr>
        </tbody>
    </table>
</div>