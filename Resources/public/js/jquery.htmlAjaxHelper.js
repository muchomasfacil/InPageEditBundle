/*
 * jQuery htmlAjaxHelper plugin
 * Version 1.0
 * @requires jQuery v1.7 or later
 * @requires jQuery.blockUI.js
 * http://www.malsup.com/jquery/block/
 * Copyright (c) 2013 MUCHOMASFACIL SL
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 */

(function( $ ) {

    $.extend({
        hahBlock: function(block_id_selector, block_message){
            if (!block_message) {
                block_message = 'Loading...';
            }
            options = {
                message: block_message
                ,css: {}
                // to style de block take a look at http://www.malsup.com/jquery/block/#faq
                // specially:
                // How do I use an external stylesheet to style the blocking message?
                // How do I use an external stylesheet to style the blocking overlay?
            };
            if ($(block_id_selector).length) {
                $(block_id_selector).block(options);
            }
            else{
                $.blockUI(options);
            }
        }
    });

    $.extend({
        hahUnblock: function (block_id_selector) {
            if ($(block_id_selector).length) {
                $(block_id_selector).unblock();
            }
            else{
                $.unblockUI();
            }
        }
    });


    $.extend({
        hahGet: function (url, data, block_id_selector, block_message, funcOnSuccess) {
            $.hahBlock(block_id_selector, block_message); //this one blocks
            //var result = null;
            $.get(url, data, function(response, status, xhr) {
                if (status == "error") {
                    var msg = "Sorry but there was an error: ";
                    alert(msg + xhr.status + " " + xhr.statusText);
                }
                $.hahUnblock(block_id_selector); //this one blocks
                funcOnSuccess(response, status, xhr);
            });
        }
    });

    //add_class used to, for example add span8
    $.extend({
        hahLoad: function (id_selector, url, data, block_id_selector, block_message) {
            $.hahBlock(block_id_selector, block_message); //this one blocks
            $(id_selector).load(url, data, function(response, status, xhr) {
                if (status == "error") {
                    var msg = "Sorry but there was an error: ";
                    alert(msg + xhr.status + " " + xhr.statusText);
                }
                $.hahUnblock(block_id_selector); //this one blocks
            });
        }
    });

    //add_class used to, for example add span8
    $.extend({
        hahModalLoad: function (id_selector, url, data, block_id_selector, block_message, modal_add_class) {

            if (!$(id_selector).length) { //if not exists create it
                $('body').append('<div id="' + id_selector.replace("#","") + '" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"></div>');
                //$('body').append('<div id="' + id_selector.replace("#","") + '" class="modal hide"></div>');
            }

            $(id_selector)
                //remove span classes
                .removeClass (function (index, css) {
                    return (css.match (/\span\S+/g) || []).join(' ');
                })
                .addClass(modal_add_class)
                .css({
                    'width': function () {
                          return ($(this).width() / $(document).width())*100 + '%';
                        }
                    ,'left': function () {
                        return (100 - (($(this).width() / $(document).width())*100))/2 + '%';
                       }
                    ,'margin': 'auto'
                });

            // load, Show loading in all page
            $.hahLoad(id_selector, url, data, block_id_selector, block_message);

            $(id_selector).modal().on('hidden', function () {
                // Remove modal objetc after hide it
                $(id_selector).remove();
            });
        }
    });

    $.extend({
        hahSubmit: function (id_selector, url, data, block_id_selector, block_message, form_id_selector) {
            //alert(id_selector  + '-' + url + '-' + data + '-' + '-' + block_id_selector + '-' + block_message + '-' + form_id_selector);

            if (!data) {
                data = $(form_id_selector).serializeArray(); //serializeArray so .load makes a POST
                //would it be good to use malsup jquery form plugin?
            }
            // Load modal contents
            $.hahLoad(id_selector, url, data, block_id_selector, block_message);
        }
    });

    $.extend({
        hahFormReset: function (form_id_selector) {
            $(form_id_selector).each(function(){
                    this.reset();
            });
        }
    });

    $.extend({
        htmlAjaxHelper : function(events, selectors) {
            //click should be customizable
            //alert('helper aplicado en ' + events + ' con selectores: ' + selectors);

            $(document).on(events, selectors,  function (e) {
                //alert('lanzado ' + events + ' con selectores: ' + selectors);
                e.preventDefault();
                e.stopPropagation();
                hah_url = $(this).attr('data-hah-url');
                hah_action = $(this).attr('data-hah-action');
                hah_id_selector = $(this).attr('data-hah-id-selector');
                hah_data = $(this).attr('data-hah-data');
                hah_block_id_selector = $(this).attr('data-hah-block-id-selector');
                hah_block_message = $(this).attr('data-hah-block-message');
                hah_modal_add_class = $(this).attr('data-hah-modal-add-class');
                hah_form_id_selector = $(this).attr('data-hah-form-id-selector');
                hah_confirm_message = $(this).attr('data-hah-confirm-message');

                $('form').on('submit', hah_form_id_selector, function(e) {
                    e.preventDefault();
                });

                if (!hah_url) {
                    hah_url = $(this).attr('href');
                    if (hah_form_id_selector) {
                        hah_url = $(hah_form_id_selector).attr('action');
                    }
                }

                if ((!hah_confirm_message) || confirm(hah_confirm_message)) {
                    switch (hah_action) {
                        case "block":
                            $.hahBlock(hah_block_id_selector, hah_block_message);
                            break
                        case "unblock":
                            $.hahUnblock(hah_block_id_selector);
                            break
                        case "load":
                             $.hahLoad(hah_id_selector, hah_url, hah_data, hah_block_id_selector, hah_block_message);
                             break
                        case "load-modal":
                             $.hahModalLoad(hah_id_selector, hah_url, hah_data, hah_block_id_selector, hah_block_message, hah_modal_add_class);
                             break
                        case "form-submit":
                             $.hahSubmit(hah_id_selector, hah_url, hah_data, hah_block_id_selector, hah_block_message, hah_form_id_selector);
                             break
                        case "form-reset":
                             $.hahFormReset(hah_form_id_selector);
                             break
                        default:
                    }//end switch
                }//confirm
            });//on event
        } //htmlajaxhelper function
    });
}( jQuery ));

//is should be instanciated like $('a, button').htmlAjaxHelper('click', '[hah-action]');