<?php
/**
 * ملف اختبار الإضافة
 * 
 * هذا الملف يحتوي على اختبارات بسيطة للتأكد من عمل الإضافة بشكل صحيح
 * 
 * @package Internal External Links Helper
 * @author فوزي جمعة
 */

// منع الوصول المباشر
if (!defined('ABSPATH')) {
    exit;
}

/**
 * كلاس اختبار الإضافة
 */
class IELH_Plugin_Test {
    
    /**
     * تشغيل جميع الاختبارات
     */
    public static function run_tests() {
        $results = array();
        
        $results['database'] = self::test_database();
        $results['constants'] = self::test_constants();
        $results['files'] = self::test_files();
        $results['hooks'] = self::test_hooks();
        
        return $results;
    }
    
    /**
     * اختبار قاعدة البيانات
     */
    private static function test_database() {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'ielh_link_texts';
        
        // التحقق من وجود الجدول
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        
        if (!$table_exists) {
            return array(
                'status' => 'error',
                'message' => 'جدول قاعدة البيانات غير موجود'
            );
        }
        
        // التحقق من وجود البيانات الافتراضية
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        
        return array(
            'status' => 'success',
            'message' => "جدول قاعدة البيانات موجود ويحتوي على $count نص"
        );
    }
    
    /**
     * اختبار الثوابت
     */
    private static function test_constants() {
        $required_constants = array(
            'IELH_PLUGIN_URL',
            'IELH_PLUGIN_PATH',
            'IELH_PLUGIN_VERSION',
            'IELH_PLUGIN_BASENAME'
        );
        
        $missing_constants = array();
        
        foreach ($required_constants as $constant) {
            if (!defined($constant)) {
                $missing_constants[] = $constant;
            }
        }
        
        if (!empty($missing_constants)) {
            return array(
                'status' => 'error',
                'message' => 'الثوابت المفقودة: ' . implode(', ', $missing_constants)
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'جميع الثوابت المطلوبة موجودة'
        );
    }
    
    /**
     * اختبار الملفات
     */
    private static function test_files() {
        $required_files = array(
            IELH_PLUGIN_PATH . 'admin/pages/main.php',
            IELH_PLUGIN_PATH . 'admin/pages/texts.php',
            IELH_PLUGIN_PATH . 'admin/pages/settings.php',
            IELH_PLUGIN_PATH . 'assets/css/admin.css',
            IELH_PLUGIN_PATH . 'assets/js/admin.js'
        );
        
        $missing_files = array();
        
        foreach ($required_files as $file) {
            if (!file_exists($file)) {
                $missing_files[] = basename($file);
            }
        }
        
        if (!empty($missing_files)) {
            return array(
                'status' => 'error',
                'message' => 'الملفات المفقودة: ' . implode(', ', $missing_files)
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'جميع الملفات المطلوبة موجودة'
        );
    }
    
    /**
     * اختبار الخطافات
     */
    private static function test_hooks() {
        $plugin_instance = InternalExternalLinksHelper::get_instance();
        
        if (!$plugin_instance) {
            return array(
                'status' => 'error',
                'message' => 'لم يتم تحميل الإضافة بشكل صحيح'
            );
        }
        
        // التحقق من وجود القائمة الإدارية
        $admin_menu_exists = has_action('admin_menu');
        
        if (!$admin_menu_exists) {
            return array(
                'status' => 'warning',
                'message' => 'قد تكون هناك مشكلة في تحميل القائمة الإدارية'
            );
        }
        
        return array(
            'status' => 'success',
            'message' => 'تم تحميل الإضافة والخطافات بنجاح'
        );
    }
    
    /**
     * عرض نتائج الاختبار
     */
    public static function display_test_results() {
        if (!current_user_can('manage_options')) {
            return;
        }
        
        $results = self::run_tests();
        
        echo '<div class="wrap">';
        echo '<h1>نتائج اختبار إضافة مساعدة الروابط</h1>';
        
        foreach ($results as $test_name => $result) {
            $class = 'notice notice-' . ($result['status'] === 'success' ? 'success' : ($result['status'] === 'warning' ? 'warning' : 'error'));
            echo '<div class="' . $class . '"><p><strong>' . ucfirst($test_name) . ':</strong> ' . $result['message'] . '</p></div>';
        }
        
        echo '</div>';
    }
}

// إضافة صفحة اختبار في قائمة الأدوات (فقط للمطورين)
if (defined('WP_DEBUG') && WP_DEBUG) {
    add_action('admin_menu', function() {
        add_management_page(
            'اختبار إضافة الروابط',
            'اختبار إضافة الروابط',
            'manage_options',
            'ielh-test',
            array('IELH_Plugin_Test', 'display_test_results')
        );
    });
}

?>