<!-- ko with: processingElement -->
<p> {text key='h-box.move-element-form-intro'} </p>
<!-- /ko -->

<!-- ko with : rootElement -->
<ul ko-template="'move-element-tree'" class="root-folder"></ul>
<!-- /ko -->

<div class="ko-template" id="move-element-tree">
    <li ko-visible="isFolder && $data !== $root.processingElement()">
        <input type="checkbox" ko-attr="{id : 'move-develop-folder-' + $data.id}" class="hidden" name="develop" ko-checked="developedInMoveForm"/>
        <label ko-attr="{for : id ? 'move-develop-folder-' + id : ''}">
            <i class="icon icon-lg icon-fw" ko-class="developedInMoveForm() ? 'icon-caret-down' : 'icon-caret-right'"></i>
        </label>

        <input type="radio" name="parent" ko-checked="$root.dialogs.moveElement.parent" ko-checkedValue="$data" ko-attr="{id : 'move-select-folder-' + id}"/>
        <label class="element-name" ko-attr="{for : 'move-select-folder-' + id}" ko-class="{'text-primary bold' : $root.dialogs.moveElement.parentId() === id}">
            <i class="icon icon-fw" ko-class="'icon-' + icon()"></i>
            <span ko-text="name"></span>
        </label>

        <ul ko-template="{name : 'move-element-tree', foreach: sortedChildren.name}"></ul>
    </li>
</div>
