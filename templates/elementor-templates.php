<?php

namespace TemplatesHX;

use Elementor\Plugin as ElementorPlugin;

class Elementor_Template_Handler
{

    public static function get_element_templateid_by_name($template_name)
    {
        $args = array(
            'post_type' => 'elementor_library',
            'posts_per_page' => 1,
            'post_status' => 'publish',
            'name' => $template_name,
        );

        $templates = get_posts($args);

        if (!empty($templates)) {
            return $templates[0]->ID;
        } else {
            return null;
        }
    }

    public static function renderTemplate($template_name)
    {
        add_action('wp_enqueue_scripts', function () {
            wp_enqueue_style('elementor-styles');
            wp_enqueue_style('elementor-global-styles');
            wp_enqueue_style('elementor-frontend');
            wp_enqueue_script('elementor-frontend');
        });

        $template_id = self::get_element_templateid_by_name($template_name);
        $elementor_template_content = ElementorPlugin::instance()->frontend->get_builder_content($template_id);

        if ($template_id) {
            if (class_exists('Elementor\Plugin')) {
                return $elementor_template_content;
            }
        }
        return null;
    }
}
