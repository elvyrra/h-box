{assign name="panelContent"}
    <template id="tree-widget-element">
        <div e-unless="isFolder">
            <label class="element-name pointer" e-click="$root.selectElement.bind($root)">
                <i class="icon icon-fw" e-class="'icon-' + icon"></i> ${name}
            </label>
        </div>
        <div e-if="isFolder">
            <input type="checkbox" e-attr="{id : 'develop-folder-' + id}" e-value="developed" class="hidden" name="develop" />
            <label e-attr="{for : id ? 'develop-folder-' + id : ''}">
                <i class="icon icon-lg icon-fw" e-class="developed ? 'icon-caret-down text-primary' : 'icon-caret-right'"></i>
            </label>

            <label class="element-name pointer" e-click="$root.selectElement.bind($root)" e-class="{'text-primary' : $root.selectedFolder === $this}">
                <i class="icon icon-fw" e-class="{'icon-folder-open' : developed, 'icon-folder' : !developed}"></i> ${name}
            </label>

            <ul>
                <li e-unless="contentLoaded" class="text-center"><i class="icon icon-spinner text-center icon-spin"></i></li>
                <li e-each="{$data : children(), $sort : $root.sortChilrenByname, $order : 1}" e-class="type">
                    <div e-template="'tree-widget-element'"></div>
                </li>
            </ul>
        </div>
    </template>

    <ul e-with="rootElement" class="root-folder">
        <li e-class="type">
            <div e-template="'tree-widget-element'"></div>
        </li>
    </ul>
{/assign}

{panel type="info" icon="folder" content="{$panelContent}" title="{text key='h-box.tree-widget-title'}" id="hbox-tree-widget"}