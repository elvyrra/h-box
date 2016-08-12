{assign name="panelContent"}
    <div class="ko-template" id="tree-widget-element">
        <li ko-class="type">
            <div ko-if="isFolder">
                <input type="checkbox" ko-attr="{id : 'develop-folder-' + id}" ko-checked="developed" class="hidden" name="develop" />
                <label ko-attr="{for : id ? 'develop-folder-' + id : ''}">
                    <i class="icon icon-lg icon-fw" ko-class="developed() ? 'icon-caret-down text-primary' : 'icon-caret-right'"></i>
                </label>

                <label class="element-name" ko-click="$root.selectElement.bind($root)" ko-class="{'text-primary' : $root.selectedFolder() === $data}">
                    <i class="icon  icon-fw" ko-class="'icon-' + icon()"></i>
                    <span ko-text="name"></span>
                </label>

                <ul ko-template="{name : 'tree-widget-element', foreach: sortedChildren.name}"></ul>
            </div>

            <div ko-ifnot="isFolder">
                <label class="element-name" ko-click="$root.selectElement.bind($root)">
                    <i class="icon  icon-fw" ko-class="'icon-' + icon()"></i>
                    <span ko-text="name"></span>
                </label>
            </div>
        </li>
    </div>

    <!-- ko with : rootElement -->
    <ul ko-template="{name : 'tree-widget-element'}" class="root-folder"></ul>
    <!-- /ko -->
{/assign}

{panel type="info" icon="folder" content="{$panelContent}" title="{text key='h-box.tree-widget-title'}" id="hbox-tree-widget"}