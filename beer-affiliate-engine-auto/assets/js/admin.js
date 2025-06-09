/**
 * Beer Affiliate Engine 管理画面JavaScript
 */

jQuery(document).ready(function($) {
    // タイプ選択時のフィールド表示切替
    $('#program_type').on('change', function() {
        $('.rakuten-fields, .a8-fields').hide();
        if ($(this).val() === 'rakuten') {
            $('.rakuten-fields').show();
        } else if ($(this).val() === 'a8') {
            $('.a8-fields').show();
        }
    });
    
    // プログラム追加/編集
    $('#add-program-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = $(this).serializeArray();
        var data = {};
        
        formData.forEach(function(item) {
            if (item.name === 'enabled') {
                data[item.name] = true;
            } else {
                data[item.name] = item.value;
            }
        });
        
        if (!data.enabled) {
            data.enabled = false;
        }
        
        $.post(ajaxurl, {
            action: 'beer_affiliate_save_program',
            program: data,
            edit_key: $('#edit_key').val(),
            nonce: beer_affiliate_admin.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('エラーが発生しました: ' + response.data);
            }
        });
    });
    
    // 編集ボタン
    $('.edit-program').on('click', function() {
        var program = $(this).data('program');
        var key = $(this).data('key');
        
        $('#program_name').val(program.name);
        $('#program_type').val(program.type).trigger('change');
        $('#url_template').val(program.url_template);
        $('#label').val(program.label);
        $('#affiliate_id').val(program.affiliate_id || '');
        $('#application_id').val(program.application_id || '');
        $('#program_id').val(program.program_id || '');
        $('#media_id').val(program.media_id || '');
        $('#enabled').prop('checked', program.enabled);
        $('#edit_key').val(key);
        
        $('html, body').animate({
            scrollTop: $('#add-program-form').offset().top - 50
        }, 500);
    });
    
    // 削除ボタン
    $('.delete-program').on('click', function() {
        if (confirm('このプログラムを削除してよろしいですか？')) {
            var key = $(this).data('key');
            
            $.post(ajaxurl, {
                action: 'beer_affiliate_delete_program',
                key: key,
                nonce: beer_affiliate_admin.nonce
            }, function(response) {
                if (response.success) {
                    location.reload();
                }
            });
        }
    });
});