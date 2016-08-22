<table ko-visible="share().length" class="table">
    <tr>
        <th>{text key="h-box.sharing-table-with-label"}</th>
        <th>{text key="h-box.sharing-table-write-label"}</th>
        <th></th>
    </tr>

    <!-- ko foreach: share -->
    <tr>
        <td ko-text="user"></td>
        <td>
            <input type="hidden" name="users[]" ko-value="user" />
            <input type="checkbox" ko-attr="{id : 'hbox-share-canwrite-' + user, name: 'canWrite[' + user + ']'}" ko-checked="rights.write"/>
            <label ko-attr="{for : 'hbox-share-canwrite-' + user}" class="checkbox-icon"></label>
        </td>
        <td>
            <i class="icon icon-chain-broken text-danger pointer" ko-click="$parent.unshare.bind($parent)" title="{text key='h-box.remove-sharing-title'}"></i>
        </td>
    </tr>
    <!-- /ko -->
</table>
