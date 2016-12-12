{assign name="formContent"}
    <div>
        {{ $form->inputs['files[]'] }}

        <div e-if="canExtract">
            {{ $form->inputs['extract'] }}
        </div>
    </div>

    <div class="clearfix"></div>

    {{ $form->fieldsets['submits'] }}
{/assign}

{form id="hbox-upload-file-form" content="{$formContent}"}