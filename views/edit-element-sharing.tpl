<table e-show="share.length" class="table">
    <tr>
        <th>{text key="h-box.sharing-table-with-label"}</th>
        <th>{text key="h-box.sharing-table-write-label"}</th>
        <th></th>
    </tr>

    <tr e-each="share">
        <td>${user}</td>
        <td>
            <input type="hidden" name="users[]" e-value="user" />
            <input type="checkbox" id="hbox-share-canwrite-${user}" name="canWrite[${user}]" e-value="rights.write"/>
            <label for="hbox-share-canwrite-${user}" class="checkbox-icon"></label>
        </td>
        <td>
            <i class="icon icon-chain-broken text-danger pointer" e-click="$parent.$parent.unshare" title="{text key='h-box.remove-sharing-title'}"></i>
        </td>
    </tr>
</table>
