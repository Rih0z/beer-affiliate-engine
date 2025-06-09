/**
 * Beer Affiliate Engine - クリック追跡
 */
(function($) {
    'use strict';
    
    $(document).ready(function() {
        // アフィリエイトリンクにクリックイベントを設定
        $('.beer-affiliate-link').on('click', function(e) {
            var $link = $(this);
            var service = $link.data('service');
            var city = $link.data('city');
            var postId = $link.data('post-id') || beer_affiliate_tracker.post_id;
            
            // Ajaxでクリックを記録
            $.ajax({
                url: beer_affiliate_tracker.ajax_url,
                type: 'POST',
                data: {
                    action: 'beer_affiliate_track_click',
                    nonce: beer_affiliate_tracker.nonce,
                    service: service,
                    city: city,
                    post_id: postId
                },
                async: true // 非同期で送信（リンク遷移を妨げない）
            });
            
            // デバッグ用
            if (beer_affiliate_tracker.debug) {
                console.log('Affiliate click tracked:', {
                    service: service,
                    city: city,
                    post_id: postId
                });
            }
        });
    });
})(jQuery);