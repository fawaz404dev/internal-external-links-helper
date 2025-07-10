<?php
/**
 * اختبار إعدادات الروابط
 * 
 * هذا الملف يختبر ما إذا كانت إعدادات الروابط تعمل بشكل صحيح
 * 
 * @package Internal External Links Helper
 * @author فوزي جمعة
 */

// منع الوصول المباشر
if (!defined('ABSPATH')) {
    exit;
}

/**
 * اختبار إعدادات الروابط
 */
function ielh_test_link_settings() {
    // جلب الإعدادات
    $default_settings = array(
        'default_link_target' => '_self',
        'link_css_class' => '',
        'enable_nofollow' => 0
    );
    $settings = wp_parse_args(get_option('ielh_settings', array()), $default_settings);
    
    echo '<div class="wrap">';
    echo '<h1>اختبار إعدادات الروابط</h1>';
    
    echo '<div class="notice notice-info">';
    echo '<p><strong>الإعدادات الحالية:</strong></p>';
    echo '<ul>';
    echo '<li><strong>هدف الرابط الافتراضي:</strong> ' . esc_html($settings['default_link_target']) . '</li>';
    echo '<li><strong>فئة CSS:</strong> ' . (empty($settings['link_css_class']) ? 'غير محدد' : esc_html($settings['link_css_class'])) . '</li>';
    echo '<li><strong>إضافة nofollow:</strong> ' . ($settings['enable_nofollow'] ? 'مُفعل' : 'غير مُفعل') . '</li>';
    echo '</ul>';
    echo '</div>';
    
    // مثال على رابط سيتم إنشاؤه
    echo '<div class="notice notice-success">';
    echo '<p><strong>مثال على الرابط الذي سيتم إنشاؤه:</strong></p>';
    
    $link_attributes = 'href="https://example.com"';
    
    if ($settings['default_link_target'] && $settings['default_link_target'] !== '_self') {
        $link_attributes .= ' target="' . esc_attr($settings['default_link_target']) . '"';
    }
    
    if (!empty($settings['link_css_class'])) {
        $link_attributes .= ' class="' . esc_attr($settings['link_css_class']) . '"';
    }
    
    if ($settings['enable_nofollow']) {
        $link_attributes .= ' rel="nofollow"';
    }
    
    $example_link = 'النص المختار <a ' . $link_attributes . '>عنوان المقالة</a>';
    
    echo '<code>' . esc_html($example_link) . '</code>';
    echo '</div>';
    
    echo '</div>';
}

// إضافة صفحة الاختبار في القائمة الإدارية (فقط إذا كان WP_DEBUG مُفعل)
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_menu', function() {
        add_submenu_page(
            'ielh-main',
            'اختبار الإعدادات',
            'اختبار الإعدادات',
            'manage_options',
            'ielh-test-settings',
            'ielh_test_link_settings'
        );
    });
}