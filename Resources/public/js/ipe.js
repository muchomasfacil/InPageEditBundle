function ipe_reload(ipe_hash, block_id_selector)
{
    var selector = "[data-ipe-hash='" + ipe_hash + "']";
    var element = $(selector);
    if (!block_id_selector) {
        block_id_selector = selector;
    }
    $.hahLoad(id_selector, element.attr('data-hah-url'), element.attr('data-hah-data'), block_id_selector);
}

function ipe_remove(ipe_hash)
{
    $("[data-ipe-hash='" + ipe_hash + "']").remove();
}

//var ipe_target_id = 'ipe_dialog';

$(function() {   
    $.htmlAjaxHelper('dblclick', '[data-ipe-hash]');        
    $.htmlAjaxHelper('click', 'a[data-hah-action], button[data-hah-action]');            
});
