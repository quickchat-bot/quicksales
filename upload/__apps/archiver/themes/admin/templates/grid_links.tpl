<style>
    .sw-trash-button {
        content: "";
        width: 16px;
        height: 16px;
        display: inline-block;
        background: url('<{$_swiftpath}>__apps/archiver/themes/__cp/images/icon_trash.png') center;
        background-size: cover;
    }

    .sw-export-button {
        content: "";
        width: 16px;
        height: 16px;
        display: inline-block;
        background: url('<{$_swiftpath}>__apps/archiver/themes/__cp/images/icon_down.png') center;
        background-size: cover;
    }

    div#gridtoolbar ul {
        float: left;
    }

    #gridtoolbar > div.gridtoolbarsub > ul > li > a:hover {
        font-weight: normal;
    }

</style>
<script>
    // move help button to the beginning of the toolbar
    $('#gridtoolbar > div.gridtoolbarsub > ul').find('li > a > i.sw-help-button').closest('li').prependTo('#gridtoolbar > div.gridtoolbarsub > ul');

    // Add placeholder to search box
    $('[id=gridirs').attr('placeholder', 'Type your filter keywords').width('180px');

    var el = $('.sw-disable').closest('a');

    <{if $_row_count == 0}>
    el.addClass('ui-state-disabled').attr('disabled', 'disabled').attr('onclick', '');
    <{/if}>
</script>
