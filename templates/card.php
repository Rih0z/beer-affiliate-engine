<?php
/**
 * カード表示テンプレート
 * 
 * @var array $links リンク情報
 * @var string $heading 見出し
 */
?>
<div class="beer-affiliate-container">
    <h3 class="beer-affiliate-title"><?php echo esc_html($heading); ?></h3>
    
    <div class="beer-affiliate-cards">
        <?php foreach ($links as $item): ?>
            <div class="beer-affiliate-card">
                <?php if (isset($item['city']['image_url']) && !empty($item['city']['image_url'])): ?>
                    <div class="beer-affiliate-card-image">
                        <img src="<?php echo esc_url(BEER_AFFILIATE_PLUGIN_URL . 'modules/travel/images/' . $item['city']['image_url']); ?>" alt="<?php echo esc_attr($item['city']['name']); ?>">
                    </div>
                <?php endif; ?>
                
                <div class="beer-affiliate-card-content">
                    <?php
                    // 国内/海外で表示を変える
                    $heading_text = '';
                    $is_international = (isset($item['city']['region']) && $item['city']['region'] === '海外') || 
                                        (isset($item['city']['country']) && !empty($item['city']['country']) && $item['city']['country'] !== '日本');
                    
                    if ($is_international && isset($item['city']['country']) && !empty($item['city']['country'])) {
                        $heading_text = $item['city']['name'] . '（' . $item['city']['country'] . '）のビール旅';
                    } else {
                        $heading_text = $item['city']['name'] . 'のビール旅';
                    }
                    ?>
                    <h4 class="beer-affiliate-card-title"><?php echo esc_html($heading_text); ?></h4>
                    
                    <?php if (isset($item['city']['description'])): ?>
                        <p class="beer-affiliate-card-description"><?php echo esc_html($item['city']['description']); ?></p>
                    <?php endif; ?>
                    
                    <div class="beer-affiliate-card-links">
                        <?php foreach ($item['links'] as $service => $link): ?>
                            <a href="<?php echo esc_url($link['url']); ?>" 
                               target="_blank" 
                               rel="nofollow noopener" 
                               class="beer-affiliate-link beer-affiliate-button beer-affiliate-button-<?php echo sanitize_html_class(strtolower($service)); ?>"
                               data-service="<?php echo esc_attr($service); ?>" 
                               data-city="<?php echo esc_attr($item['city']['name']); ?>">
                                <?php echo esc_html($link['label']); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
