/**
 * ملف JavaScript الخاص بلوحة التحكم
 * 
 * @package Internal External Links Helper
 * @author فوزي جمعة
 */

(function($) {
    'use strict';
    
    // متغيرات عامة
    let currentEditor = null;
    let selectedPost = null;
    let selectedText = null;
    let linkTexts = [];
    let searchTimeout = null;
    
    // تهيئة الإضافة عند تحميل الصفحة
    $(document).ready(function() {
        initPlugin();
    });
    
    /**
     * تهيئة الإضافة
     */
    function initPlugin() {
        // إنشاء النافذة المنبثقة
        createModal();
        
        // ربط الأحداث
        bindEvents();
        
        // تحميل النصوص المحفوظة
        loadLinkTexts();
    }
    
    /**
     * إنشاء النافذة المنبثقة
     */
    function createModal() {
        const modalHtml = `
            <div id="ielh-modal" class="ielh-modal">
                <div class="ielh-modal-content">
                    <div class="ielh-modal-header">
                        <h3 class="ielh-modal-title">إدراج رابط داخلي</h3>
                        <button type="button" class="ielh-modal-close">&times;</button>
                    </div>
                    <div class="ielh-modal-body">
                        <!-- اختيار النص -->
                        <div class="ielh-form-group">
                            <label for="ielh-text-select">اختر النص:</label>
                            <select id="ielh-text-select">
                                <option value="">-- اختر النص --</option>
                            </select>
                        </div>
                        
                        <!-- إضافة نص جديد -->
                        <div class="ielh-form-group">
                            <label for="ielh-custom-text">أو أدخل نص جديد:</label>
                            <input type="text" id="ielh-custom-text" placeholder="مثال: شاهد المزيد من المعلومات من هنا:">
                        </div>
                        
                        <!-- البحث عن المقالات -->
                        <div class="ielh-form-group">
                            <label for="ielh-post-search">البحث عن مقالة أو صفحة:</label>
                            <div class="ielh-search-container">
                                <span class="dashicons dashicons-search ielh-search-icon"></span>
                                <input type="search" id="ielh-post-search" class="ielh-search-input" placeholder="ابحث عن مقالة أو صفحة...">
                            </div>
                        </div>
                        
                        <!-- قائمة المقالات -->
                        <div class="ielh-form-group">
                            <label>اختر المقالة أو الصفحة:</label>
                            <div id="ielh-posts-container">
                                <div class="ielh-loading">
                                    <div class="dashicons dashicons-update"></div>
                                    <p>جاري تحميل المقالات...</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- معاينة الرابط -->
                        <div id="ielh-preview-container" class="ielh-form-group" style="display: none;">
                            <label>معاينة الرابط:</label>
                            <div class="ielh-preview-text">
                                <div class="ielh-preview-label">سيتم إدراج النص التالي:</div>
                                <div id="ielh-preview-content" class="ielh-preview-content"></div>
                            </div>
                        </div>
                    </div>
                    <div class="ielh-modal-footer">
                        <button type="button" id="ielh-insert-btn" class="ielh-btn ielh-btn-primary" disabled>
                            <span class="dashicons dashicons-plus"></span>
                            إدراج الرابط
                        </button>
                        <button type="button" id="ielh-cancel-btn" class="ielh-btn ielh-btn-secondary">
                            <span class="dashicons dashicons-no"></span>
                            إلغاء
                        </button>
                    </div>
                </div>
            </div>
        `;
        
        $('body').append(modalHtml);
    }
    
    /**
     * ربط الأحداث
     */
    function bindEvents() {
        // زر إدراج الرابط في المحرر
        $(document).on('click', '#ielh-insert-link-btn', function() {
            currentEditor = $(this).data('editor') || 'content';
            openModal();
        });
        
        // إغلاق النافذة المنبثقة
        $(document).on('click', '.ielh-modal-close, #ielh-cancel-btn', closeModal);
        $(document).on('click', '.ielh-modal', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
        
        // البحث عن المقالات
        $(document).on('input', '#ielh-post-search', function() {
            const searchTerm = $(this).val();
            
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(function() {
                searchPosts(searchTerm);
            }, 300);
        });
        
        // اختيار مقالة
        $(document).on('click', '.ielh-post-item', function() {
            $('.ielh-post-item').removeClass('selected');
            $(this).addClass('selected');
            
            selectedPost = {
                id: $(this).data('id'),
                title: $(this).data('title'),
                url: $(this).data('url')
            };
            
            updatePreview();
            updateInsertButton();
        });
        
        // تغيير النص المختار
        $(document).on('change', '#ielh-text-select', function() {
            const textValue = $(this).val();
            if (textValue) {
                $('#ielh-custom-text').val('');
                selectedText = textValue;
            } else {
                selectedText = null;
            }
            updatePreview();
            updateInsertButton();
        });
        
        // إدخال نص مخصص
        $(document).on('input', '#ielh-custom-text', function() {
            const customText = $(this).val().trim();
            if (customText) {
                $('#ielh-text-select').val('');
                selectedText = customText;
            } else {
                selectedText = null;
            }
            updatePreview();
            updateInsertButton();
        });
        
        // إدراج الرابط
        $(document).on('click', '#ielh-insert-btn', insertLink);
        
        // مفتاح Escape لإغلاق النافذة
        $(document).on('keydown', function(e) {
            if (e.keyCode === 27 && $('#ielh-modal').is(':visible')) {
                closeModal();
            }
        });
    }
    
    /**
     * فتح النافذة المنبثقة
     */
    function openModal() {
        $('#ielh-modal').fadeIn(300);
        resetModal();
        searchPosts('');
        
        // التركيز على حقل البحث
        setTimeout(function() {
            $('#ielh-post-search').focus();
        }, 350);
    }
    
    /**
     * إغلاق النافذة المنبثقة
     */
    function closeModal() {
        $('#ielh-modal').fadeOut(300);
        resetModal();
    }
    
    /**
     * إعادة تعيين النافذة المنبثقة
     */
    function resetModal() {
        selectedPost = null;
        selectedText = null;
        
        $('#ielh-text-select').val('');
        $('#ielh-custom-text').val('');
        $('#ielh-post-search').val('');
        $('#ielh-posts-container').html('<div class="ielh-loading"><div class="dashicons dashicons-update"></div><p>جاري تحميل المقالات...</p></div>');
        $('#ielh-preview-container').hide();
        $('#ielh-insert-btn').prop('disabled', true);
    }
    
    /**
     * تحميل النصوص المحفوظة
     */
    function loadLinkTexts() {
        $.ajax({
            url: ielh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ielh_get_link_texts',
                nonce: ielh_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    linkTexts = response.data;
                    populateTextSelect();
                }
            },
            error: function() {
                console.error('فشل في تحميل النصوص المحفوظة');
            }
        });
    }
    
    /**
     * ملء قائمة النصوص
     */
    function populateTextSelect() {
        const $select = $('#ielh-text-select');
        $select.find('option:not(:first)').remove();
        
        linkTexts.forEach(function(text) {
            $select.append(`<option value="${text.text_content}">${text.text_content}</option>`);
        });
    }
    
    /**
     * البحث عن المقالات
     */
    function searchPosts(searchTerm) {
        const $container = $('#ielh-posts-container');
        
        // إظهار مؤشر التحميل
        $container.html('<div class="ielh-loading"><div class="dashicons dashicons-update"></div><p>جاري البحث...</p></div>');
        
        $.ajax({
            url: ielh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ielh_get_posts',
                nonce: ielh_ajax.nonce,
                search: searchTerm
            },
            success: function(response) {
                if (response.success) {
                    displayPosts(response.data);
                } else {
                    showError('فشل في تحميل المقالات');
                }
            },
            error: function() {
                showError('حدث خطأ في الاتصال');
            }
        });
    }
    
    /**
     * عرض المقالات
     */
    function displayPosts(posts) {
        const $container = $('#ielh-posts-container');
        
        if (posts.length === 0) {
            $container.html(`
                <div class="ielh-empty-results">
                    <div class="dashicons dashicons-search"></div>
                    <p>لم يتم العثور على نتائج</p>
                </div>
            `);
            return;
        }
        
        let html = '<div class="ielh-posts-list">';
        
        posts.forEach(function(post) {
            html += `
                <div class="ielh-post-item" 
                     data-id="${post.id}" 
                     data-title="${post.title}" 
                     data-url="${post.url}"
                     tabindex="0">
                    <div class="ielh-post-info">
                        <div class="ielh-post-title">${post.title}</div>
                        <div class="ielh-post-meta">
                            <span class="ielh-post-type">${post.type === 'post' ? 'مقالة' : 'صفحة'}</span>
                            <span class="ielh-post-url">${post.url}</span>
                        </div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        $container.html(html);
    }
    
    /**
     * عرض رسالة خطأ
     */
    function showError(message) {
        const $container = $('#ielh-posts-container');
        $container.html(`
            <div class="ielh-error">
                <span class="dashicons dashicons-warning"></span>
                ${message}
            </div>
        `);
    }
    
    /**
     * تحديث معاينة الرابط
     */
    function updatePreview() {
        if (selectedText && selectedPost) {
            // بناء خصائص الرابط بناءً على الإعدادات
            let linkAttributes = `href="${selectedPost.url}"`;
            
            // إضافة target إذا كان مُحدد
            if (ielh_ajax.settings.default_link_target && ielh_ajax.settings.default_link_target !== '_self') {
                linkAttributes += ` target="${ielh_ajax.settings.default_link_target}"`;
            }
            
            // إضافة class إذا كان مُحدد
            if (ielh_ajax.settings.link_css_class) {
                linkAttributes += ` class="${ielh_ajax.settings.link_css_class} ielh-preview-link"`;
            } else {
                linkAttributes += ` class="ielh-preview-link"`;
            }
            
            // إضافة nofollow إذا كان مُفعل
            if (ielh_ajax.settings.enable_nofollow == 1) {
                linkAttributes += ` rel="nofollow"`;
            }
            
            const previewHtml = `${selectedText} <a ${linkAttributes}>${selectedPost.title}</a>`;
            $('#ielh-preview-content').html(previewHtml);
            $('#ielh-preview-container').show();
        } else {
            $('#ielh-preview-container').hide();
        }
    }
    
    /**
     * تحديث حالة زر الإدراج
     */
    function updateInsertButton() {
        const canInsert = selectedText && selectedPost;
        $('#ielh-insert-btn').prop('disabled', !canInsert);
    }
    
    /**
     * إدراج الرابط في المحرر
     */
    function insertLink() {
        if (!selectedText || !selectedPost) {
            return;
        }
        
        // بناء خصائص الرابط بناءً على الإعدادات
        let linkAttributes = `href="${selectedPost.url}"`;
        
        // إضافة target إذا كان مُحدد
        if (ielh_ajax.settings.default_link_target && ielh_ajax.settings.default_link_target !== '_self') {
            linkAttributes += ` target="${ielh_ajax.settings.default_link_target}"`;
        }
        
        // إضافة class إذا كان مُحدد
        if (ielh_ajax.settings.link_css_class) {
            linkAttributes += ` class="${ielh_ajax.settings.link_css_class}"`;
        }
        
        // إضافة nofollow إذا كان مُفعل
        if (ielh_ajax.settings.enable_nofollow == 1) {
            linkAttributes += ` rel="nofollow"`;
        }
        
        // إنشاء HTML للرابط
        const linkHtml = `${selectedText} <a ${linkAttributes}>${selectedPost.title}</a>`;
        
        // إدراج في المحرر
        if (typeof tinyMCE !== 'undefined' && tinyMCE.get(currentEditor)) {
            // محرر TinyMCE
            const editor = tinyMCE.get(currentEditor);
            editor.insertContent(linkHtml);
        } else {
            // المحرر النصي
            const $textarea = $('#' + currentEditor);
            if ($textarea.length) {
                const cursorPos = $textarea[0].selectionStart;
                const textBefore = $textarea.val().substring(0, cursorPos);
                const textAfter = $textarea.val().substring(cursorPos);
                $textarea.val(textBefore + linkHtml + textAfter);
                
                // تحديث موضع المؤشر
                const newPos = cursorPos + linkHtml.length;
                $textarea[0].setSelectionRange(newPos, newPos);
                $textarea.focus();
            }
        }
        
        // حفظ النص المخصص إذا كان جديداً
        if (selectedText && !linkTexts.find(text => text.text_content === selectedText)) {
            saveCustomText(selectedText);
        }
        
        // إغلاق النافذة
        closeModal();
        
        // إظهار رسالة نجاح
        showNotice('تم إدراج الرابط بنجاح!', 'success');
    }
    
    /**
     * حفظ نص مخصص
     */
    function saveCustomText(text) {
        $.ajax({
            url: ielh_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'ielh_save_link_text',
                nonce: ielh_ajax.nonce,
                text_content: text
            },
            success: function(response) {
                if (response.success) {
                    // إضافة النص للقائمة المحلية
                    linkTexts.push({
                        text_content: text,
                        created_at: new Date().toISOString()
                    });
                    populateTextSelect();
                }
            }
        });
    }
    
    /**
     * إظهار رسالة تنبيه
     */
    function showNotice(message, type = 'info') {
        const noticeHtml = `
            <div class="notice notice-${type} is-dismissible ielh-notice-temp">
                <p>${message}</p>
            </div>
        `;
        
        $('.wrap h1').after(noticeHtml);
        
        // إزالة الرسالة بعد 3 ثوان
        setTimeout(function() {
            $('.ielh-notice-temp').fadeOut(300, function() {
                $(this).remove();
            });
        }, 3000);
    }
    
    // تصدير الوظائف للاستخدام الخارجي
    window.IELH = {
        openModal: openModal,
        closeModal: closeModal,
        insertLink: insertLink
    };
    
})(jQuery);

/**
 * وظائف إضافية للتعامل مع المحرر الكلاسيكي
 */
(function() {
    'use strict';
    
    // إضافة زر في شريط أدوات المحرر الكلاسيكي
    if (typeof QTags !== 'undefined') {
        QTags.addButton(
            'ielh_link',
            'رابط داخلي',
            function() {
                if (window.IELH) {
                    window.IELH.openModal();
                }
            },
            '',
            '',
            'إدراج رابط داخلي',
            1
        );
    }
    
})();