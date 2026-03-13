<?php
// Main Control Center Class
class WP_User_Control_Center {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('wp_ajax_wtfe_update_profile', array($this, 'ajax_update_profile'));
        add_action('wp_ajax_wtfe_upload_avatar', array($this, 'ajax_upload_avatar'));
        add_action('wp_ajax_wtfe_get_posts', array($this, 'ajax_get_posts'));
        add_action('wp_ajax_wtfe_save_post', array($this, 'ajax_save_post'));
        add_action('wp_ajax_wtfe_delete_post', array($this, 'ajax_delete_post'));
        add_action('wp_ajax_wtfe_get_post', array($this, 'ajax_get_post'));
        add_shortcode('wtfe_user_center', array($this, 'render_shortcode'));
    }
    
    public function init() {
        if (!is_user_logged_in()) return;
    }
    
    public function enqueue_scripts() {
        if (!is_user_logged_in()) return;
        
        wp_enqueue_script('jquery');
        wp_enqueue_editor();
        wp_enqueue_media();
        
        wp_add_inline_style('wp-admin', $this->get_css());
        wp_add_inline_script('jquery', $this->get_js());
    }
    
    public function render_shortcode() {
        if (!is_user_logged_in()) {
            return '<p>Please log in to access this area.</p>';
        }
        
        $user = wp_get_current_user();
        $avatar_url = get_user_meta($user->ID, 'wtfe_custom_avatar', true);
        if (!$avatar_url) $avatar_url = get_avatar_url($user->ID);
        
        return $this->get_html($user, $avatar_url);
    }
    
    private function get_html($user, $avatar_url) {
        return '
        <div class="fe-control-center">
            <div class="fe-tabs">
                <button class="fe-tab active" data-tab="profile">Profile</button>
                <button class="fe-tab" data-tab="posts">My Posts</button>
                <button class="fe-tab" data-tab="new-post">New Post</button>
            </div>
            
            <div class="fe-content">
                <div class="fe-tab-content active" id="fe-profile">
                    <h3>Profile Settings</h3>
                    <form id="fe-profile-form">
                        <div class="fe-avatar-section">
                            <img id="fe-avatar-preview" src="' . esc_url($avatar_url) . '" alt="Avatar">
                            <button type="button" id="fe-upload-avatar">Upload Avatar</button>
                            <input type="file" id="fe-avatar-file" accept="image/*" style="display:none;">
                        </div>
                        <div class="fe-form-group">
                            <label>Display Name:</label>
                            <input type="text" name="display_name" value="' . esc_attr($user->display_name) . '">
                        </div>
                        <div class="fe-form-group">
                            <label>Email:</label>
                            <input type="email" name="user_email" value="' . esc_attr($user->user_email) . '">
                        </div>
                        <div class="fe-form-group">
                            <label>Bio:</label>
                            <textarea name="description">' . esc_textarea($user->description) . '</textarea>
                        </div>
                        <button type="submit">Update Profile</button>
                    </form>
                </div>
                
                <div class="fe-tab-content" id="fe-posts">
                    <h3>My Posts</h3>
                    <div id="fe-posts-list"></div>
                </div>
                
                <div class="fe-tab-content" id="fe-new-post">
                    <h3>Create New Post</h3>
                    <form id="fe-post-form">
                        <input type="hidden" name="post_id" value="">
                        <div class="fe-form-group">
                            <label>Title:</label>
                            <input type="text" name="post_title" required>
                        </div>
                        <div class="fe-form-group">
                            <label>Content:</label>
                            <textarea name="post_content" id="fe-post-content"></textarea>
                        </div>
                        <div class="fe-form-actions">
                            <button type="submit">Publish Post</button>
                            <button type="button" id="fe-cancel-edit" style="display:none;">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>';
    }
    
    private function get_css() {
        return '
        .fe-control-center { max-width: 800px; margin: 20px auto; font-family: Arial, sans-serif; }
        .fe-tabs { display: flex; border-bottom: 2px solid #ddd; margin-bottom: 20px; }
        .fe-tab { padding: 10px 20px; background: #f5f5f5; border: none; cursor: pointer; margin-right: 5px; }
        .fe-tab.active { background: #007cba; color: white; }
        .fe-tab-content { display: none; }
        .fe-tab-content.active { display: block; }
        .fe-form-group { margin-bottom: 15px; }
        .fe-form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .fe-form-group input, .fe-form-group textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .fe-form-group textarea { min-height: 100px; resize: vertical; }
        .fe-avatar-section { text-align: center; margin-bottom: 20px; }
        .fe-avatar-section img { width: 100px; height: 100px; border-radius: 50%; object-fit: cover; display: block; margin: 0 auto 10px; }
        .fe-posts-list { margin-top: 20px; }
        .fe-post-item { padding: 15px; border: 1px solid #ddd; margin-bottom: 10px; border-radius: 4px; }
        .fe-post-title { font-size: 18px; font-weight: bold; margin-bottom: 5px; }
        .fe-post-date { color: #666; font-size: 14px; margin-bottom: 10px; }
        .fe-post-actions { text-align: right; }
        .fe-post-actions button { margin-left: 10px; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; }
        .fe-edit-btn { background: #007cba; color: white; }
        .fe-delete-btn { background: #d63638; color: white; }
        .fe-form-actions button { padding: 10px 20px; margin-right: 10px; border: none; border-radius: 4px; cursor: pointer; }
        .fe-form-actions button[type="submit"] { background: #007cba; color: white; }
        .fe-message { padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .fe-message.success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .fe-message.error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        ';
    }
    
    private function get_js() {
        return '
        jQuery(document).ready(function($) {
            // Tab switching
            $(".fe-tab").click(function() {
                var tab = $(this).data("tab");
                $(".fe-tab").removeClass("active");
                $(".fe-tab-content").removeClass("active");
                $(this).addClass("active");
                $("#fe-" + tab).addClass("active");
                
                if (tab === "posts") {
                    loadPosts();
                } else if (tab === "new-post") {
                    initPostEditor();
                }
            });
            
            // Avatar upload
            $("#fe-upload-avatar").click(function() {
                $("#fe-avatar-file").click();
            });
            
            $("#fe-avatar-file").change(function() {
                var file = this.files[0];
                if (file) {
                    var formData = new FormData();
                    formData.append("action", "wtfe_upload_avatar");
                    formData.append("avatar", file);
                    formData.append("nonce", "' . wp_create_nonce('wtfe_nonce') . '");
                    
                    $.ajax({
                        url: "' . admin_url('admin-ajax.php') . '",
                        type: "POST",
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            if (response.success) {
                                $("#fe-avatar-preview").attr("src", response.data.url);
                                showMessage("Avatar updated successfully!", "success");
                            } else {
                                showMessage("Error: " + response.data, "error");
                            }
                        }
                    });
                }
            });
            
            // Profile form
            $("#fe-profile-form").submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                formData += "&action=wtfe_update_profile&nonce=' . wp_create_nonce('wtfe_nonce') . '";
                
                $.ajax({
                    url: "' . admin_url('admin-ajax.php') . '",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            showMessage("Profile updated successfully!", "success");
                        } else {
                            showMessage("Error: " + response.data, "error");
                        }
                    }
                });
            });
            
            // Post form
            $("#fe-post-form").submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();
                formData += "&action=wtfe_save_post&nonce=' . wp_create_nonce('wtfe_nonce') . '";
                formData += "&post_content=" + encodeURIComponent(wp.editor.getContent("fe-post-content"));
                
                $.ajax({
                    url: "' . admin_url('admin-ajax.php') . '",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            showMessage("Post saved successfully!", "success");
                            $("#fe-post-form")[0].reset();
                            wp.editor.setContent("fe-post-content", "");
                            $("#fe-cancel-edit").hide();
                        } else {
                            showMessage("Error: " + response.data, "error");
                        }
                    }
                });
            });
            
            // Cancel edit
            $("#fe-cancel-edit").click(function() {
                $("#fe-post-form")[0].reset();
                wp.editor.setContent("fe-post-content", "");
                $(this).hide();
                $("input[name=\"post_id\"]").val("");
            });
            
            function loadPosts() {
                $.ajax({
                    url: "' . admin_url('admin-ajax.php') . '",
                    type: "POST",
                    data: {
                        action: "wtfe_get_posts",
                        nonce: "' . wp_create_nonce('wtfe_nonce') . '"
                    },
                    success: function(response) {
                        if (response.success) {
                            $("#fe-posts-list").html(response.data);
                        }
                    }
                });
            }
            
            function initPostEditor() {
                if (typeof wp.editor !== "undefined" && !wp.editor.get("fe-post-content")) {
                    wp.editor.initialize("fe-post-content", {
                        tinymce: {
                            wpautop: true,
                            plugins: "lists,link,image,wordpress,wplink,wpdialogs",
                            toolbar1: "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,|,link,unlink,|,bullist,numlist,|,undo,redo"
                        },
                        quicktags: true
                    });
                }
            }
            
            // Post actions
            $(document).on("click", ".fe-edit-btn", function() {
                var postId = $(this).data("id");
                $.ajax({
                    url: "' . admin_url('admin-ajax.php') . '",
                    type: "POST",
                    data: {
                        action: "wtfe_get_post",
                        post_id: postId,
                        nonce: "' . wp_create_nonce('wtfe_nonce') . '"
                    },
                    success: function(response) {
                        if (response.success) {
                            var post = response.data;
                            $("input[name=\"post_title\"]").val(post.title);
                            $("input[name=\"post_id\"]").val(post.id);
                            wp.editor.setContent("fe-post-content", post.content);
                            $("#fe-cancel-edit").show();
                            $(".fe-tab[data-tab=\"new-post\"]").click();
                        }
                    }
                });
            });
            
            $(document).on("click", ".fe-delete-btn", function() {
                if (confirm("Are you sure you want to delete this post?")) {
                    var postId = $(this).data("id");
                    $.ajax({
                        url: "' . admin_url('admin-ajax.php') . '",
                        type: "POST",
                        data: {
                            action: "wtfe_delete_post",
                            post_id: postId,
                            nonce: "' . wp_create_nonce('wtfe_nonce') . '"
                        },
                        success: function(response) {
                            if (response.success) {
                                showMessage("Post deleted successfully!", "success");
                                loadPosts();
                            } else {
                                showMessage("Error: " + response.data, "error");
                            }
                        }
                    });
                }
            });
            
            function showMessage(message, type) {
                var messageDiv = $("<div class=\"fe-message " + type + "\">" + message + "</div>");
                $(".fe-control-center").prepend(messageDiv);
                setTimeout(function() {
                    messageDiv.fadeOut(function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        });
        ';
    }
    
    // AJAX Handlers
    public function ajax_update_profile() {
        if (!wp_verify_nonce($_POST['nonce'], 'wtfe_nonce') || !is_user_logged_in()) {
            wp_die('Security check failed');
        }
        
        $user_id = get_current_user_id();
        $user_data = array(
            'ID' => $user_id,
            'display_name' => sanitize_text_field($_POST['display_name']),
            'user_email' => sanitize_email($_POST['user_email']),
            'description' => sanitize_textarea_field($_POST['description'])
        );
        
        $result = wp_update_user($user_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success();
        }
    }
    
    public function ajax_upload_avatar() {
        if (!wp_verify_nonce($_POST['nonce'], 'wtfe_nonce') || !is_user_logged_in()) {
            wp_die('Security check failed');
        }
        
        if (empty($_FILES['avatar'])) {
            wp_send_json_error('No file uploaded');
        }
        
        $file = $_FILES['avatar'];
        $upload = wp_handle_upload($file, array('test_form' => false));
        
        if (isset($upload['error'])) {
            wp_send_json_error($upload['error']);
        }
        
        $user_id = get_current_user_id();
        update_user_meta($user_id, 'wtfe_custom_avatar', $upload['url']);
        
        wp_send_json_success(array('url' => $upload['url']));
    }
    
    public function ajax_get_posts() {
        if (!wp_verify_nonce($_POST['nonce'], 'wtfe_nonce') || !is_user_logged_in()) {
            wp_die('Security check failed');
        }
        
        $posts = get_posts(array(
            'author' => get_current_user_id(),
            'post_status' => 'publish',
            'numberposts' => -1
        ));
        
        $html = '';
        foreach ($posts as $post) {
            $html .= '<div class="fe-post-item">';
            $html .= '<div class="fe-post-title">' . esc_html($post->post_title) . '</div>';
            $html .= '<div class="fe-post-date">' . get_the_date('M j, Y', $post->ID) . '</div>';
            $html .= '<div class="fe-post-actions">';
            $html .= '<button class="fe-edit-btn" data-id="' . $post->ID . '">Edit</button>';
            $html .= '<button class="fe-delete-btn" data-id="' . $post->ID . '">Delete</button>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        if (empty($html)) {
            $html = '<p>No posts found.</p>';
        }
        
        wp_send_json_success($html);
    }
    
    public function ajax_get_post() {
        if (!wp_verify_nonce($_POST['nonce'], 'wtfe_nonce') || !is_user_logged_in()) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);
        
        if (!$post || $post->post_author != get_current_user_id()) {
            wp_send_json_error('Post not found or access denied');
        }
        
        wp_send_json_success(array(
            'id' => $post->ID,
            'title' => $post->post_title,
            'content' => $post->post_content
        ));
    }
    
    public function ajax_save_post() {
        if (!wp_verify_nonce($_POST['nonce'], 'wtfe_nonce') || !is_user_logged_in()) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_POST['post_id']);
        $post_data = array(
            'post_title' => sanitize_text_field($_POST['post_title']),
            'post_content' => wp_kses_post($_POST['post_content']),
            'post_status' => 'publish',
            'post_type' => 'post'
        );
        
        if ($post_id) {
            $existing_post = get_post($post_id);
            if ($existing_post && $existing_post->post_author == get_current_user_id()) {
                $post_data['ID'] = $post_id;
                $result = wp_update_post($post_data);
            } else {
                wp_send_json_error('Access denied');
            }
        } else {
            $post_data['post_author'] = get_current_user_id();
            $result = wp_insert_post($post_data);
        }
        
        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success();
        }
    }
    
    public function ajax_delete_post() {
        if (!wp_verify_nonce($_POST['nonce'], 'wtfe_nonce') || !is_user_logged_in()) {
            wp_die('Security check failed');
        }
        
        $post_id = intval($_POST['post_id']);
        $post = get_post($post_id);
        
        if (!$post || $post->post_author != get_current_user_id()) {
            wp_send_json_error('Post not found or access denied');
        }
        
        $result = wp_delete_post($post_id, true);
        
        if ($result) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete post');
        }
    }
}

// Initialize the plugin
new WP_User_Control_Center();