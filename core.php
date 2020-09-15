<?php
/**
 * WordPress-Dev
 *
 * Create WordPress custom post types, taxonomies, fields and option pages easily
 *
 * @link    https://github.com/andreadegiovine/WordPress-Dev
 * @author  andreadegiovine
 * @link    https://www.andreadegiovine.it
 * @version 1.0
 * @license https://opensource.org/licenses/mit-license.html MIT License
 */

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

if (!class_exists('WpDev')) {
    class WpDev
    {
        public function __construct()
        {
            return;
        }
        public function enqueue_assets(){
            add_action('admin_enqueue_scripts', function () {
                if (!did_action('wp_enqueue_media')) {
                    wp_enqueue_media();
                }
                wp_enqueue_style('baseplugin-core', plugin_dir_url(__FILE__) . 'core/admin.css', array(), false, 'all');
                wp_enqueue_script('baseplugin-core-js', plugin_dir_url(__FILE__) . 'core/admin.js', array('jquery'), false, true);
                $vars = array(
                    'upload_title' => __('Inserisci immagine', 'wpdev-string'),
                    'upload_button' => __('Scegli immagine', 'wpdev-string')
                );
                wp_localize_script('baseplugin-core-js', 'baseplugin', $vars);
            });
        }
        public function post_type($singular = null, $plural = null, $args = null, $labels = null)
        {
            if (!$singular || !$plural) {
                return;
            }
            $register_slug = sanitize_title($singular);
            $default_labels = array(
                'name'                  => _x($plural, 'Post type general name', 'wpdev-string'),
                'singular_name'         => _x($singular, 'Post type singular name', 'wpdev-string'),
                'menu_name'             => _x($plural, 'Admin Menu text', 'wpdev-string'),
                'name_admin_bar'        => _x($singular, 'Add New on Toolbar', 'wpdev-string'),
                'add_new'               => sprintf(__('Aggiungi %s', 'wpdev-string'), $singular),
                'add_new_item'          => sprintf(__('Aggiungi %s', 'wpdev-string'), $singular),
                'new_item'              => sprintf(__('Aggiungi %s', 'wpdev-string'), $singular),
                'edit_item'             => sprintf(__('Modifica %s', 'wpdev-string'), $singular),
                'view_item'             => sprintf(__('Vedi %s', 'wpdev-string'), $singular),
                'all_items'             => __($plural, 'wpdev-string'),
                'search_items'          => sprintf(__('Cerca %s', 'wpdev-string'), $plural),
                'not_found'             => __('Nessun contenuto disponibile.', 'wpdev-string'),
                'not_found_in_trash'    => __('Nessun contenuto disponibile nel cestino.', 'wpdev-string'),
            );
            $register_labels = $labels && is_array($labels) ? array_replace_recursive($default_labels, $labels) : $default_labels;
            $default_args = array(
                'public'             => true,
                'publicly_queryable' => true,
                'show_ui'            => true,
                'show_in_menu'       => true,
                'query_var'          => true,
                'rewrite'            => array('slug' => $register_slug),
                'capability_type'    => 'post',
                'has_archive'        => true,
                'hierarchical'       => false,
                'menu_position'      => null,
                'supports'           => array('title'),
            );
            $register_args = $args && is_array($args) ? array_replace_recursive($default_args, $args) : $default_args;
            $register_args['labels'] = $register_labels;
            register_post_type($register_slug, $register_args);

            return $this;
        }

        public function taxonomy($post_type = null, $singular = null, $plural = null, $args = null, $labels = null)
        {
            if (!$post_type || !$singular || !$plural) {
                return;
            }
            $register_slug = sanitize_title($singular);
            $default_labels = array(
                'name'              => _x($plural, 'taxonomy general name', 'wpdev-string'),
                'singular_name'     => _x($singular, 'taxonomy singular name', 'wpdev-string'),
                'search_items'      => sprintf(__('Cerca %s', 'wpdev-string'), $singular),
                'all_items'         => __($plural, 'wpdev-string'),
                'parent_item'       => sprintf(__('%s principale', 'wpdev-string'), $singular),
                'parent_item_colon' => sprintf(__('%s principale', 'wpdev-string'), $singular),
                'edit_item'         => sprintf(__('Modifica %s', 'wpdev-string'), $singular),
                'update_item'       => sprintf(__('Aggiorna %s', 'wpdev-string'), $singular),
                'add_new_item'      => sprintf(__('Aggiungi %s', 'wpdev-string'), $singular),
                'new_item_name'     => sprintf(__('Nuovo nome %s', 'wpdev-string'), $singular),
                'menu_name'         => __($singular, 'wpdev-string'),
            );
            $register_labels = $labels && is_array($labels) ? array_replace_recursive($default_labels, $labels) : $default_labels;
            $default_args = array(
                'hierarchical'      => true,
                'show_ui'           => true,
                'show_admin_column' => true,
                'query_var'         => true,
                'rewrite'           => array('slug' => $register_slug),
            );
            $register_args = $args && is_array($args) ? array_replace_recursive($default_args, $args) : $default_args;
            $register_args['labels'] = $register_labels;
            register_taxonomy($register_slug, $post_type, $register_args);

            return $this;
        }
        public function meta_box($post_type = null, $name = null, $fields = null, $position = 'normal')
        {
            if (!$post_type || !$name || !$fields) {
                return;
            }
            add_action('add_meta_boxes', function ($posttype) use (&$post_type, &$name, &$fields, &$position) {
                if ($posttype !== $post_type) {
                    return;
                }
                $register_slug = sanitize_title($name);
                add_meta_box(
                    $register_slug,
                    __($name, 'wpdev-string'),
                    function ($post) use (&$fields) {
                        wp_nonce_field('adg_fields_nonce', 'fields_nonce');

                        foreach ($fields as $field) {
                            $meta_value = get_post_meta($post->ID, $field['key'], true);
?>
                        <div class="field-wrapper">
                            <span class="field-label"><label for="<?php echo 'field_' . $field['key']; ?>"><?php _e($field['label'], 'wpdev-string'); ?></label></span>
                            <div class="field-input">
                                <?php if (in_array($field['type'], array('text', 'email', 'tel'))) { ?>
                                    <input type="<?php echo $field['type']; ?>" name="meta-fields[<?php echo $field['key']; ?>]" id="<?php echo 'field_' . $field['key']; ?>" value="<?php echo $meta_value; ?>" autocomplete="off">
                                <?php } elseif ($field['type'] == 'image') { ?>
                                    <div class="image-field">
                                        <div class="upload-actions">
                                            <input type="hidden" name="meta-fields[<?php echo $field['key']; ?>]" value="<?php echo $meta_value; ?>">
                                            <button class="button button-primary upload" id="<?php echo 'field_' . $field['key']; ?>"><?php _e('Scegli', 'wpdev-string'); ?></button> <button class="button button-secondary remove"><?php _e('Rimuovi', 'wpdev-string'); ?></button>
                                        </div>
                                        <?php if ($meta_value) { ?>
                                            <img src="<?php echo $meta_value; ?>">
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </div>
                    <?php }
                    },
                    $post_type,
                    $position,
                    'default'
                );
            });
            add_action('save_post_' . $post_type, function ($post_id) use (&$fields) {
                if (!isset($_POST['fields_nonce']) || !wp_verify_nonce($_POST['fields_nonce'], 'adg_fields_nonce') || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) ||  !current_user_can('edit_post', $post_id)) {
                    return $post_id;
                }
                $accepted_keys = array_map(
                    function ($key) {
                        return $key['key'];
                    },
                    $fields
                );
                $meta_values = isset($_POST['meta-fields']) ? $_POST['meta-fields'] : array();
                foreach ($meta_values as $key => $value) {
                    if (!in_array($key, $accepted_keys)) {
                        continue;
                    }
                    $new_value = sanitize_text_field($value);
                    update_post_meta($post_id, $key, $new_value);
                }
            });

            $this->enqueue_assets();

            return $this;
        }
        public function options_page($menu_name = null, $fields = null, $page_title = null, $dashicon = 'dashicons-admin-settings', $role = 'administrator')
        {
            if (!$menu_name || !$fields) {
                return;
            }
            if (!$page_title) {
                $page_title = $menu_name;
            }
            $page_slug = sanitize_title($menu_name);
            add_action('admin_menu', function () use (&$menu_name, &$fields, &$page_title, &$dashicon, &$role, &$page_slug) {

                add_menu_page(
                    $page_title,
                    $menu_name,
                    $role,
                    $page_slug,
                    function () use (&$fields, &$page_title, &$page_slug) {
                    ?>
                    <div class="wrap">
                        <h1><?php echo $page_title; ?></h1>
                        <form method="post" action="options.php">
                            <?php settings_fields($page_slug); ?>
                            <?php do_settings_sections($page_slug); ?>
                            <table class="form-table">
                                <?php
                                foreach ($fields as $field) {
                                    $option_value = esc_attr(get_option($field['key']));
                                ?>
                                    <tr valign="top" class="option-wrapper">
                                        <th scope="row" class="option-label"><label for="<?php echo 'field_' . $field['key']; ?>"><?php _e($field['label'], 'wpdev-string'); ?></label></th>
                                        <td class="option-input">
                                            <?php if (in_array($field['type'], array('text', 'email', 'tel'))) { ?>
                                                <input type="<?php echo $field['type']; ?>" name="<?php echo $field['key']; ?>" id="<?php echo 'field_' . $field['key']; ?>" value="<?php echo $option_value; ?>" autocomplete="off">
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </table>
                            <?php submit_button(__('Salva impostazioni', 'wpdev-string')); ?>
                        </form>
                    </div>
<?php
                    },
                    $dashicon
                );
                add_action('admin_init', function () use (&$fields, &$page_slug) {
                    foreach ($fields as $field) {
                        register_setting($page_slug, $field['key']);
                    }
                });
            });

            $this->enqueue_assets();
        }
    }
    $wpDev = new WpDev();
}
