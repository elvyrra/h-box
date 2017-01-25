<div e-with="$root.selectedFolder">
    <ol class="breadcrumb pull-left">
        <li class="text-primary" e-each="parents()">
            <a e-click="$root.selectElement.bind($root)" class="pointer">${name}</a>
        </li>

        <li class="active">${name}</li>
    </ol>

    <div class="pull-right folder-actions">
        <div class="dropdown">
            {icon icon="ellipsis-v" size="lg" class="pointer text-info" data-toggle="dropdown"}
            <ul class="dropdown-menu dropdown-menu-right">
                <li e-if="id">
                    <a href="#" e-click="$root.dialogs.renameElement.open = $this">
                        {icon icon="pencil" class="text-primary"} {text key='h-box.rename-element-title'}
                    </a>
                </li>

                <li e-if="id">
                    <a href="#" e-click="$root.dialogs.deleteElement.open = $this">
                        {icon icon="trash" class="text-danger"} {text key='h-box.delete-element-title'}
                    </a>
                </li>

                <li e-if="id">
                    <a href="#" e-click="$root.dialogs.moveElement.open = $this">
                        {icon icon="arrows" class="text-warning"} {text key='h-box.move-element-title'}
                    </a>
                </li>

                <li>
                    <a href="#" e-click="$root.downloadElement.bind($root)">
                        {icon icon="download"} {text key='h-box.download-element-title'}
                    </a>
                </li>

                <li e-if="id">
                    <a href="#" e-click="$root.dialogs.shareElement.open = $this" e-show="canShare">
                        {icon icon="share-alt-square" class="text-info"} {text key='h-box.share-element-title'}
                    </a>
                </li>

                <li>
                    <a href="#" e-click="$root.dialogs.newFolder.open = true">
                        {icon icon="folder-open-o" class="text-success"} {text key='h-box.create-folder-title'}
                    </a>
                </li>

                <li>
                    <a href="#" e-click="$root.dialogs.uploadFile.open = true">
                        {icon icon="upload" class="text-success"} {text key='h-box.upload-file-title'}
                    </a>
                </li>
            </ul>
        </div>
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
                    <div class="dropdown pull-right">
                        {icon icon="caret-square-o-down" data-toggle="dropdown" size="lg"}
                        <ul class="dropdown-menu dropdown-menu-right">
                            <li>
                                <a href="#" e-click="function(element) { $root.dialogs.renameElement.open = element; }">
                                    {icon icon="pencil" class="text-primary"} {text key="h-box.rename-element-title"}
                                </a>
                            </li>

                            <li>
                                <a href="#" e-click="function(element) { $root.dialogs.deleteElement.open = element; }">
                                    {icon icon="trash" class="text-danger"} {text key="h-box.delete-element-title"}
                                </a>
                            </li>

                            <li>
                                <a href="#" e-click="function(element) { $root.dialogs.moveElement.open = element; }">
                                    {icon icon="arrows" class="text-warning"} {text key="h-box.move-element-title"}
                                </a>
                            </li>

                            <li>
                                <a href="#" e-click="$root.downloadElement.bind($root)">
                                    {icon icon="download"} {text key="h-box.download-element-title"}
                                </a>
                            </li>

                            <li e-if="canShare">
                                <a href="#" e-click="function(element) { $root.dialogs.shareElement.open = element; }">
                                    {icon icon="share-alt-square" class="text-info"} {text key="h-box.share-element-title"}
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>