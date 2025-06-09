<?php
/**
 * ユーザーフレンドリーなテンプレート
 * 
 * @var array $links カテゴリー別のリンク配列
 */
?>
<div class="beer-affiliate-container user-friendly">
    <?php foreach ($links as $category_key => $category) : ?>
        <div class="beer-category-section">
            <h3 class="category-title"><?php echo esc_html($category['title']); ?></h3>
            
            <div class="beer-links-grid">
                <?php foreach ($category['links'] as $link) : ?>
                    <div class="beer-link-card">
                        <a href="<?php echo esc_url($link['url']); ?>" 
                           target="_blank" 
                           rel="noopener noreferrer"
                           class="beer-link-wrapper"
                           data-service="<?php echo esc_attr($link['service']); ?>">
                            
                            <div class="link-content">
                                <h4 class="link-title"><?php echo esc_html($link['label']); ?></h4>
                                <?php if (!empty($link['description'])) : ?>
                                    <p class="link-description"><?php echo esc_html($link['description']); ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="link-arrow">
                                <svg width="20" height="20" viewBox="0 0 20 20">
                                    <path d="M7 2l8 8-8 8" stroke="currentColor" stroke-width="2" fill="none"/>
                                </svg>
                            </div>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
    
    <div class="beer-affiliate-footer">
        <p class="disclaimer">※ 各サービスの最新情報は公式サイトでご確認ください</p>
    </div>
</div>

<style>
.beer-affiliate-container.user-friendly {
    margin: 30px 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
}

.beer-category-section {
    margin-bottom: 35px;
}

.category-title {
    font-size: 1.3em;
    font-weight: bold;
    color: #333;
    margin-bottom: 15px;
    padding-bottom: 8px;
    border-bottom: 2px solid #f0a500;
}

.beer-links-grid {
    display: grid;
    gap: 15px;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
}

.beer-link-card {
    background: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    transition: all 0.3s ease;
    overflow: hidden;
}

.beer-link-card:hover {
    border-color: #f0a500;
    box-shadow: 0 4px 12px rgba(240, 165, 0, 0.15);
    transform: translateY(-2px);
}

.beer-link-wrapper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px;
    text-decoration: none;
    color: inherit;
    height: 100%;
}

.link-content {
    flex: 1;
    padding-right: 15px;
}

.link-title {
    font-size: 1.1em;
    font-weight: 600;
    color: #333;
    margin: 0 0 8px 0;
    line-height: 1.3;
}

.link-description {
    font-size: 0.9em;
    color: #666;
    margin: 0;
    line-height: 1.4;
}

.link-arrow {
    flex-shrink: 0;
    color: #f0a500;
    transition: transform 0.3s ease;
}

.beer-link-card:hover .link-arrow {
    transform: translateX(4px);
}

.beer-affiliate-footer {
    margin-top: 25px;
    padding-top: 20px;
    border-top: 1px solid #e0e0e0;
}

.disclaimer {
    font-size: 0.85em;
    color: #888;
    text-align: center;
    margin: 0;
}

/* レスポンシブ対応 */
@media (max-width: 768px) {
    .beer-links-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .beer-link-wrapper {
        padding: 16px;
    }
    
    .link-title {
        font-size: 1em;
    }
    
    .link-description {
        font-size: 0.85em;
    }
}

/* ダークモード対応 */
@media (prefers-color-scheme: dark) {
    .beer-affiliate-container.user-friendly {
        color: #e0e0e0;
    }
    
    .category-title {
        color: #f0f0f0;
        border-bottom-color: #f0a500;
    }
    
    .beer-link-card {
        background: #2a2a2a;
        border-color: #444;
    }
    
    .beer-link-card:hover {
        border-color: #f0a500;
        background: #333;
    }
    
    .link-title {
        color: #f0f0f0;
    }
    
    .link-description {
        color: #aaa;
    }
    
    .disclaimer {
        color: #888;
    }
}
</style>