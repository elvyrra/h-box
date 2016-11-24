<p e-with="processingElement"> {text key='h-box.move-element-form-intro'} </p>

<ul e-with="rootElement" class="root-folder">
    <li e-template="'move-element-tree'"></li>
</ul>

<template id="move-element-tree">
    <input type="checkbox" id="move-develop-folder-${id}" class="hidden" name="develop" e-value="developedInMoveForm"/>
    <label for="move-develop-folder-${id}" >
        <i class="icon icon-lg icon-fw" e-class="developedInMoveForm ? 'icon-caret-down' : 'icon-caret-right'"></i>
    </label>

    <input type="radio" name="parent" e-value="$root.dialogs.moveElement.parentId" value="${id}" id="move-select-folder-${id}"/>
    <label class="element-name" for="move-select-folder-${id}" e-class="{'text-primary bold' : $root.dialogs.moveElement.parentId === id}">
        <i class="icon icon-fw icon-${ icon }"></i>
        <span>${name}</span>
    </label>

    <ul>
        <li e-each="{$data : children(), $sort : sortChilrenByname, $filter : function(elem) { return elem.isFolder && elem !== $root.processingElement }}">
            <div e-template="'move-element-tree'"></div>
        </li>
    </ul>
</template>
