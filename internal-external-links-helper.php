<?php
/**
 * Plugin Name: مساعدة إنشاء الروابط الداخلية والخارجية
 * Plugin URI: https://fjomah.com
 * Description: إضافة ووردبريس تساعد في إنشاء الروابط الداخلية والخارجية بسهولة من خلال واجهة بسيطة ومرنة
 * Version: 1.0.1
 * Author: فوزي جمعة
 * Author URI: https://fjomah.com
 * Text Domain: internal-external-links-helper
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * Contact: +201111933193 (WhatsApp)
 * Website: fjomah.com
 */

// منع الوصول المباشر للملف
if (!defined('ABSPATH')) {
    exit;
}

// تعريف الثوابت الأساسية للإضافة
define('IELH_PLUGIN_URL', plugin_dir_url(__FILE__));
define('IELH_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('IELH_PLUGIN_VERSION', '1.0.1');
define('IELH_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * الكلاس الرئيسي للإضافة
 */
class InternalExternalLinksHelper {
    
    /**
     * المتغير الوحيد للكلاس
     */
    private static $instance = null;
    
    /**
     * إنشاء أو إرجاع المتغير الوحيد للكلاس
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * البناء الأساسي للكلاس
     */
    private function __construct() {
        $this->init_hooks();
    }
    
    /**
     * تهيئة الخطافات الأساسية
     */
    private function init_hooks() {
        // تفعيل الإضافة
        register_activation_hook(__FILE__, array($this, 'activate'));
        
        // إلغاء تفعيل الإضافة
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // تحميل الإضافة
        add_action('plugins_loaded', array($this, 'load_plugin'));
        
        // تحميل ملف اختبار الإعدادات (فقط في وضع التطوير)
        if (defined('WP_DEBUG') && WP_DEBUG) {
            include_once IELH_PLUGIN_PATH . 'test-settings.php';
        }
        
        // إضافة القائمة الإدارية
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // تحميل الملفات والأنماط في لوحة التحكم
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // إضافة زر في محرر ووردبريس التقليدي
        add_action('media_buttons', array($this, 'add_media_button'));
        
        // معالجة طلبات AJAX
        add_action('wp_ajax_ielh_get_posts', array($this, 'ajax_get_posts'));
        add_action('wp_ajax_ielh_save_link_text', array($this, 'ajax_save_link_text'));
        add_action('wp_ajax_ielh_get_link_texts', array($this, 'ajax_get_link_texts'));
        add_action('wp_ajax_ielh_delete_link_text', array($this, 'ajax_delete_link_text'));
        add_action('wp_ajax_ielh_export_texts', array($this, 'ajax_export_texts'));
        add_action('wp_ajax_ielh_import_texts', array($this, 'ajax_import_texts'));
        add_action('wp_ajax_ielh_reset_settings', array($this, 'ajax_reset_settings'));
        
        // إضافة ودجيت نسخ الروابط
        add_action('add_meta_boxes', array($this, 'add_link_copy_meta_box'));
    }
    
    /**
     * تفعيل الإضافة
     */
    public function activate() {
        // إنشاء جدول النصوص المحفوظة
        $this->create_tables();
        
        // إضافة النصوص الافتراضية
        $this->add_default_link_texts();
    }
    
    /**
     * إلغاء تفعيل الإضافة
     */
    public function deactivate() {
        // تنظيف مؤقت إذا لزم الأمر
    }
    
    /**
     * تحميل الإضافة
     */
    public function load_plugin() {
        // تحميل ملفات الترجمة
        load_plugin_textdomain('internal-external-links-helper', false, dirname(IELH_PLUGIN_BASENAME) . '/languages');
    }
    
    /**
     * إنشاء الجداول المطلوبة
     */
    private function create_tables() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ielh_link_texts';
        
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            text_content text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * إضافة النصوص الافتراضية
     */
    private function add_default_link_texts() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ielh_link_texts';
        
        // التحقق من وجود نصوص مسبقاً
        $existing_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        if ($existing_count == 0) {
            $default_texts = array(
                'شاهد المزيد من المعلومات من هنا:',
                'اقرأ أيضاً:',
                'للمزيد من التفاصيل:',
                'مقال ذو صلة:',
                'قد يهمك أيضاً:'
            );
            
            foreach ($default_texts as $text) {
                $wpdb->insert(
                    $table_name,
                    array('text_content' => $text),
                    array('%s')
                );
            }
        }
    }
    
    /**
     * إضافة القائمة الإدارية
     */
    public function add_admin_menu() {
        // القائمة الرئيسية
        add_menu_page(
            'مساعدة الروابط الداخلية والخارجية',
            'مساعدة الروابط',
            'manage_options',
            'ielh-main',
            array($this, 'admin_page_main'),
            'dashicons-admin-links',
            30
        );
        
        // صفحة إدارة النصوص
        add_submenu_page(
            'ielh-main',
            'إدارة النصوص',
            'إدارة النصوص',
            'manage_options',
            'ielh-texts',
            array($this, 'admin_page_texts')
        );
        
        // صفحة الإعدادات
        add_submenu_page(
            'ielh-main',
            'الإعدادات',
            'الإعدادات',
            'manage_options',
            'ielh-settings',
            array($this, 'admin_page_settings')
        );
    }
    
    /**
     * تحميل الملفات والأنماط في لوحة التحكم
     */
    public function admin_enqueue_scripts($hook) {
        // تحميل الملفات فقط في صفحات الإضافة أو في محرر المقالات
        if (strpos($hook, 'ielh-') !== false || $hook === 'post.php' || $hook === 'post-new.php') {
            wp_enqueue_script('ielh-admin-js', IELH_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), IELH_PLUGIN_VERSION, true);
            wp_enqueue_style('ielh-admin-css', IELH_PLUGIN_URL . 'assets/css/admin.css', array(), IELH_PLUGIN_VERSION);
            
            // جلب إعدادات الإضافة
            $default_settings = array(
                'default_link_target' => '_self',
                'link_css_class' => '',
                'enable_nofollow' => 0
            );
            $settings = wp_parse_args(get_option('ielh_settings', array()), $default_settings);
            
            // تمرير البيانات لـ JavaScript
            wp_localize_script('ielh-admin-js', 'ielh_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('ielh_nonce'),
                'settings' => array(
                    'default_link_target' => $settings['default_link_target'],
                    'link_css_class' => $settings['link_css_class'],
                    'enable_nofollow' => $settings['enable_nofollow']
                ),
                'strings' => array(
                    'select_post' => 'اختر مقالة أو صفحة',
                    'select_text' => 'اختر النص',
                    'insert_link' => 'إدراج الرابط',
                    'cancel' => 'إلغاء',
                    'loading' => 'جاري التحميل...',
                    'error' => 'حدث خطأ، يرجى المحاولة مرة أخرى'
                )
            ));
        }
    }
    
    /**
     * إضافة زر في محرر ووردبريس
     */
    public function add_media_button() {
        global $post;
        
        if (!$post || !in_array($post->post_type, array('post', 'page'))) {
            return;
        }
        
        echo '<button type="button" id="ielh-insert-link-btn" class="button" data-editor="content">';
        echo '<span class="dashicons dashicons-admin-links" style="margin-top: 3px;"></span> إدراج رابط داخلي';
        echo '</button>';
    }
    
    /**
     * إضافة ودجيت نسخ الروابط
     */
    public function add_link_copy_meta_box() {
        add_meta_box(
            'ielh-link-copy-widget',
            'نسخ رابط المقالة بتنسيقات مختلفة',
            array($this, 'link_copy_meta_box_callback'),
            array('post', 'page'),
            'side',
            'default'
        );
    }
    
    /**
     * محتوى ودجيت نسخ الروابط
     */
    public function link_copy_meta_box_callback($post) {
        $post_title = get_the_title($post->ID);
        $post_url = get_permalink($post->ID);
        
        // إذا كانت المقالة لم تُنشر بعد، استخدم رابط المعاينة
        if ($post->post_status !== 'publish') {
            $post_url = get_preview_post_link($post->ID);
            if (!$post_url) {
                $post_url = admin_url('post.php?post=' . $post->ID . '&action=edit');
            }
        }
        
        // تنسيقات الروابط المختلفة
        $formats = array(
            'html' => array(
                'label' => 'HTML',
                'code' => '<a href="' . esc_url($post_url) . '">' . esc_html($post_title) . '</a>'
            ),
            'bbcode' => array(
                'label' => 'BBCode (المنتديات)',
                'code' => '[url=' . esc_url($post_url) . ']' . esc_html($post_title) . '[/url]'
            ),
            'markdown' => array(
                'label' => 'Markdown',
                'code' => '[' . esc_html($post_title) . '](' . esc_url($post_url) . ')'
            ),
            'plain' => array(
                'label' => 'نص عادي',
                'code' => esc_html($post_title) . ' - ' . esc_url($post_url)
            ),
            'url_only' => array(
                'label' => 'الرابط فقط',
                'code' => esc_url($post_url)
            )
        );
        
        echo '<div id="ielh-link-copy-widget">';
        
        if ($post->post_status !== 'publish') {
            echo '<div class="notice notice-info inline"><p><strong>ملاحظة:</strong> المقالة لم تُنشر بعد. الروابط المعروضة هي روابط معاينة.</p></div>';
        }
        
        foreach ($formats as $format_key => $format) {
            echo '<div class="ielh-format-group">';
            echo '<label><strong>' . $format['label'] . ':</strong></label>';
            echo '<div class="ielh-code-container">';
            echo '<textarea readonly class="ielh-code-field" data-format="' . $format_key . '">' . $format['code'] . '</textarea>';
            echo '<button type="button" class="button ielh-copy-btn" data-format="' . $format_key . '" title="نسخ">';
            echo '<span class="dashicons dashicons-clipboard"></span>';
            echo '</button>';
            echo '</div>';
            echo '</div>';
        }
        
        echo '<div id="ielh-copy-success" class="notice notice-success inline" style="display: none;">';
        echo '<p>تم نسخ الكود بنجاح!</p>';
        echo '</div>';
        
        echo '</div>';
        
        // إضافة الأنماط والسكريبت
        $this->add_link_copy_styles_and_scripts();
    }
    
    /**
     * إضافة الأنماط والسكريبت لودجيت نسخ الروابط
     */
    private function add_link_copy_styles_and_scripts() {
        ?>
        <style>
        #ielh-link-copy-widget .ielh-format-group {
            margin-bottom: 15px;
        }
        
        #ielh-link-copy-widget .ielh-format-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        #ielh-link-copy-widget .ielh-code-container {
            display: flex;
            gap: 5px;
        }
        
        #ielh-link-copy-widget .ielh-code-field {
            flex: 1;
            min-height: 60px;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            resize: vertical;
            background-color: #f9f9f9;
        }
        
        #ielh-link-copy-widget .ielh-copy-btn {
            padding: 8px 12px;
            height: auto;
            min-height: 60px;
            border-radius: 4px;
        }
        
        #ielh-link-copy-widget .ielh-copy-btn:hover {
            background-color: #0073aa;
            color: white;
        }
        
        #ielh-link-copy-widget .ielh-copy-btn .dashicons {
            font-size: 16px;
            width: 16px;
            height: 16px;
        }
        
        #ielh-copy-success {
            margin-top: 10px;
            padding: 8px 12px;
        }
        </style>
        
        <script>
        jQuery(document).ready(function($) {
            // نسخ الكود عند الضغط على الزر
            $('.ielh-copy-btn').on('click', function() {
                var format = $(this).data('format');
                var textarea = $('.ielh-code-field[data-format="' + format + '"]');
                
                // تحديد النص ونسخه
                textarea.select();
                textarea[0].setSelectionRange(0, 99999); // للهواتف المحمولة
                
                try {
                    document.execCommand('copy');
                    
                    // إظهار رسالة النجاح
                    $('#ielh-copy-success').fadeIn().delay(2000).fadeOut();
                    
                    // تغيير لون الزر مؤقتاً
                    $(this).css('background-color', '#46b450').css('color', 'white');
                    setTimeout(function() {
                        $('.ielh-copy-btn').css('background-color', '').css('color', '');
                    }, 1000);
                    
                } catch (err) {
                    alert('فشل في نسخ الكود. يرجى نسخه يدوياً.');
                }
            });
            
            // تحديد النص عند الضغط على textarea
            $('.ielh-code-field').on('click', function() {
                $(this).select();
            });
        });
        </script>
        <?php
    }
    
    /**
     * الصفحة الرئيسية للإضافة
     */
    public function admin_page_main() {
        include IELH_PLUGIN_PATH . 'admin/pages/main.php';
    }
    
    /**
     * صفحة إدارة النصوص
     */
    public function admin_page_texts() {
        include IELH_PLUGIN_PATH . 'admin/pages/texts.php';
    }
    
    /**
     * صفحة الإعدادات
     */
    public function admin_page_settings() {
        include IELH_PLUGIN_PATH . 'admin/pages/settings.php';
    }
    
    /**
     * معالجة طلب AJAX لجلب المقالات
     */
    public function ajax_get_posts() {
        check_ajax_referer('ielh_nonce', 'nonce');
        
        $search = sanitize_text_field($_POST['search'] ?? '');
        $post_type = sanitize_text_field($_POST['post_type'] ?? 'post');
        
        $args = array(
            'post_type' => array('post', 'page'),
            'post_status' => 'publish',
            'posts_per_page' => 20,
            'orderby' => 'title',
            'order' => 'ASC'
        );
        
        if (!empty($search)) {
            $args['s'] = $search;
        }
        
        $posts = get_posts($args);
        $results = array();
        
        foreach ($posts as $post) {
            $results[] = array(
                'id' => $post->ID,
                'title' => $post->post_title,
                'url' => get_permalink($post->ID),
                'type' => $post->post_type
            );
        }
        
        wp_send_json_success($results);
    }
    
    /**
     * معالجة طلب AJAX لحفظ نص جديد
     */
    public function ajax_save_link_text() {
        check_ajax_referer('ielh_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('غير مصرح لك بهذا الإجراء');
        }
        
        $text_content = sanitize_textarea_field($_POST['text_content'] ?? '');
        
        if (empty($text_content)) {
            wp_send_json_error('النص مطلوب');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ielh_link_texts';
        
        $result = $wpdb->insert(
            $table_name,
            array('text_content' => $text_content),
            array('%s')
        );
        
        if ($result !== false) {
            wp_send_json_success('تم حفظ النص بنجاح');
        } else {
            wp_send_json_error('فشل في حفظ النص');
        }
    }
    
    /**
     * معالجة طلب AJAX لجلب النصوص المحفوظة
     */
    public function ajax_get_link_texts() {
        check_ajax_referer('ielh_nonce', 'nonce');
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ielh_link_texts';
        
        $texts = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        
        wp_send_json_success($texts);
    }
    
    /**
     * معالجة طلب AJAX لحذف نص
     */
    public function ajax_delete_link_text() {
        check_ajax_referer('ielh_nonce', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('غير مصرح لك بهذا الإجراء');
        }
        
        $text_id = intval($_POST['text_id'] ?? 0);
        
        if ($text_id <= 0) {
            wp_send_json_error('معرف النص غير صحيح');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ielh_link_texts';
        
        $result = $wpdb->delete(
            $table_name,
            array('id' => $text_id),
            array('%d')
        );
        
        if ($result !== false) {
            wp_send_json_success('تم حذف النص بنجاح');
        } else {
            wp_send_json_error('فشل في حذف النص');
        }
    }
    
    /**
     * معالجة طلب AJAX لتصدير النصوص
     */
    public function ajax_export_texts() {
        check_ajax_referer('ielh_export_texts', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('غير مصرح لك بهذا الإجراء');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ielh_link_texts';
        
        $texts = $wpdb->get_results("SELECT text_content, created_at FROM $table_name ORDER BY created_at DESC");
        
        $export_data = array(
            'plugin' => 'Internal External Links Helper',
            'version' => IELH_PLUGIN_VERSION,
            'export_date' => current_time('mysql'),
            'texts' => $texts
        );
        
        wp_send_json_success($export_data);
    }
    
    /**
     * معالجة طلب AJAX لاستيراد النصوص
     */
    public function ajax_import_texts() {
        check_ajax_referer('ielh_import_texts', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('غير مصرح لك بهذا الإجراء');
        }
        
        $texts_data = sanitize_textarea_field($_POST['texts_data'] ?? '');
        
        if (empty($texts_data)) {
            wp_send_json_error('لا توجد بيانات للاستيراد');
        }
        
        $data = json_decode($texts_data, true);
        
        if (!$data || !isset($data['texts']) || !is_array($data['texts'])) {
            wp_send_json_error('تنسيق الملف غير صحيح');
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'ielh_link_texts';
        
        $imported_count = 0;
        
        foreach ($data['texts'] as $text) {
            if (isset($text->text_content) && !empty($text->text_content)) {
                // التحقق من عدم وجود النص مسبقاً
                $existing = $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(*) FROM $table_name WHERE text_content = %s",
                    $text->text_content
                ));
                
                if ($existing == 0) {
                    $result = $wpdb->insert(
                        $table_name,
                        array('text_content' => $text->text_content),
                        array('%s')
                    );
                    
                    if ($result !== false) {
                        $imported_count++;
                    }
                }
            }
        }
        
        wp_send_json_success("تم استيراد {$imported_count} نص بنجاح");
    }
    
    /**
     * معالجة طلب AJAX لإعادة تعيين الإعدادات
     */
    public function ajax_reset_settings() {
        check_ajax_referer('ielh_reset_settings', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('غير مصرح لك بهذا الإجراء');
        }
        
        // حذف الإعدادات
        delete_option('ielh_settings');
        
        wp_send_json_success('تم إعادة تعيين الإعدادات بنجاح');
    }
}

// تشغيل الإضافة
InternalExternalLinksHelper::get_instance();

?>