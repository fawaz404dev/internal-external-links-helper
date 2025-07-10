<?php
/**
 * صفحة إدارة النصوص
 * 
 * @package Internal External Links Helper
 * @author فوزي جمعة
 */

// منع الوصول المباشر
if (!defined('ABSPATH')) {
    exit;
}

// معالجة إضافة نص جديد
if (isset($_POST['add_text']) && wp_verify_nonce($_POST['ielh_nonce'], 'ielh_add_text')) {
    $text_content = sanitize_textarea_field($_POST['text_content']);
    
    if (!empty($text_content)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ielh_link_texts';
        
        $result = $wpdb->insert(
            $table_name,
            array('text_content' => $text_content),
            array('%s')
        );
        
        if ($result !== false) {
            echo '<div class="notice notice-success is-dismissible"><p>تم إضافة النص بنجاح!</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>فشل في إضافة النص. يرجى المحاولة مرة أخرى.</p></div>';
        }
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>يرجى إدخال النص المطلوب.</p></div>';
    }
}

// معالجة حذف نص
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['text_id']) && wp_verify_nonce($_GET['_wpnonce'], 'ielh_delete_text')) {
    $text_id = intval($_GET['text_id']);
    
    if ($text_id > 0) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'ielh_link_texts';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $text_id),
            array('%d')
        );
        
        if ($result !== false) {
            echo '<div class="notice notice-success is-dismissible"><p>تم حذف النص بنجاح!</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>فشل في حذف النص.</p></div>';
        }
    }
}

// جلب جميع النصوص
global $wpdb;
$table_name = $wpdb->prefix . 'ielh_link_texts';
$texts = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="ielh-texts-page">
        <!-- نموذج إضافة نص جديد -->
        <div class="ielh-add-text-form">
            <div class="ielh-card">
                <div class="ielh-card-header">
                    <h2><span class="dashicons dashicons-plus-alt"></span> إضافة نص جديد</h2>
                </div>
                <div class="ielh-card-body">
                    <form method="post" action="">
                        <?php wp_nonce_field('ielh_add_text', 'ielh_nonce'); ?>
                        
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="text_content">النص:</label>
                                </th>
                                <td>
                                    <textarea 
                                        id="text_content" 
                                        name="text_content" 
                                        rows="3" 
                                        cols="50" 
                                        class="large-text" 
                                        placeholder="مثال: شاهد المزيد من المعلومات من هنا:"
                                        required
                                    ></textarea>
                                    <p class="description">
                                        أدخل النص الذي سيظهر قبل الرابط. مثال: "شاهد المزيد من المعلومات من هنا:"
                                    </p>
                                </td>
                            </tr>
                        </table>
                        
                        <p class="submit">
                            <input type="submit" name="add_text" class="button-primary" value="إضافة النص" />
                        </p>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- قائمة النصوص المحفوظة -->
        <div class="ielh-texts-list">
            <div class="ielh-card">
                <div class="ielh-card-header">
                    <h2><span class="dashicons dashicons-list-view"></span> النصوص المحفوظة (<?php echo count($texts); ?>)</h2>
                </div>
                <div class="ielh-card-body">
                    <?php if (empty($texts)): ?>
                        <div class="ielh-empty-state">
                            <div class="ielh-empty-icon">
                                <span class="dashicons dashicons-format-quote"></span>
                            </div>
                            <h3>لا توجد نصوص محفوظة</h3>
                            <p>قم بإضافة النص الأول باستخدام النموذج أعلاه</p>
                        </div>
                    <?php else: ?>
                        <div class="ielh-texts-grid">
                            <?php foreach ($texts as $text): ?>
                                <div class="ielh-text-item">
                                    <div class="ielh-text-content">
                                        <div class="ielh-text-preview">
                                            <?php echo esc_html($text->text_content); ?>
                                        </div>
                                        <div class="ielh-text-meta">
                                            <span class="ielh-text-date">
                                                <span class="dashicons dashicons-calendar-alt"></span>
                                                <?php echo date_i18n('Y/m/d H:i', strtotime($text->created_at)); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="ielh-text-actions">
                                        <button 
                                            type="button" 
                                            class="button ielh-copy-text" 
                                            data-text="<?php echo esc_attr($text->text_content); ?>"
                                            title="نسخ النص"
                                        >
                                            <span class="dashicons dashicons-admin-page"></span>
                                        </button>
                                        <a 
                                            href="<?php echo wp_nonce_url(
                                                admin_url('admin.php?page=ielh-texts&action=delete&text_id=' . $text->id),
                                                'ielh_delete_text'
                                            ); ?>" 
                                            class="button ielh-delete-text"
                                            onclick="return confirm('هل أنت متأكد من حذف هذا النص؟')"
                                            title="حذف النص"
                                        >
                                            <span class="dashicons dashicons-trash"></span>
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- نصائح وإرشادات -->
        <div class="ielh-tips">
            <div class="ielh-card">
                <div class="ielh-card-header">
                    <h2><span class="dashicons dashicons-lightbulb"></span> نصائح وإرشادات</h2>
                </div>
                <div class="ielh-card-body">
                    <div class="ielh-tips-grid">
                        <div class="ielh-tip">
                            <div class="ielh-tip-icon">
                                <span class="dashicons dashicons-yes-alt"></span>
                            </div>
                            <div class="ielh-tip-content">
                                <h4>استخدم نصوص واضحة</h4>
                                <p>اختر نصوص واضحة ومفهومة تدل على أن هناك رابط مفيد للقارئ</p>
                            </div>
                        </div>
                        
                        <div class="ielh-tip">
                            <div class="ielh-tip-icon">
                                <span class="dashicons dashicons-format-aside"></span>
                            </div>
                            <div class="ielh-tip-content">
                                <h4>تنويع النصوص</h4>
                                <p>استخدم نصوص متنوعة لتجنب التكرار وجعل المحتوى أكثر طبيعية</p>
                            </div>
                        </div>
                        
                        <div class="ielh-tip">
                            <div class="ielh-tip-icon">
                                <span class="dashicons dashicons-editor-help"></span>
                            </div>
                            <div class="ielh-tip-content">
                                <h4>أمثلة مفيدة</h4>
                                <p>"اقرأ أيضاً:"، "للمزيد من التفاصيل:"، "مقال ذو صلة:"، "قد يهمك:"</p>
                            </div>
                        </div>
                        
                        <div class="ielh-tip">
                            <div class="ielh-tip-icon">
                                <span class="dashicons dashicons-admin-links"></span>
                            </div>
                            <div class="ielh-tip-content">
                                <h4>الربط الداخلي</h4>
                                <p>الربط الداخلي يحسن من تجربة المستخدم ويساعد في تحسين محركات البحث</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ielh-texts-page {
    max-width: 1200px;
}

.ielh-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    border-radius: 4px;
    margin-bottom: 20px;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.ielh-card-header {
    background: #f6f7f7;
    border-bottom: 1px solid #ccd0d4;
    padding: 15px 20px;
}

.ielh-card-header h2 {
    margin: 0;
    font-size: 16px;
    font-weight: 600;
    color: #1d2327;
}

.ielh-card-header .dashicons {
    margin-left: 5px;
    color: #2271b1;
}

.ielh-card-body {
    padding: 20px;
}

.ielh-empty-state {
    text-align: center;
    padding: 40px 20px;
    color: #646970;
}

.ielh-empty-icon {
    font-size: 48px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.ielh-empty-state h3 {
    margin: 0 0 10px 0;
    color: #1d2327;
}

.ielh-texts-grid {
    display: grid;
    gap: 15px;
}

.ielh-text-item {
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    transition: all 0.2s;
}

.ielh-text-item:hover {
    background: #f0f6fc;
    border-color: #c3dcf3;
}

.ielh-text-content {
    flex: 1;
}

.ielh-text-preview {
    font-size: 14px;
    line-height: 1.5;
    color: #1d2327;
    margin-bottom: 8px;
    word-break: break-word;
}

.ielh-text-meta {
    font-size: 12px;
    color: #646970;
}

.ielh-text-date .dashicons {
    font-size: 12px;
    margin-left: 3px;
}

.ielh-text-actions {
    display: flex;
    gap: 5px;
    margin-right: 15px;
}

.ielh-text-actions .button {
    padding: 4px 8px;
    min-height: auto;
    line-height: 1;
}

.ielh-copy-text {
    background: #2271b1;
    color: #fff;
    border-color: #2271b1;
}

.ielh-copy-text:hover {
    background: #135e96;
    border-color: #135e96;
    color: #fff;
}

.ielh-delete-text {
    background: #d63638;
    color: #fff;
    border-color: #d63638;
}

.ielh-delete-text:hover {
    background: #b32d2e;
    border-color: #b32d2e;
    color: #fff;
}

.ielh-tips-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.ielh-tip {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    padding: 15px;
    background: #f0f6fc;
    border: 1px solid #c3dcf3;
    border-radius: 4px;
}

.ielh-tip-icon {
    background: #2271b1;
    color: #fff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.ielh-tip-content h4 {
    margin: 0 0 8px 0;
    font-size: 14px;
    color: #1d2327;
}

.ielh-tip-content p {
    margin: 0;
    font-size: 13px;
    color: #646970;
    line-height: 1.4;
}

@media (max-width: 768px) {
    .ielh-text-item {
        flex-direction: column;
        gap: 15px;
    }
    
    .ielh-text-actions {
        margin-right: 0;
        justify-content: flex-end;
    }
    
    .ielh-tips-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // نسخ النص
    $('.ielh-copy-text').on('click', function() {
        var text = $(this).data('text');
        
        // إنشاء عنصر مؤقت للنسخ
        var tempInput = $('<textarea>');
        $('body').append(tempInput);
        tempInput.val(text).select();
        document.execCommand('copy');
        tempInput.remove();
        
        // تغيير النص مؤقتاً
        var originalHtml = $(this).html();
        $(this).html('<span class="dashicons dashicons-yes"></span>');
        $(this).css('background', '#00a32a');
        
        setTimeout(function() {
            $('.ielh-copy-text').html(originalHtml);
            $('.ielh-copy-text').css('background', '#2271b1');
        }, 1000);
    });
});
</script>