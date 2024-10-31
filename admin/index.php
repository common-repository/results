<div class="wrap">
    <?php screen_icon('sport-results'); ?>
    <h2><?php _e('Sport Results', 'iworks_results') ?></h2>
<?php

if( isset($_GET['settings-updated']) && $_GET['settings-updated'] ) {
    $iworks_results_options->update_option( 'cache_stamp', date('c') );
    echo '<div id="message" class="updated fade"><p>'.__('results options saved.', 'results').'</p></div>';
}
?>
    <form method="post" action="options.php" id="iworks_results_admin_index">
        <div class="postbox-container" style="width:75%">
<?php
$option_name = basename( __FILE__, '.php');
d( $option_name );
$options = $iworks_results->get_options();
d( $options );

$iworks_results->options->settings_fields( $option_name );
$iworks_results->options->build_options( $option_name );

$configuration = get_option( 'iworks_results_configuration', 'simple' );
if ( !preg_match( '/^(advance|simple)$/', $configuration ) ) {
    $configuration = 'simple';
    $iworks_results_options->update_option( 'configuration', $configuration );
}

?>
        </div>
        <div class="postbox-container" style="width:23%;margin-left:2%">
            <div class="metabox-holder">
                <div id="links" class="postbox">
                    <h3 class="hndle"><?php _e( 'Choose configuration mode', 'results' ); ?></h3>
                    <div class="inside">
                        <p><?php _e( 'Below are some links to help spread this plugin to other users', 'results' ); ?></p>
                        <ul>
                        <li><input type="radio" name="iworks_results_configuration" value="simple" id="iworks_results_configuration_simple"   <?php checked( $configuration, 'simple' ); ?>/> <label for="iworks_results_configuration_simple"><?php _e( 'simple', 'results' ); ?></label></li>
                        <li><input type="radio" name="iworks_results_configuration" value="advance" id="iworks_results_configuration_advance" <?php checked( $configuration, 'advance' ); ?>/> <label for="iworks_results_configuration_advance"><?php _e( 'advance', 'results' ); ?></label></li>
                        </ul>
                    </div>
                </div>
                <div id="links" class="postbox">
                    <h3 class="hndle"><?php _e( 'Loved this Plugin?', 'results' ); ?></h3>
                    <div class="inside">
                        <p><?php _e( 'Below are some links to help spread this plugin to other users', 'results' ); ?></p>
                        <ul>
                            <li><a href="http://wordpress.org/extend/plugins/results/"><?php _e( 'Give it a 5 star on Wordpress.org', 'results' ); ?></a></li>
                            <li><a href="http://wordpress.org/extend/plugins/results/"><?php _e( 'Link to it so others can easily find it', 'results' ); ?></a></li>
                        </ul>
                    </div>
                </div>
                <div id="help" class="postbox">
                    <h3 class="hndle"><?php _e( 'Need Assistance?', 'results' ); ?></h3>
                    <div class="inside">
                        <p><?php _e( 'Problems? The links bellow can be very helpful to you', 'results' ); ?></p>
                        <ul>
                            <li><a href="http://wordpress.org/tags/results"><?php _e( 'Wordpress Help Forum', 'results' ); ?></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

