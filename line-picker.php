<?php

/**
 * Line Picker
 *
 * @package     LinePicker
 * @author      Henri Susanto
 * @copyright   2022 Henri Susanto
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: Line Picker
 * Plugin URI:  https://github.com/susantohenri
 * Description: Show 3 Line of Uploaded Text File
 * Version:     1.0.0
 * Author:      Henri Susanto
 * Author URI:  https://github.com/susantohenri
 * Text Domain: line-picker
 * License:     GPL v2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */



register_activation_hook(__FILE__, function () {
    global $wpdb;
    $table = "{$wpdb->prefix}line_picker";
    $wpdb->query("
        CREATE TABLE `{$table}` (
            `id` int(11) NOT NULL,
            `user` varchar(255) NOT NULL,
            `shown_times` int(11) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8
    ");
    $wpdb->query("ALTER TABLE `{$table}` ADD PRIMARY KEY (`id`)");
    $wpdb->query("ALTER TABLE `{$table}` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT");
});

register_deactivation_hook(__FILE__, function () {
    global $wpdb;
    $table = "{$wpdb->prefix}line_picker";
    $wpdb->query("DROP TABLE `{$table}`");
});

add_action('admin_menu', function () {
    add_menu_page('Line Picker', 'Line Picker', 'administrator', __FILE__, function () {

        $inputName = 'linepickertxt';
        global $wpdb;
        $table = "{$wpdb->prefix}line_picker";

        if ($_FILES && $_FILES[$inputName]['tmp_name']) {
            $wpdb->query("TRUNCATE $table");
            $fp = fopen($_FILES[$inputName]['tmp_name'], 'rb');
            while (($line = fgets($fp)) !== false) {
                $wpdb->insert($table, [
                    'id' => null,
                    'user' => $line,
                    'shown_times' => 0
                ], ['%d', '%s', '%d']);
            }
        }

?>
        <div class="wrap">
            <h1>Upload Text File Here</h1>
            <form method="post" action="" enctype="multipart/form-data">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Choose File</th>
                        <td><input type="file" name="<?= $inputName ?>" /></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
<?php
    }, '');
});

add_shortcode('line-picker', function () {
    global $wpdb;
    $table = "{$wpdb->prefix}line_picker";
    $maxShown = (int) $wpdb->get_var("SELECT MAX(shown_times) FROM $table");

    $picked = linePickerPick($maxShown - 1);
    $newMark = $maxShown;
    if (0 === count($picked)) {
        $picked = linePickerPick($maxShown);
        $newMark = $maxShown + 1;
    }

    $result = [];
    foreach ($picked as $record) {
        $result[] = $record->user;
        $wpdb->update(
            $table,
            ['shown_times' => $newMark],
            ['id' => $record->id],
            ['%d'],
            ['%d']
        );
    }
    return implode(', ', $result);
});

function linePickerPick($shownTimes)
{
    global $wpdb;
    $table = "{$wpdb->prefix}line_picker";
    return $wpdb->get_results("SELECT * FROM $table WHERE shown_times = $shownTimes LIMIT 3");
}
