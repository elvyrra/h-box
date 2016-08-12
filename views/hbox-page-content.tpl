<div role="tabpanel" id="hbox-tabs">
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" role="tablist">
        <li role="presentation" class="actve" ko-style="{ 'max-width': 'calc(90% / ' + (openFiles().length  + 1) + ' - 2px )' }">
            <a role="tab" data-toggle="tab" href="#hbox-folder-content">
                {text key="h-box.tabs-folder-content-title"}
            </a>
        </li>

        <!-- ko foreach: openFiles -->
        <li role="presentation" ko-style="{ 'max-width': 'calc(90% / ' + ($parent.openFiles().length + 1) + ' - 2px )' }" ko-attr="{title : name}">
            <a role="tab" data-toggle="tab" ko-attr="{ href: '#' + tab.id }">
                <i class="icon" ko-class="'icon-' + icon()"></i>
                <span ko-text="name"></span>

                <span class="tab-action">
                    <!-- ko if: !saved() -->
                        {icon icon="circle" class="not-saved pull-right"}
                    <!-- /ko -->
                    {icon icon="times-circle" class="pull-right close-file" ko-click="$root.closeFile.bind($root)"}
                </span>
            </a>
        </li>
        <!-- /ko -->
    </ul>

    <!-- Tab panes -->
    <div class="tab-content">
        <div role="tabpanel" class="tab-pane active" id="hbox-folder-content">
            {import file="folder-content.tpl"}
        </div>

        <!-- ko foreach: openFiles -->
        <div role="tabpanel" class="tab-pane file-editor" ko-attr="{ id : tab.id }" ko-template="'hbox-editor-template-' + template()" ko-class="'file-editor-' + template()"></div>
        <!-- /ko -->
    </div>
</div>

<!-- Modal forms -->
{foreach($dialogForms as $name => $form)}
    <div class="modal fade dialog-form">{{ $form }}</div>
{/foreach}

<!-- Editors templates -->
{foreach($editorTemplates as $name => $template)}
    <div class="ko-template" id="hbox-editor-template-{{ $name }}">
        {{ $template }}
    </div>
{/foreach}
