<?php
/**
 * Plugin Name: Meta Title & Description
 * Description: Добавляет в WordPress для каждой записи и страницы поля для Title и Description.
 * Version: 1.0.0
 * Author: 7on
 * License: GPL-2.0-or-later
 * Text Domain: meta-title-description
 * Domain Path: /languages
 * Requires at least: 5.4
 * Requires PHP: 5.6
 */

// Добавляем метабокс
function mtd_add_meta_box() {
    add_meta_box(
        'mtd_meta_box',
        __('Meta Title & Description', 'meta-title-description'),
        'mtd_meta_box_callback',
        ['post', 'page'],
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'mtd_add_meta_box');

// Колбэк для метабокса
function mtd_meta_box_callback($post) {
    wp_nonce_field('mtd_save_meta_box_data', 'mtd_meta_box_nonce');

    $meta_title = get_post_meta($post->ID, '_mtd_title', true);
    $meta_description = get_post_meta($post->ID, '_mtd_description', true);

    echo '<p><label for="mtd_title">' . __('Meta Title', 'meta-title-description') . '</label></p>';
    echo '<input type="text" id="mtd_title" name="mtd_title" value="' . esc_attr($meta_title) . '" style="width:100%" />';

    echo '<p><label for="mtd_description">' . __('Meta Description', 'meta-title-description') . '</label></p>';
    echo '<textarea id="mtd_description" name="mtd_description" rows="4" style="width:100%">' . esc_textarea($meta_description) . '</textarea>';
}

// Сохраняем данные
function mtd_save_meta_box_data($post_id) {
    if (!isset($_POST['mtd_meta_box_nonce']) || !wp_verify_nonce($_POST['mtd_meta_box_nonce'], 'mtd_save_meta_box_data')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (isset($_POST['post_type']) && 'page' == $_POST['post_type']) {
        if (!current_user_can('edit_page', $post_id)) {
            return;
        }
    } else {
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
    }

    if (isset($_POST['mtd_title'])) {
        update_post_meta($post_id, '_mtd_title', sanitize_text_field($_POST['mtd_title']));
    }

    if (isset($_POST['mtd_description'])) {
        update_post_meta($post_id, '_mtd_description', sanitize_textarea_field($_POST['mtd_description']));
    }
}
add_action('save_post', 'mtd_save_meta_box_data');

// Вывод в <head>
function mtd_add_meta_tags() {
    if (is_singular()) {
        global $post;
        $meta_title = get_post_meta($post->ID, '_mtd_title', true);
        $meta_description = get_post_meta($post->ID, '_mtd_description', true);

        if ($meta_title) {
            echo '<title>' . esc_html($meta_title) . '</title>' . "\n";
        }
        if ($meta_description) {
            echo '<meta name="description" content="' . esc_attr($meta_description) . '" />' . "\n";
        }
    }
}
add_action('wp_head', 'mtd_add_meta_tags', 1);
