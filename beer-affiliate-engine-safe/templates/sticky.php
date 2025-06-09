<?php
/**
 * スクロール追従表示テンプレート
 * 
 * @var array $item 地域とリンク情報
 */

// 地域名の表示を準備
$city_name = $item['city']['name'];
$is_international = (isset($item['city']['region']) && $item['city']['region'] === '海外') || 
                    (isset($item['city']['country']) && !empty($item['city']['country']) && $item['city']['country'] !== '日本');

if ($is_international && isset($item['city']['country']) && !empty($item['city']['country'])) {
    $city_display = $city_name . '（' . $item['city']['country'] . '）';
} else {
    $city_display = $city_name;
}
?>
<div class="beer-affiliate-sticky-container">
    <div class="beer-affiliate-sticky hidden">
        <div class="beer-affiliate-sticky-content">
            <h4 class="beer-affiliate-sticky-title"><?php echo esc_html($city_display); ?>のビール旅に出かけよう！</h4>
            
            <div class="beer-affiliate-sticky-buttons">
                <?php foreach ($item['links'] as $service => $link): ?>
                    <a href="<?php echo esc_url($link['url']); ?>" 
                       target="_blank" 
                       rel="nofollow noopener" 
                       class="beer-affiliate-button beer-affiliate-button-sticky beer-affiliate-button-<?php echo sanitize_html_class(strtolower($service)); ?>"
                       data-service="<?php echo esc_attr($service); ?>" 
                       data-city="<?php echo esc_attr($city_name); ?>">
                        <?php echo esc_html($link['label']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        
        <button class="beer-affiliate-sticky-close" aria-label="<?php esc_attr_e('閉じる', 'beer-affiliate-engine'); ?>">×</button>
    </div>
</div>
