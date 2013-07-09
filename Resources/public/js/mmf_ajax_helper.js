var ipe_target_id = 'ipe_ui_dialog';

function ipe_reload(ipe_hash)
{
    var selector = "[data-ipe-hash='" + ipe_hash + "']";
    ipe_load(selector, Routing.generate('_ipe_doctrine_render', {'ipe_hash': ipe_hash}));
}

function ipe_close_dialog(target_id)
{
    $('#' + target_id).modal('hide');
}


function ipe_load_in_dialog(target_id, url, params)
{
    ipe_load_in_spanned_dialog('', target_id, url, params);
}

function ipe_load_in_spanned_dialog(span_class, target_id, url, data)
{
    selector = '#' + target_id;
    if (!$(selector).length) {
        $('body').append('<div id="' + target_id + '" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>');
    }
    //let us remove any span class
    $(selector).removeClass (function (index, css) {
            return (css.match (/\span\S+/g) || []).join(' ');
        })
        .addClass(span_class)
        .modal()
        .css({
            'width': function () {
                  return ($(this).width() / $(document).width())*100 + '%';
                },
            'left': function () {
                return (100 - (($(this).width() / $(document).width())*100))/2 + '%';
               },
            'margin': 'auto'
        });
  ipe_load(selector, url, data);
}

function ipe_open_dialog_if_close(selector)
{
    $(selector).dialog({
        //autoOpen: false,
        width: Math.min(800,$(window).width()*90/100),
        height: Math.min(600,$(window).height()*90/100),
        modal: true, //if not it would be possible to be input files with same id (entity_name_field)
        //position: { at: "left center"},
        close: function() {
            $(selector).html('');
        }
    });

    if (!$(selector).dialog('isOpen')) {
        $(selector).dialog('open');
    }
}



function ipe_block(selector)
{
    $(selector).block({
                message: '<img src="{{ asset('bundles/muchomasfacilinpageedit/images/ajax_loader.gif') }}">', ////////////////////
                css: { border: '0px', background: 'transparent' }
            });
}

function ipe_unblock(selector)
{
    $(selector).unblock();
}

function ipe_load(selector, url, params)
{
    ipe_block(selector);
    $(selector).load(url, params, function(response, status, xhr) {
      ipe_unblock(selector);
      if (status == "error") {
        var msg = "{{ 'ajax_error'|trans({}, 'mmf_ipe') }}: "; ///////////////////////////
        $(selector).html(msg + xhr.status+ " " + xhr.statusText);
      }
    });
}

function ipe_form_submit(form_selector, target_id, route, params )
{
    //using malsup jquery form plugin
    // prepare Options Object
    var options = {
        target:   '#' + target_id
        ,url:     Routing.generate(route, params)
        ,beforeSubmit: function(formData, jqForm, options) {
            //ipe_block('#' + target_id);
            ipe_block(form_selector);
            // here we could return false to prevent the form from being submitted;
            // returning anything other than false will allow the form submit to continue
            return true;
        }
        ,success: function (responseText, statusText, xhr, $form) {
            //ipe_unblock('#' + target_id);
            ipe_unblock(form_selector);
            if (statusText == "error") {
                var msg = "{{ 'ajax_error'|trans({}, 'mmf_ipe') }}: ";
                $('#' + target_id).html(msg + xhr.status+ " " + xhr.statusText);
            }
        }
    };
    $(form_selector).ajaxSubmit(options);
    $(form_selector).submit(options);

}

function ipe_form_reset(form_selector) {
    $(form_selector).each(function(){
            this.reset();
    });
}

$(function() {
    $( "[data-ipe-hash]" ).dblclick(function() {
        ipe_load_in_dialog (ipe_target_id, Routing.generate('ipe_doctrine_edit_index', {'ipe_hash': $(this).attr('data-ipe-hash')}));
    });

});
