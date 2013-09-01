$(function() {
    $.htmlAjaxHelper('dblclick', '[data-ipe-hash]');
    $.htmlAjaxHelper('click', 'a[data-hah-action], button[data-hah-action]');

    $('#title_tag_edit_link').click(function(event) {
        event.preventDefault();
        $('#title_tag_container').dblclick();
    });
    $('#meta_tags_edit_link').click(function(event) {
        event.preventDefault();
        $('#meta_tags_container').dblclick();
    });

});
