<?php
/**
 * الصفحة الرئيسية للإضافة في لوحة التحكم
 * 
 * @package Internal External Links Helper
 * @author فوزي جمعة
 */

// منع الوصول المباشر
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="ielh-admin-header">
        <div class="ielh-plugin-info">
            <h2>مرحباً بك في إضافة مساعدة الروابط الداخلية والخارجية</h2>
            <p class="description">هذه الإضافة تساعدك في إنشاء الروابط الداخلية بسهولة من خلال واجهة بسيطة ومرنة.</p>
        </div>
    </div>
    
    <div class="ielh-dashboard-widgets">
        <div class="ielh-widget-row">
            <!-- معلومات سريعة -->
            <div class="ielh-widget">
                <div class="ielh-widget-header">
                    <h3><span class="dashicons dashicons-info"></span> معلومات سريعة</h3>
                </div>
                <div class="ielh-widget-content">
                    <ul class="ielh-info-list">
                        <li><strong>إصدار الإضافة:</strong> <?php echo IELH_PLUGIN_VERSION; ?></li>
                        <li><strong>المطور:</strong> فوزي جمعة</li>
                        <li><strong>الموقع:</strong> <a href="https://fjomah.com" target="_blank">fjomah.com</a></li>
                        <li><strong>واتساب:</strong> <a href="https://wa.me/201111933193" target="_blank">+201111933193</a></li>
                    </ul>
                </div>
            </div>
            
            <!-- إحصائيات -->
            <div class="ielh-widget">
                <div class="ielh-widget-header">
                    <h3><span class="dashicons dashicons-chart-bar"></span> إحصائيات</h3>
                </div>
                <div class="ielh-widget-content">
                    <?php
                    global $wpdb;
                    $texts_table = $wpdb->prefix . 'ielh_link_texts';
                    $texts_count = $wpdb->get_var("SELECT COUNT(*) FROM $texts_table");
                    
                    $posts_count = wp_count_posts('post');
                    $pages_count = wp_count_posts('page');
                    ?>
                    <ul class="ielh-stats-list">
                        <li>
                            <span class="ielh-stat-number"><?php echo $texts_count; ?></span>
                            <span class="ielh-stat-label">نص محفوظ</span>
                        </li>
                        <li>
                            <span class="ielh-stat-number"><?php echo $posts_count->publish; ?></span>
                            <span class="ielh-stat-label">مقالة منشورة</span>
                        </li>
                        <li>
                            <span class="ielh-stat-number"><?php echo $pages_count->publish; ?></span>
                            <span class="ielh-stat-label">صفحة منشورة</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        
        <div class="ielh-widget-row">
            <!-- كيفية الاستخدام -->
            <div class="ielh-widget ielh-widget-full">
                <div class="ielh-widget-header">
                    <h3><span class="dashicons dashicons-book-alt"></span> كيفية الاستخدام</h3>
                </div>
                <div class="ielh-widget-content">
                    <div class="ielh-usage-steps">
                        <div class="ielh-step">
                            <div class="ielh-step-number">1</div>
                            <div class="ielh-step-content">
                                <h4>إدارة النصوص</h4>
                                <p>قم بإضافة النصوص التي تريد استخدامها قبل الروابط من صفحة <a href="<?php echo admin_url('admin.php?page=ielh-texts'); ?>">إدارة النصوص</a></p>
                            </div>
                        </div>
                        
                        <div class="ielh-step">
                            <div class="ielh-step-number">2</div>
                            <div class="ielh-step-content">
                                <h4>إدراج الروابط</h4>
                                <p>أثناء تحرير المقالة أو الصفحة، اضغط على زر "إدراج رابط داخلي" في شريط أدوات المحرر</p>
                            </div>
                        </div>
                        
                        <div class="ielh-step">
                            <div class="ielh-step-number">3</div>
                            <div class="ielh-step-content">
                                <h4>اختيار النص والمقالة</h4>
                                <p>اختر النص المناسب من القائمة، ثم اختر المقالة أو الصفحة التي تريد الربط إليها</p>
                            </div>
                        </div>
                        
                        <div class="ielh-step">
                            <div class="ielh-step-number">4</div>
                            <div class="ielh-step-content">
                                <h4>إدراج الرابط</h4>
                                <p>اضغط على "إدراج الرابط" وسيتم إضافة الرابط بالتنسيق المطلوب في المحرر</p>
                            </div>
                        </div>
                        
                        <div class="ielh-step">
                            <div class="ielh-step-number">5</div>
                            <div class="ielh-step-content">
                                <h4>نسخ الروابط بتنسيقات مختلفة</h4>
                                <p>استخدم ودجيت "نسخ رابط المقالة" في الشريط الجانبي لنسخ روابط المقالة بتنسيقات HTML، BBCode، Markdown وغيرها</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="ielh-widget-row">
            <!-- روابط سريعة -->
            <div class="ielh-widget">
                <div class="ielh-widget-header">
                    <h3><span class="dashicons dashicons-admin-links"></span> روابط سريعة</h3>
                </div>
                <div class="ielh-widget-content">
                    <div class="ielh-quick-links">
                        <a href="<?php echo admin_url('admin.php?page=ielh-texts'); ?>" class="ielh-quick-link">
                            <span class="dashicons dashicons-edit"></span>
                            إدارة النصوص
                        </a>
                        <a href="<?php echo admin_url('admin.php?page=ielh-settings'); ?>" class="ielh-quick-link">
                            <span class="dashicons dashicons-admin-settings"></span>
                            الإعدادات
                        </a>
                        <a href="<?php echo admin_url('post-new.php'); ?>" class="ielh-quick-link">
                            <span class="dashicons dashicons-plus-alt"></span>
                            مقالة جديدة
                        </a>
                        <a href="<?php echo admin_url('post-new.php?post_type=page'); ?>" class="ielh-quick-link">
                            <span class="dashicons dashicons-plus-alt"></span>
                            صفحة جديدة
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- الدعم والمساعدة -->
            <div class="ielh-widget">
                <div class="ielh-widget-header">
                    <h3><span class="dashicons dashicons-sos"></span> الدعم والمساعدة</h3>
                </div>
                <div class="ielh-widget-content">
                    <p>إذا كنت تحتاج إلى مساعدة أو لديك أي استفسار:</p>
                    <div class="ielh-support-links">
                        <a href="https://wa.me/201111933193" target="_blank" class="ielh-support-link">
                            <span class="dashicons dashicons-whatsapp"></span>
                            واتساب: +201111933193
                        </a>
                        <a href="https://fjomah.com" target="_blank" class="ielh-support-link">
                            <span class="dashicons dashicons-admin-site"></span>
                            الموقع الإلكتروني
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ielh-admin-header {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    padding: 20px;
    margin: 20px 0;
}

.ielh-plugin-info h2 {
    color: #1d2327;
    margin: 0 0 10px 0;
}

.ielh-dashboard-widgets {
    margin-top: 20px;
}

.ielh-widget-row {
    display: flex;
    gap: 20px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.ielh-widget {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    flex: 1;
    min-width: 300px;
}

.ielh-widget-full {
    flex: 100%;
}

.ielh-widget-header {
    background: #f6f7f7;
    border-bottom: 1px solid #ccd0d4;
    padding: 15px 20px;
}

.ielh-widget-header h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
    color: #1d2327;
}

.ielh-widget-header .dashicons {
    margin-left: 5px;
    color: #2271b1;
}

.ielh-widget-content {
    padding: 20px;
}

.ielh-info-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

.ielh-info-list li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f1;
}

.ielh-info-list li:last-child {
    border-bottom: none;
}

.ielh-stats-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    justify-content: space-around;
    text-align: center;
}

.ielh-stats-list li {
    flex: 1;
}

.ielh-stat-number {
    display: block;
    font-size: 24px;
    font-weight: bold;
    color: #2271b1;
    margin-bottom: 5px;
}

.ielh-stat-label {
    display: block;
    font-size: 12px;
    color: #646970;
}

.ielh-usage-steps {
    display: grid;
    gap: 20px;
}

.ielh-step {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.ielh-step-number {
    background: #2271b1;
    color: #fff;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    flex-shrink: 0;
}

.ielh-step-content h4 {
    margin: 0 0 8px 0;
    color: #1d2327;
}

.ielh-step-content p {
    margin: 0;
    color: #646970;
}

.ielh-quick-links {
    display: grid;
    gap: 10px;
}

.ielh-quick-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px;
    background: #f6f7f7;
    border: 1px solid #dcdcde;
    border-radius: 3px;
    text-decoration: none;
    color: #1d2327;
    transition: all 0.2s;
}

.ielh-quick-link:hover {
    background: #2271b1;
    color: #fff;
    text-decoration: none;
}

.ielh-support-links {
    display: grid;
    gap: 10px;
    margin-top: 15px;
}

.ielh-support-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px;
    background: #f0f6fc;
    border: 1px solid #c3dcf3;
    border-radius: 3px;
    text-decoration: none;
    color: #2271b1;
    font-size: 13px;
}

.ielh-support-link:hover {
    background: #2271b1;
    color: #fff;
    text-decoration: none;
}

@media (max-width: 768px) {
    .ielh-widget-row {
        flex-direction: column;
    }
    
    .ielh-stats-list {
        flex-direction: column;
        gap: 15px;
    }
}
</style>