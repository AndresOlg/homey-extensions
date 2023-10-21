<?php
function unload_scripts()
{
    wp_dequeue_script('hx-toast-script');
    wp_dequeue_style('hx-toast-style');
    wp_dequeue_style('hx_styles');
}
