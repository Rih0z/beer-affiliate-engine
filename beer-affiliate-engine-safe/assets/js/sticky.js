/**
 * Beer Affiliate Engine - Sticky Banner JavaScript
 * 
 * スクロール追従バナーの動作を制御するスクリプト
 */
(function($) {
    'use strict';
    
    // DOM読み込み完了後に実行
    $(document).ready(function() {
        // スクロール追従要素
        const $sticky = $('.beer-affiliate-sticky');
        
        // 閉じるボタン
        const $closeBtn = $('.beer-affiliate-sticky-close');
        
        // 表示遅延（ミリ秒）
        const showDelay = 3000;
        
        // スクロールしきい値（ピクセル）
        const scrollThreshold = 300;
        
        // 閲覧済みフラグのCookie名
        const cookieName = 'beer_affiliate_sticky_closed';
        
        // 初期状態は非表示
        $sticky.addClass('hidden');
        
        // スクロールイベントハンドラ
        $(window).on('scroll', debounce(function() {
            // 既に閉じられている場合は何もしない
            if (getCookie(cookieName)) {
                return;
            }
            
            // 十分にスクロールした場合は表示
            if ($(window).scrollTop() > scrollThreshold) {
                setTimeout(function() {
                    $sticky.removeClass('hidden');
                }, showDelay);
            }
        }, 100));
        
        // 閉じるボタンクリックイベント
        $closeBtn.on('click', function(e) {
            e.preventDefault();
            
            // バナーを非表示
            $sticky.addClass('hidden');
            
            // Cookieに閲覧済みフラグを設定（1日間有効）
            setCookie(cookieName, '1', 1);
        });
        
        // バナー内のリンククリックをトラッキング
        $sticky.find('a').on('click', function() {
            const service = $(this).attr('data-service') || '';
            const city = $(this).attr('data-city') || '';
            
            // クリックイベント記録（可能であれば）
            if (typeof logAffiliateClick === 'function') {
                logAffiliateClick(service, city, 'sticky');
            }
        });
    });
    
    /**
     * デバウンス関数
     * 連続して発生するイベントの処理頻度を制限
     * 
     * @param {Function} func 実行する関数
     * @param {number} wait 待機時間（ミリ秒）
     * @return {Function} デバウンスされた関数
     */
    function debounce(func, wait) {
        let timeout;
        
        return function() {
            const context = this;
            const args = arguments;
            
            clearTimeout(timeout);
            
            timeout = setTimeout(function() {
                func.apply(context, args);
            }, wait);
        };
    }
    
    /**
     * Cookieを設定
     * 
     * @param {string} name Cookie名
     * @param {string} value Cookie値
     * @param {number} days 有効日数
     */
    function setCookie(name, value, days) {
        let expires = '';
        
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = '; expires=' + date.toUTCString();
        }
        
        document.cookie = name + '=' + value + expires + '; path=/';
    }
    
    /**
     * Cookieを取得
     * 
     * @param {string} name Cookie名
     * @return {string|null} Cookie値
     */
    function getCookie(name) {
        const nameEQ = name + '=';
        const ca = document.cookie.split(';');
        
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') {
                c = c.substring(1, c.length);
            }
            
            if (c.indexOf(nameEQ) === 0) {
                return c.substring(nameEQ.length, c.length);
            }
        }
        
        return null;
    }
    
})(jQuery);
