<div e-if="typeof data === 'object'" class="hbox-zip-editor">
    <h3>{text key="h-box.zip-file-content-title"}</h3>

    <template id="archive-content-element">
        <label for="h-box-develop-zip-${uid}" e-if="type === 'folder'">
            <i class="icon icon-lg" e-class="{'icon-caret-right' : !developed, 'icon-caret-down' : developed}"></i>
        </label>
        <input type="checkbox" id="h-box-develop-zip-${uid}" e-if="type === 'folder'" e-value="developed" />

        <label for="h-box-develop-zip-${uid}" class="zip-filename pointer">
            <i class="icon icon-${type === 'file' ? 'file' : 'folder-open'}"></i> ${name}
        </label>

        <div e-if="type === 'folder'">
            <ul e-show="developed">
                <li e-each="{$data : content, $sort : 'name'}" e-class="type">
                    <div e-template="'archive-content-element'"></div>
                </li>
            </ul>
        </div>
    </template>

    <ul class="zip-root">
        <li e-each="{$data : data, $sort : 'name'}" e-class="type" >
            <div e-template="'archive-content-element'"></div>
        </li>
    </ul>
</div>
<div e-unless="typeof data === 'object'">
    <div class="alert alert-danger">
        {icon icon="exclamation-circle"} ${data}
    </div>
</div>