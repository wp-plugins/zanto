/**
 * @author Zanto Translate
 */
/**
 * Wrapper function to safely use $
 * @author Zanto Translate
 */

function zwt_wrapper($) {
    var zwt = {
        /**
         * Main entry point
         * @author Zanto Translate
         */
        init: function () {
            zwt.prefix = 'zwt_';
            zwt.templateURL = $('#templateURL').val();

            if (adminpage == 'zanto_page_zwt_trans_network') {
                zwt.transIdArray = $('#zwt_remove_elements #zwt_trans_id').clone();
                zwt.langArray = $('#zwt_remove_elements #update_lang_blog').clone();
                zwt.transNetworkPageStart();
                zwt.transNetEventHandlers();
            }
            if (adminpage == 'post-php' || adminpage == 'post-new-php') {
                zwt.postPageStart();
                zwt.PostEventHandlers();
                zwt.selectSet = new Array();
            }
            if (adminpage == 'zanto_page_zwt_advanced_tools') {
                zwt.AToolsEventHandlers();
            }
        },

        /**
         * Registers event handlers
         * @author Zanto Translate
         */
        transNetEventHandlers: function () {
            $('a.add_to_network').click(zwt.showUpdateTransInputs);
            $('a.remove_from_network').click(zwt.removeFromNetwork);
        },

        PostEventHandlers: function (event) {
            $('.transln_method_mthds').change(zwt.transln_method_change);
        },

        AToolsEventHandlers: function (event) {
            $('#zwt_reset_cache').click(zwt.advancedToolsAjax);
            $('#zwt_copy_taxonomy').click(zwt.advancedToolsAjax);
            $('#zwt_reset_zanto').click(zwt.advancedToolsAjax);
        },
		
        removeFromNetwork: function(event){
            var buttonSelected = $(this);
            var buttonId = buttonSelected.attr('id');
			var buttonTitle = buttonSelected.attr('title');
            var idRegex = /[0-9]+/;
            var id = parseInt(buttonId.match(idRegex), 10);
            event.preventDefault();
            var proceed = confirm(buttonTitle+':\n\n'+zwt_install_params['remove_string']);
            if(!proceed){
                return;
            }else{
                data = {
                    action: 'zwt_all_ajax',
                    admin_fn : 'remove_trans_site',
                    blog_id: id,
                    _wpnonce: $('#zwt_updatetrans_nonce').val()
                };
					
            }
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: data,
                cache: false,
                beforeSend: function () {
                    $('#'+buttonId).after('<span class="zwt_ajax_response" style="margin-left:10px"><img src="'+zwt_pluginUrl+'/images/spin-small.gif"></span>');                       
                },
                success: function (data) {
                        location.reload(true);
                }
            });
        },

        showUpdateTransInputs: function (event) {
            var c_user = zwt_install_params['current_user'];
            buttonSelected = $(this);
            var classRegex = /active/;
            if (buttonSelected.attr('class').search(classRegex) != -1) {
                $('.zwt_show_dash').text('-');
                $('.add_to_network').addClass('button').text('Add to Translation Network').removeClass('active').blur();
                $('#zwt_get_blogid').attr('value', '');
                event.preventDefault();
            } else {
                $('.zwt_show_dash').text('-');
                $('.add_to_network').addClass('button').text('Add to Translation Network').removeClass('active');


                var buttonId = buttonSelected.attr('id');
                var idRegex = /[0-9]+/;
                var id = parseInt(buttonId.match(idRegex), 10); 
                var transIdPos = $('#2zwt_elements' + id);
                var langArrayPos = $('#1zwt_elements' + id);
                var userPos = $('#3zwt_elements' + id);
                transIdPos.text('');
                langArrayPos.text('');
                userPos.text('');
                transIdPos.prepend(zwt.transIdArray);
                langArrayPos.prepend(zwt.langArray);
                userPos.text(c_user);
                buttonSelected.removeClass('button').text('Cancel').addClass('active').blur();
                //add the selected ID to submit button value for identification of selected ID
                $('#zwt_get_blogid').attr('value', id);
                return false;
            }
        },
        advancedToolsAjax: function(event){
            var selected = $(this).attr('name');
            var data;
		 
            if(selected == 'zwt_reset_cache'){
                data = {
                    action: 'zwt_all_ajax',
                    admin_fn : selected,
                    cacheType: $('input[name="zwt_clear_cache"]:checked').val(),
                    _wpnonce: $('#zwt_advanced_tools').val()
                };
					
            }else if(selected == 'zwt_copy_taxonomy'){
                data = {
                    action: 'zwt_all_ajax',
                    admin_fn: selected,
                    fromBlog: $('#zwt_from_blog :selected').val(),
                    taxonomy: $('#zwt_taxonomy_name :selected').val(),
                    _wpnonce: $('#zwt_advanced_tools').val()
                };
					
            }else if(selected== 'zwt_reset_zanto'){
                if(!$('#zwt_reset_settings').attr('checked')){
                    alert('Nothing Selected');
                    return;
                }
                var r=confirm("Your current blog settings will be lost. The default settings will be applied");
                if (!r==true){
                    return;
                }
                data = {
                    action: 'zwt_all_ajax',
                    admin_fn :selected,
                    _wpnonce: $('#zwt_advanced_tools').val()
                };
            }
		 
            jQuery.ajax({
                url: ajaxurl,
                type: 'post',
                data: data,
                cache: false,
                beforeSend: function () {
                    $('#'+selected).after('<span class="zwt_ajax_response" style="margin-left:10px"><img src="'+zwt_pluginUrl+'/images/spin-small.gif"></span>');                       
                },
                success: function (data) {
                    $('.zwt_ajax_response').html(data).fadeOut(1000, function(){});   
                }
            });
        },
		
        transNetworkPageStart: function () {
            $('#zwt_remove_elements').remove();
        },
        postPageStart: function () {
            $('.transln_method input').hide();
        },
        transln_method_change: function (event) {

            var inputSelected = $(this);
            var inputSelectedVal = inputSelected.val();
            var current_blog = inputSelected.attr('id');
            var idRegex = /[0-9]+/;
            var id = parseInt(current_blog.match(idRegex), 10);
            $('#transln_methd_div' + id + ' input').hide().attr('value', '');
            $('#transln_methd_div' + id + ' select').hide().attr('value', '');
            $('#transln_method_img' + id).hide();
            if (inputSelectedVal == 1) {
                $('#transln_method_img' + id).show();
            } else if (inputSelectedVal == 2) {

                var transSelectPost = $('#transln_method_select' + id);
                if (zwt.selectSet[id] != 1) {
                    var data = {
                        action: 'zwt_all_ajax',
                        admin_fn: 'zwt_fetch_trans_posts',
                        blog_id: id,
                        post_type: typenow
                    };
                    $.get(ajaxurl, data, function (data) {
                        transSelectPost.prepend(data);
                    });
                    zwt.selectSet[id] = 1;
                }
                transSelectPost.show();
            } else if (inputSelectedVal == 3) {
                $('#transln_method_text' + id).show().attr('value', 'http://');
            }
        }

    }; // end zwt

    $(document).ready(zwt.init);

} // end zwt_wrapper()

zwt_wrapper(jQuery);

function zwt_copy_from_original(blog_id, post_id) {
    //jQuery('#zwt_cfo').after(zwt_ajxloaderimg).attr('disabled', 'disabled');

    if (typeof tinyMCE != 'undefined' && (ed = tinyMCE.activeEditor) && !ed.isHidden()) {
        var editor_type = 'rich';
    } else {
        var editor_type = 'html';
    }

    jQuery.ajax({
        url: ajaxurl,
        data: {
            action: 'zwt_all_ajax',
            'admin_fn': 'zwt_copy_from_original_ajx',
            'b_id': blog_id,
            'p_id': post_id,
            'editor_type': editor_type,
            '_zwt_nonce': jQuery('#_zwt_nonce_cfo_' + post_id).val(),
            'type': typenow
        },
        dataType: 'JSON',
        success: function (msg) {
            if (msg.error) {
                alert(msg.error);
            } else {
                try { // we may not have the content 
                    if (typeof tinyMCE != 'undefined' && (ed = tinyMCE.activeEditor) && !ed.isHidden()) {
                        ed.focus();
                        if (tinymce.isIE)
                            ed.selection.moveToBookmark(tinymce.EditorManager.activeEditor.windowManager.bookmark);
                        ed.execCommand('mceInsertContent', false, msg.body);
                    } else {
                        if (typeof wpActiveEditor == 'undefined') wpActiveEditor = 'content';
                        edInsertContent(edCanvas, msg.body);
                    }
					jQuery('#title').focus().attr('value', msg.title);
                } catch (err) {
                ;
                }
                jQuery('#zwt_cfo').attr('disabled', true);

            }
        //   jQuery('#zwt_cfo').next().fadeOut();

        }
    });
    return false;
}