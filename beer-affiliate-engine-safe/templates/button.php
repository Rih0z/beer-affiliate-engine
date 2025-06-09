<?php
/**
 * ボタン表示テンプレート
 * 
 * @var array $links リンク情報
 * @var string $heading 見出し
 */
?>
<div class="beer-affiliate-container beer-affiliate-button-container">
    <h3 class="beer-affiliate-title"><?php echo esc_html($heading); ?></h3>
    
    <div class="beer-affiliate-buttons">
        <?php foreach ($links as $item): ?>
            <div class="beer-affiliate-button-group">
                <?php
                // 国内/海外で表示を変える
                $city_name_text = '';
                $is_international = (isset($item['city']['region']) && $item['city']['region'] === '海外') || 
                                    (isset($item['city']['country']) && !empty($item['city']['country']) && $item['city']['country'] !== '日本');
                
                if ($is_international && isset($item['city']['country']) && !empty($item['city']['country'])) {
                    $city_name_text = $item['city']['name'] . '（' . $item['city']['country'] . '）のビール旅';
                } else {
                    $city_name_text = $item['city']['name'] . 'のビール旅';
                }
                ?>
                <p class="beer-affiliate-city-name"><?php echo esc_html($city_name_text); ?></p>
                
                <?php foreach ($item['links'] as $service => $link): ?>
                    <a href="<?php echo esc_url($link['url']); ?>" 
                       target="_blank" 
                       rel="nofollow noopener" 
                       class="beer-affiliate-button beer-affiliate-link beer-affiliate-button-<?php echo sanitize_html_class(strtolower($service)); ?>"
                       data-service="<?php echo esc_attr($service); ?>" 
                       data-city="<?php echo esc_attr($item['city']['name']); ?>"
                       data-post-id="<?php echo get_the_ID(); ?>">
                        <?php echo esc_html($link['label']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
