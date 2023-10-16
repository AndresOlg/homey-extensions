<?php
function copy_and_replace_plugin_files($upgrader_object, $options)
{
    // List of plugins you want to control
    $target_plugins = array(
        'homey-core',             // Plugin folder name 'homey-core'
        'homey-login-register'    // Plugin folder name 'homey-login-register'
    );

    // Check if any of the plugins from the list were updated
    $updated_plugins = $options['plugins'];

    foreach ($target_plugins as $target_plugin) {
        if (in_array($target_plugin . '/' . $target_plugin . '.php', $updated_plugins)) {
            // Path to the backup folder
            $backup_folder = '/path/to/backup/folder/' . $target_plugin;

            // Path to the plugin folder
            $plugin_folder = WP_PLUGIN_DIR . '/' . $target_plugin;

            // Copy and replace files from the backup folder
            copy_and_replace_files($backup_folder, $plugin_folder);
        }
    }
}

// Function to copy and replace files
function copy_and_replace_files($source, $destination)
{
    if (is_dir($source)) {
        $files = scandir($source);
        foreach ($files as $file) {
            if ($file != "." && $file != "..") {
                copy_and_replace_files("$source/$file", "$destination/$file");
            }
        }
    } elseif (file_exists($source)) {
        copy($source, $destination);
    }
}
