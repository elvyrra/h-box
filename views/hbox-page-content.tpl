<div role="tabpanel" id="hbox-tabs">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="actve" e-style="{ 'max-width': 'calc(90% / ' + (openFiles.length  + 1) + ' - 2px )' }">
            <a role="tab" data-toggle="tab" href="#hbox-folder-content">
                {text key="h-box.tabs-folder-content-title"}
            </a>
        </li>

        <li role="presentation" e-each="openFiles" e-style="{ 'max-width': 'calc(90% / ' + ($parent.openFiles.length + 1) + ' - 2px )' }" title="${name}">
            <a role="tab" data-toggle="tab" e-attr="{href : '#' + tabId}">
                <i class="icon icon-${ icon }"></i> ${name}

                <span class="tab-action">
                    {icon icon="circle" class="not-saved pull-right" e-unless="saved"}
                    {icon icon="times-circle" class="pull-right close-file" e-click="$root.closeFile.bind($root)"}
                </span>
            </a>
        </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="hbox-folder-content">
            {import file="folder-content.tpl"}
        </div>

        <div e-each="openFiles" role="tabpanel" class="tab-pane file-editor" e-class="'file-editor-' + template" e-attr="{id : tabId}" e-template="'hbox-editor-template-' + template"></div>
    </div>
</div>

<!-- Modal forms -->
{foreach($dialogForms as $name => $form)}
    <div class="modal fade dialog-form">
        <div class="modal-backdrop fade"></div>
        <div class="modal-dialog center">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">&times;</button>
                    <h4 class="modal-title">
                        {icon icon="{$form['icon']}"} {{ $form['title'] }}
                    </h4>
                </div>
                <div class="modal-body">{{ $form['page'] }}</div>
            </div>
        </div>
    </div>
{/foreach}

<!-- Editors templates -->
{foreach($editorTemplates as $name => $template)}
    <template id="hbox-editor-template-{{ $name }}">
        {{ $template }}
    </template>
{/foreach}

<input type="hidden" id="hbox-all-elements" value="{{ $allElements }}" />