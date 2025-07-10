<?php
/**
 * صفحة الإعدادات
 * 
 * @package Internal External Links Helper
 * @author فوزي جمعة
 */

// منع الوصول المباشر
if (!defined('ABSPATH')) {
    exit;
}

// معالجة حفظ الإعدادات
if (isset($_POST['save_settings']) && wp_verify_nonce($_POST['ielh_settings_nonce'], 'ielh_save_settings')) {
    $settings = array(
        'default_link_target' => sanitize_text_field($_POST['default_link_target'] ?? '_self'),
        'show_post_types' => array_map('sanitize_text_field', $_POST['show_post_types'] ?? array('post', 'page')),
        'link_css_class' => sanitize_text_field($_POST['link_css_class'] ?? ''),
        'enable_nofollow' => isset($_POST['enable_nofollow']) ? 1 : 0,
        'search_limit' => intval($_POST['search_limit'] ?? 20),
        'auto_save_texts' => isset($_POST['auto_save_texts']) ? 1 : 0
    );
    
    update_option('ielh_settings', $settings);
    echo '<div class="notice notice-success is-dismissible"><p>تم حفظ الإعدادات بنجاح!</p></div>';
}

// جلب الإعدادات الحالية
$default_settings = array(
    'default_link_target' => '_self',
    'show_post_types' => array('post', 'page'),
    'link_css_class' => '',
    'enable_nofollow' => 0,
    'search_limit' => 20,
    'auto_save_texts' => 1
);

$settings = wp_parse_args(get_option('ielh_settings', array()), $default_settings);

// جلب أنواع المقالات المتاحة
$post_types = get_post_types(array('public' => true), 'objects');
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="ielh-settings-page">
        <form method="post" action="">
            <?php wp_nonce_field('ielh_save_settings', 'ielh_settings_nonce'); ?>
            
            <div class="ielh-settings-sections">
                <!-- إعدادات الروابط -->
                <div class="ielh-card">
                    <div class="ielh-card-header">
                        <h2><span class="dashicons dashicons-admin-links"></span> إعدادات الروابط</h2>
                    </div>
                    <div class="ielh-card-body">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    <label for="default_link_target">هدف الرابط الافتراضي:</label>
                                </th>
                                <td>
                                    <select id="default_link_target" name="default_link_target">
                                        <option value="_self" <?php selected($settings['default_link_target'], '_self'); ?>>
                                            نفس النافذة (_self)
                                        </option>
                                        <option value="_blank" <?php selected($settings['default_link_target'], '_blank'); ?>>
                                            نافذة جديدة (_blank)
                                        </option>
                                    </select>
                                    <p class="description">
                                        اختر كيف سيتم فتح الروابط الداخلية افتراضياً
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="link_css_class">فئة CSS للروابط:</label>
                                </th>
                                <td>
                                    <input 
                                        type="text" 
                                        id="link_css_class" 
                                        name="link_css_class" 
                                        value="<?php echo esc_attr($settings['link_css_class']); ?>"
                                        class="regular-text"
                                        placeholder="internal-link"
                                    />
                                    <p class="description">
                                        فئة CSS اختيارية ستُضاف لجميع الروابط المُدرجة (اتركها فارغة إذا لم تكن تحتاجها)
                                    </p>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    إضافة nofollow:
                                </th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input 
                                                type="checkbox" 
                                                name="enable_nofollow" 
                                                value="1" 
                                                <?php checked($settings['enable_nofollow'], 1); ?>
                                            />
                                            إضافة خاصية rel="nofollow" للروابط الداخلية
                                        </label>
                                        <p class="description">
                                            عادة لا يُنصح بإضافة nofollow للروابط الداخلية
                                        </p>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- إعدادات البحث والعرض -->
                <div class="ielh-card">
                    <div class="ielh-card-header">
                        <h2><span class="dashicons dashicons-search"></span> إعدادات البحث والعرض</h2>
                    </div>
                    <div class="ielh-card-body">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    أنواع المحتوى المعروضة:
                                </th>
                                <td>
                                    <fieldset>
                                        <?php foreach ($post_types as $post_type): ?>
                                            <?php if (in_array($post_type->name, array('attachment', 'revision', 'nav_menu_item'))) continue; ?>
                                            <label>
                                                <input 
                                                    type="checkbox" 
                                                    name="show_post_types[]" 
                                                    value="<?php echo esc_attr($post_type->name); ?>"
                                                    <?php checked(in_array($post_type->name, $settings['show_post_types'])); ?>
                                                />
                                                <?php echo esc_html($post_type->labels->name); ?> (<?php echo esc_html($post_type->name); ?>)
                                            </label><br>
                                        <?php endforeach; ?>
                                        <p class="description">
                                            اختر أنواع المحتوى التي ستظهر في قائمة البحث
                                        </p>
                                    </fieldset>
                                </td>
                            </tr>
                            
                            <tr>
                                <th scope="row">
                                    <label for="search_limit">حد نتائج البحث:</label>
                                </th>
                                <td>
                                    <input 
                                        type="number" 
                                        id="search_limit" 
                                        name="search_limit" 
                                        value="<?php echo esc_attr($settings['search_limit']); ?>"
                                        min="5"
                                        max="100"
                                        class="small-text"
                                    />
                                    <p class="description">
                                        عدد النتائج القصوى التي ستظهر في البحث (5-100)
                                    </p>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- إعدادات متقدمة -->
                <div class="ielh-card">
                    <div class="ielh-card-header">
                        <h2><span class="dashicons dashicons-admin-settings"></span> إعدادات متقدمة</h2>
                    </div>
                    <div class="ielh-card-body">
                        <table class="form-table">
                            <tr>
                                <th scope="row">
                                    الحفظ التلقائي:
                                </th>
                                <td>
                                    <fieldset>
                                        <label>
                                            <input 
                                                type="checkbox" 
                                                name="auto_save_texts" 
                                                value="1" 
                                                <?php checked($settings['auto_save_texts'], 1); ?>
                                            />
                                            حفظ النصوص المستخدمة تلقائياً
                                        </label>
                                        <p class="description">
                                            عند التفعيل، سيتم حفظ أي نص جديد تستخدمه تلقائياً في قاعدة البيانات
                                        </p>
                                    </fieldset>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                <!-- معلومات النظام -->
                <div class="ielh-card">
                    <div class="ielh-card-header">
                        <h2><span class="dashicons dashicons-info"></span> معلومات النظام</h2>
                    </div>
                    <div class="ielh-card-body">
                        <div class="ielh-system-info">
                            <div class="ielh-info-grid">
                                <div class="ielh-info-item">
                                    <strong>إصدار الإضافة:</strong>
                                    <span><?php echo IELH_PLUGIN_VERSION; ?></span>
                                </div>
                                <div class="ielh-info-item">
                                    <strong>إصدار ووردبريس:</strong>
                                    <span><?php echo get_bloginfo('version'); ?></span>
                                </div>
                                <div class="ielh-info-item">
                                    <strong>إصدار PHP:</strong>
                                    <span><?php echo PHP_VERSION; ?></span>
                                </div>
                                <div class="ielh-info-item">
                                    <strong>قاعدة البيانات:</strong>
                                    <span><?php global $wpdb; echo $wpdb->db_version(); ?></span>
                                </div>
                                <div class="ielh-info-item">
                                    <strong>عدد النصوص المحفوظة:</strong>
                                    <span>
                                        <?php 
                                        global $wpdb;
                                        $table_name = $wpdb->prefix . 'ielh_link_texts';
                                        echo $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
                                        ?>
                                    </span>
                                </div>
                                <div class="ielh-info-item">
                                    <strong>المطور:</strong>
                                    <span>فوزي جمعة</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <p class="submit">
                <input type="submit" name="save_settings" class="button-primary" value="حفظ الإعدادات" />
                <a href="<?php echo admin_url('admin.php?page=ielh-main'); ?>" class="button">العودة للصفحة الرئيسية</a>
            </p>
        </form>
        
        <!-- أدوات إضافية -->
        <div class="ielh-card">
            <div class="ielh-card-header">
                <h2><span class="dashicons dashicons-admin-tools"></span> أدوات إضافية</h2>
            </div>
            <div class="ielh-card-body">
                <div class="ielh-tools-grid">
                    <div class="ielh-tool">
                        <h4>تصدير النصوص</h4>
                        <p>تصدير جميع النصوص المحفوظة كملف JSON</p>
                        <button type="button" class="button" id="export-texts">
                            <span class="dashicons dashicons-download"></span>
                            تصدير النصوص
                        </button>
                    </div>
                    
                    <div class="ielh-tool">
                        <h4>استيراد النصوص</h4>
                        <p>استيراد النصوص من ملف JSON</p>
                        <input type="file" id="import-file" accept=".json" style="display: none;">
                        <button type="button" class="button" id="import-texts">
                            <span class="dashicons dashicons-upload"></span>
                            استيراد النصوص
                        </button>
                    </div>
                    
                    <div class="ielh-tool">
                        <h4>إعادة تعيين</h4>
                        <p>إعادة تعيين جميع الإعدادات للقيم الافتراضية</p>
                        <button type="button" class="button button-secondary" id="reset-settings">
                            <span class="dashicons dashicons-update"></span>
                            إعادة تعيين
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.ielh-settings-page {
    max-width: 1000px;
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

.ielh-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.ielh-info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 3px;
}

.ielh-tools-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.ielh-tool {
    padding: 20px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
}

.ielh-tool h4 {
    margin: 0 0 10px 0;
    color: #1d2327;
}

.ielh-tool p {
    margin: 0 0 15px 0;
    color: #646970;
    font-size: 13px;
}

.ielh-tool .button {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

@media (max-width: 768px) {
    .ielh-info-grid,
    .ielh-tools-grid {
        grid-template-columns: 1fr;
    }
    
    .ielh-info-item {
        flex-direction: column;
        gap: 5px;
        text-align: center;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // تصدير النصوص
    $('#export-texts').on('click', function() {
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'ielh_export_texts',
                nonce: '<?php echo wp_create_nonce('ielh_export_texts'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    var dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(response.data));
                    var downloadAnchorNode = document.createElement('a');
                    downloadAnchorNode.setAttribute("href", dataStr);
                    downloadAnchorNode.setAttribute("download", "ielh-texts-" + new Date().toISOString().slice(0,10) + ".json");
                    document.body.appendChild(downloadAnchorNode);
                    downloadAnchorNode.click();
                    downloadAnchorNode.remove();
                } else {
                    alert('فشل في تصدير النصوص');
                }
            }
        });
    });
    
    // استيراد النصوص
    $('#import-texts').on('click', function() {
        $('#import-file').click();
    });
    
    $('#import-file').on('change', function(e) {
        var file = e.target.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                try {
                    var data = JSON.parse(e.target.result);
                    
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'ielh_import_texts',
                            nonce: '<?php echo wp_create_nonce('ielh_import_texts'); ?>',
                            texts_data: JSON.stringify(data)
                        },
                        success: function(response) {
                            if (response.success) {
                                alert('تم استيراد النصوص بنجاح');
                                location.reload();
                            } else {
                                alert('فشل في استيراد النصوص: ' + response.data);
                            }
                        }
                    });
                } catch (error) {
                    alert('ملف غير صحيح');
                }
            };
            reader.readAsText(file);
        }
    });
    
    // إعادة تعيين الإعدادات
    $('#reset-settings').on('click', function() {
        if (confirm('هل أنت متأكد من إعادة تعيين جميع الإعدادات؟')) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'ielh_reset_settings',
                    nonce: '<?php echo wp_create_nonce('ielh_reset_settings'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('تم إعادة تعيين الإعدادات بنجاح');
                        location.reload();
                    } else {
                        alert('فشل في إعادة تعيين الإعدادات');
                    }
                }
            });
        }
    });
});
</script>