<table class="list table table-hover">
    <thead>
        <!-- FIRST LINE, CONTAINING THE LABELS OF THE FIELDS AND THE SEARCH AND SORT OPTIONS -->
        <tr class="list-title-line" >
            <th class="list-column-title" >
                <div class="list-label-sorts">
                    <span class='list-title-label list-title-label-name pull-left' title="{text key='h-box.folder-content-name-label'}">{text key='h-box.folder-content-name-label'}</span>
                </div>
            </th>
            <th class="list-column-title" >
                <div class="list-label-sorts">
                    <span class='list-title-label list-title-label-name pull-left' title="{text key='h-box.folder-content-mtime-label'}">{text key='h-box.folder-content-mtime-label'}</span>
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
        <!-- ko foreach: displayedElements -->
        <tr class="list-line" ko-value="id">
            <td class="list-cell list-cell-name" ko-text="name"></td>
            <td class="list-cell list-cell-mtime" ko-text="mtime"></td>
            <td class="list-cell list-cell-actions">
                {button icon="pencil"
                    class="btn-primary"
                    title="{text key='h-box.rename-element-title'}"
                }


                {button icon="times"
                    class="btn-danger"
                    title="{text key='h-box.delete-element-title'}"
                }
            </td>
        </tr>
    <!-- /ko -->
    </tbody>
</table>



