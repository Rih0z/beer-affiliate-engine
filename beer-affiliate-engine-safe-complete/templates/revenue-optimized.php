<?php
/**
 * 収益最適化テンプレート
 * 
 * @var array $links カテゴリー別のリンク情報
 */
?>
<div class="beer-affiliate-container beer-revenue-optimized">
    <?php foreach ($links as $category => $category_data): ?>
        <div class="beer-affiliate-category beer-affiliate-<?php echo esc_attr($category); ?>">
            <h3 class="beer-affiliate-category-title"><?php echo esc_html($category_data['title']); ?></h3>
            
            <div class="beer-affiliate-links">
                <?php foreach ($category_data['links'] as $link): ?>
                    <div class="beer-affiliate-link-item <?php echo isset($link['is_recurring']) && $link['is_recurring'] ? 'beer-affiliate-recurring' : ''; ?>">
                        <a href="<?php echo esc_url($link['url']); ?>" 
                           target="_blank" 
                           rel="nofollow noopener" 
                           class="beer-affiliate-link beer-affiliate-cta"
                           data-service="<?php echo esc_attr($link['service']); ?>"
                           data-category="<?php echo esc_attr($link['category']); ?>"
                           data-revenue="<?php echo esc_attr($link['potential_revenue']); ?>"
                           data-post-id="<?php echo get_the_ID(); ?>">
                            <?php echo esc_html($link['label']); ?>
                            
                            <?php if (isset($link['potential_revenue']) && $link['potential_revenue'] >= 2000): ?>
                                <span class="beer-affiliate-badge">高還元</span>
                            <?php endif; ?>
                            
                            <?php if (isset($link['is_recurring']) && $link['is_recurring']): ?>
                                <span class="beer-affiliate-badge beer-affiliate-recurring-badge">継続収益</span>
                            <?php endif; ?>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <div class="beer-affiliate-note">
        <p>※ リンクから商品・サービスをご利用いただくと、売上の一部が当サイトに還元されることがあります。</p>
    </div>
</div>

<style>
.beer-revenue-optimized {
    margin: 2em 0;
}

.beer-affiliate-category {
    margin-bottom: 2em;
    padding: 1.5em;
    background: #f9f9f9;
    border-radius: 8px;
}

.beer-affiliate-category-title {
    font-size: 1.2em;
    margin-bottom: 1em;
    color: #333;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 0.5em;
}

.beer-affiliate-link-item {
    margin-bottom: 1em;
}

.beer-affiliate-cta {
    display: inline-block;
    padding: 0.8em 1.5em;
    background: #0073aa;
    color: white !important;
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
    position: relative;
}

.beer-affiliate-cta:hover {
    background: #005a87;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.beer-affiliate-badge {
    display: inline-block;
    margin-left: 0.5em;
    padding: 0.2em 0.6em;
    background: #ff6b6b;
    color: white;
    font-size: 0.8em;
    border-radius: 3px;
    font-weight: bold;
}

.beer-affiliate-recurring-badge {
    background: #4ecdc4;
}

/* カテゴリー別の色分け */
.beer-affiliate-travel .beer-affiliate-cta {
    background: #e74c3c;
}

.beer-affiliate-travel .beer-affiliate-cta:hover {
    background: #c0392b;
}

.beer-affiliate-experience .beer-affiliate-cta {
    background: #3498db;
}

.beer-affiliate-experience .beer-affiliate-cta:hover {
    background: #2980b9;
}

.beer-affiliate-shopping .beer-affiliate-cta {
    background: #27ae60;
}

.beer-affiliate-shopping .beer-affiliate-cta:hover {
    background: #229954;
}

.beer-affiliate-utility .beer-affiliate-cta {
    background: #9b59b6;
}

.beer-affiliate-utility .beer-affiliate-cta:hover {
    background: #8e44ad;
}

.beer-affiliate-note {
    margin-top: 2em;
    padding: 1em;
    background: #f0f0f0;
    border-radius: 5px;
    font-size: 0.9em;
    color: #666;
}

@media (max-width: 768px) {
    .beer-affiliate-cta {
        display: block;
        text-align: center;
        margin-bottom: 0.5em;
    }
}
</style>