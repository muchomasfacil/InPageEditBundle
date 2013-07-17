function ipe_reload(ipe_hash)
{
    var selector = "[data-ipe-hash='" + ipe_hash + "']";
    mmf_load(selector, Routing.generate('_ipe_action', {'action': 'ajax-render','ipe_hash': ipe_hash}));
}

function ipe_remove(ipe_hash)
{
    var selector = "[data-ipe-hash='" + ipe_hash + "']";
    $(selector).remove();
}

var ipe_target_id = 'ipe_dialog';

$(function() {
    $( "[data-ipe-hash]" ).dblclick(function() {
        mmf_load_in_dialog (ipe_target_id, Routing.generate('_ipe_action', {'action': 'edit', 'ipe_hash': $(this).attr('data-ipe-hash')}));
    });

});
