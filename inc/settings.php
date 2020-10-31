<?php
class Settings
{
    public function plugin_settings()
    {
        // Add a menu for option page
        add_action('admin_menu', 'settings_menu');
        function settings_menu()
        {
            add_options_page('Lazy Loading Plugin Settings', 'Lazy Loading icon', 'manage_options', 'lazy_loading_plugin', 'option_page');
        }

        // Create the option page
        function option_page()
        {
?>
    <div class="wrap">
    <h2>lazy loading</h2>
    <form action="options.php" method="post">
    <?php
            settings_fields('lazy_loading_options');
            do_settings_sections('lazy_loading_plugin');
            submit_button('Save Changes', 'primary');
?>
    </form>
    </div>
    <?php
        }

        // Register and define the settings
        add_action('admin_init', 'admin_init');
        function admin_init()
        {
            // Define the setting args
            $args = array(
                'type' => 'string',
                'default' => NULL
            );
            // Register settings
            register_setting('lazy_loading_options', 'lazy_loading_options', $args);

            // Add a settings section
            add_settings_section('lazy_loading_main', 'Lazy Loading Plugin Settings', 'section_text', 'lazy_loading_plugin');

            // Create our settings field enable in general
            add_settings_field('settings_enable', 'Enable Plugin', 'settings_enable', 'lazy_loading_plugin', 'lazy_loading_main');

            // Create our settings field enable for pics
            add_settings_field('settings_enable_pics', 'Enable for Pics', 'settings_enable_pics', 'lazy_loading_plugin', 'lazy_loading_main');

            // Create our settings field enable for pics in background
            add_settings_field('settings_enable_background_pics', 'Enable for Pics in Background', 'settings_enable_background_pics', 'lazy_loading_plugin', 'lazy_loading_main');

            // Create our settings field enable for Iframes
            add_settings_field('settings_enable_iframes', 'Enable for Iframes', 'settings_enable_iframes', 'lazy_loading_plugin', 'lazy_loading_main');

            // Create our settings field enable for videos
            add_settings_field('settings_enable_videos', 'Enable for Videos', 'settings_enable_videos', 'lazy_loading_plugin', 'lazy_loading_main');

        }

        // Draw the section header
        function section_text()
        {
            echo '<p>Enter your settings here.</p>';
        }

        function settings_enable()
        {
            // Get option value from the database
            // Set to 'disable' as a default if the option does not exist
            $options = get_option('lazy_loading_options', ['Enable' => 'enable']);
            $enable = $options['Enable'];
            // Define the select option values for position
            $items = array(
                'enable',
                'disable'
            );
            echo "<select name='lazy_loading_options[Enable]'>";
            foreach ($items as $item)
            {
                // Loop through the option values
                // If saved option matches the option value, select it
                echo "<option value='" . esc_attr($item) . "'
 " . selected($enable, $item, false) . ">" . esc_html($item) . "</option>";
            }
            echo "</select>";
        }

        function settings_enable_pics()
        {
            // Get option value from the database
            // Set to 'disable' as a default if the option does not exist
            $options = get_option('lazy_loading_options', ['Enable_Pics' => 'enable']);
            $enable = $options['Enable_Pics'];
            // Define the select option values for position
            $items = array(
                'enable',
                'disable'
            );
            echo "<select name='lazy_loading_options[Enable_Pics]'>";
            foreach ($items as $item)
            {
                // Loop through the option values
                // If saved option matches the option value, select it
                echo "<option value='" . esc_attr($item) . "'
 " . selected($enable, $item, false) . ">" . esc_html($item) . "</option>";
            }
            echo "</select>";
        }

        function settings_enable_background_pics()
        {
            // Get option value from the database
            // Set to 'disable' as a default if the option does not exist
            $options = get_option('lazy_loading_options', ['Enable_Background_Pics' => 'enable']);
            $enable = $options['Enable_Background_Pics'];
            // Define the select option values for position
            $items = array(
                'enable',
                'disable'
            );
            echo "<select name='lazy_loading_options[Enable_Background_Pics]'>";
            foreach ($items as $item)
            {
                // Loop through the option values
                // If saved option matches the option value, select it
                echo "<option value='" . esc_attr($item) . "'
 " . selected($enable, $item, false) . ">" . esc_html($item) . "</option>";
            }
            echo "</select>";
        }

        function settings_enable_iframes()
        {
            // Get option value from the database
            // Set to 'disable' as a default if the option does not exist
            $options = get_option('lazy_loading_options', ['Enable_Iframes' => 'enable']);
            $enable = $options['Enable_Iframes'];
            // Define the select option values for position
            $items = array(
                'enable',
                'disable'
            );
            echo "<select name='lazy_loading_options[Enable_Iframes]'>";
            foreach ($items as $item)
            {
                // Loop through the option values
                // If saved option matches the option value, select it
                echo "<option value='" . esc_attr($item) . "'
 " . selected($enable, $item, false) . ">" . esc_html($item) . "</option>";
            }
            echo "</select>";
        }


        function settings_enable_videos()
        {
            // Get option value from the database
            // Set to 'disable' as a default if the option does not exist
            $options = get_option('lazy_loading_options', ['Enable_Videos' => 'enable']);
            $enable = $options['Enable_Videos'];
            // Define the select option values for position
            $items = array(
                'enable',
                'disable'
            );
            echo "<select name='lazy_loading_options[Enable_Videos]'>";
            foreach ($items as $item)
            {
                // Loop through the option values
                // If saved option matches the option value, select it
                echo "<option value='" . esc_attr($item) . "'
 " . selected($enable, $item, false) . ">" . esc_html($item) . "</option>";
            }
            echo "</select>";
        }

    }

}

