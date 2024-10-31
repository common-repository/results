<?php
/**
 * iWorks Sport Results
 *
 * PHP version 5
 *
 * @category   WordPress_Plugins
 * @package    iWorks
 * @subpackage Sport Results
 * @author     Marcin Pietrzak <marcin@iworks.pl>
 * @version    SVN: $Id: class-iworks-sport-results.php 615140 2012-10-21 05:51:05Z iworks $
 * @link       http://iworks.pl/
 *
 */

/*

Copyright 2012 Marcin Pietrzak (marcin@iworks.pl)

this program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

 */

if ( class_exists( 'iWorks_Sport_Results' ) ) {
    return;
}


class iWorks_Sport_Results
{
    private static $base;
    private static $dev;
    private static $dir;
    private static $menu_slug;
    private static $version;
    private $options;

    public function __construct()
    {
        /**
         * static settings
         */
        $this->version      = '1.0';
        $this->base         = dirname( __FILE__ );
        $this->dir          = basename( dirname( $this->base ) );
        $this->capability   = apply_filters( 'iworks_upprev_capability', 'manage_options' );
        $this->is_pro       = $this->is_pro();
        $this->working_mode = 'site';
        $this->dev          = ( defined( 'IWORKS_DEV_MODE' ) && IWORKS_DEV_MODE )? '.dev':'';
        /**
         * actions
         */
        add_action( 'add_meta_boxes',              array( &$this, 'add_meta_boxes'              ) );
        add_action( 'admin_init',                  array( &$this, 'admin_init'                  ) );
        add_action( 'admin_menu',                  array( &$this, 'admin_menu'                  ) );
        add_action( 'admin_print_styles',          array( &$this, 'admin_print_styles'          ) );
        add_action( 'init',                        array( &$this, 'init'                        ) );
        add_action( 'right_now_content_table_end', array( &$this, 'right_now_content_table_end' ) );
        add_action( 'save_post',                   array( &$this, 'save_post'                   ) );
        /**
         * filters
         */
        add_filter( 'the_title', array( &$this, 'the_title' ), 99999, 2 );
        /**
         * configuration
         */
        $this->menu_slug = basename( dirname( dirname ( __FILE__ ) ) ).'/admin/';
        $this->noncename = 'iworks_result_nonce';
        /**
         * options
         */
        require_once dirname( __FILE__ ) . '/iworks.options.class.php';
        $this->options   = new IworksOptions();;
        /**
         * arrays
         */
        $this->post_type     = 'sport-result';
        $this->custom_fields = array(
            $this->post_type( 'team' ) => array(
                'labels' => array(
                    'add_meta_boxes' => __( 'Team data', 'iworks_results' )
                ),
                'custom_fields' => array(
                    'date' => array(
                        'type'        => 'text',
                        'title'       => __( 'Team date', 'iworks_results' ),
                        'placeholder' => __( 'Enter contest date', 'iworks_results' ),
                        'class'       => 'widefat',
                    ),
                    'place' => array(
                        'type'  => 'textarea',
                        'title' => __( 'Team place', 'iworks_results' )
                    )
                )
            ),
            $this->post_type( 'contest' ) => array(
                'labels' => array(
                    'add_meta_boxes' => __( 'Contest data', 'iworks_results' )
                ),
                'custom_fields' => array(
                    'date' => array(
                        'type'        => 'text',
                        'title'       => __( 'Contest date', 'iworks_results' ),
                        'placeholder' => __( 'Enter contest date', 'iworks_results' ),
                        'class'       => 'widefat',
                    ),
                    'place' => array(
                        'type'  => 'textarea',
                        'title' => __( 'Contest place', 'iworks_results' )
                    )
                )
            ),
            $this->post_type( 'result' ) => array(
                'labels' => array(
                    'add_meta_boxes' => __( 'Result data', 'iworks_results' )
                ),
                'custom_fields' => array(
                    'result' => array(
                        'type'        => 'text',
                        'title'       => __( 'Result', 'iworks_results' ),
                        'placeholder' => __( 'Enter result', 'iworks_results' ),
                    ),
                    'contest' => array(
                        'type'        => 'select',
                        'title'       => __( 'Contest', 'iworks_results' ),
                        'placeholder' => __( 'Select contest', 'iworks_results' ),
                        'class'       => 'widefat',
                        'callback'    => 'iworks_results_get_contests_list'
                    ),
                    'player' => array(
                        'type'        => 'select',
                        'title'       => __( 'Player', 'iworks_results' ),
                        'placeholder' => __( 'Select player', 'iworks_results' ),
                        'class'       => 'widefat',
                        'callback'    => 'iworks_results_get_players_list'
                    )
                )
            ),
            $this->post_type( 'player' ) => array(
                'labels' => array(
                    'add_meta_boxes' => __( 'Player data', 'iworks_results' )
                ),
                'custom_fields' => array(
                    'date' => array(
                        'type'        => 'text',
                        'title'       => __( 'Birth date', 'iworks_results' ),
                        'placeholder' => __( 'Enter birth date', 'iworks_results' ),
                        'class'       => 'widefat',
                    ),
                )
            ),
        );
    }

    public function is_pro()
    {
        return false;
        return true;
    }

    public function get_options()
    {
        return $this->options;
    }

    public function add_meta_boxes()
    {
        foreach( array( 'contest', 'result', 'team', 'player' ) as $key ) {
            $post_type = $this->post_type( $key );
            add_meta_box(
                'iworks_results',
                $this->custom_fields[ $post_type ][ 'labels'][ __FUNCTION__ ],
                array( &$this, 'meta_box_inner' ),
                $post_type,
                'normal',
                'high'
            );
        }
    }

    private function post_type( $sufix )
    {
        return sprintf( '%s-%s', $this->post_type, $sufix );
    }

    private function get_list_by_type( $value, $post_type )
    {
        $content = '';
        $args = array (
            'order'     => 'ASC',
            'orderby'   => 'post_title',
            'post_type' => $this->post_type( $post_type ),
            'showposts' => -1,
        );
        $posts = new WP_Query( $args );
        if ( $posts->have_posts() ) {
            while ( $posts->have_posts() ) {
                $posts->the_post();
                $content .= sprintf(
                    '<option value="%d"%s>%s</option>',
                    get_the_ID(),
                    $value == get_the_ID()? ' selected="selected"':'',
                    get_the_title()
                );
            }
        }
        if ( $content ) {
            $c =sprintf( '<select name="iworks_results[%s]">', $post_type );
            $c .= sprintf( '<option value="0">%s</option>', __( 'Select contest', 'iworks_results' ) );
            $c .= $content;
            $c .='</select>';
            return $c;
        }
        return false;
    }

    public function get_contests_list( $value )
    {
        $content = $this->get_list_by_type( $value, 'contest' );
        if ( $content ) {
            echo $content;
            return;
        }
        _e( 'There is no contests!', 'iworks_results' );
    }

    public function get_players_list( $value )
    {
        $content = $this->get_list_by_type( $value, 'player' );
        if ( $content ) {
            echo $content;
            return;
        }
        _e( 'There is no players!', 'iworks_results' );
    }

    public function meta_box_inner( $post )
    {
        wp_nonce_field( plugin_basename( __FILE__ ), $this->noncename );
        echo '<dl>';
        foreach( $this->custom_fields[ get_post_type() ][ 'custom_fields' ] as $key => $one ) {
            printf( '<dt>%s</dt>', $one['title'] );
            echo '<dd>';
            $template = '<input type="text" name="iworks_results[%s]" value="%s"%s />';
            /**
             * add extra
             */
            $extra = '';
            foreach( array( 'class', 'placeholder' ) as $attr_name ) {
                if ( isset( $one[ $attr_name ] ) && $one[ $attr_name ] ) {
                    $extra .= sprintf( ' %s="%s"', $attr_name, $one[ $attr_name ] );
                }
            }
            /**
             * add standard attribiutes
             */
            switch( $one['type'] ) {
            case 'textarea':
                $template = '<textarea name="iworks_results[%s]"%3$s>%2$s</textarea>';
                break;
            case 'text':
            default:
            }
            /**
             * run callback if need
             */
            if ( isset( $one[ 'callback' ] ) && is_callable( $one[ 'callback' ] ) ) {
                $one[ 'callback' ]( get_post_meta( $post->ID, $this->meta( $key ), true ) );
            } else {
                printf(
                    $template,
                    $key,
                    get_post_meta( $post->ID, $this->meta( $key ), true ),
                    $extra
                );
            }
            echo '</dd>';
        }
        echo '</dl>';
    }

    public function save_post( $post_id )
    {
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        if ( !wp_verify_nonce( $_POST[$this->noncename], plugin_basename( __FILE__ ) ) ) {
            return;
        }
        $mydata = $_POST['iworks_results'];
        foreach ( array_keys( $this->custom_fields[ get_post_type( $post_id ) ][ 'custom_fields'] ) as $meta_key ) {
            if ( isset( $mydata[ $meta_key ] ) ) {
                $meta_value = $mydata[ $meta_key ];
                update_post_meta( $post_id, $this->meta( $meta_key ), $meta_value );
            }
            else {
                delete_post_meta( $post_id, $meta_key );
            }
        }
    }

    public function meta( $name )
    {
        return 'iworks_results_'.$name;
    }

    public function admin_init()
    {
        add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
        wp_register_style( 'iWorksSportResultsStylesheet', plugins_url('styles/admin.css', dirname( __FILE__) ) );
    }

    public function plugin_row_meta( $links, $file )
    {
        if ( $this->dir.'/results.php' == $file ) {
            if ( !is_multisite() && current_user_can( $this->capability ) ) {
                $links[] = '<a href="admin.php?page='.$this->dir.'/admin/index.php">' . __( 'Settings' ) . '</a>';
            }
            if ( !$this->is_pro ) {
                $links[] = '<a href="http://iworks.pl/donate/results.php">' . __( 'Donate' ) . '</a>';
            }
        }
        return $links;
    }

    public function admin_print_styles()
    {
        $screen = get_current_screen();
        $re = '/^'.$this->post_type.'(-[a-z]+)?$/';
        if(
            ( isset( $screen->post_type ) && preg_match( $re, $screen->post_type ) )
            or
            strpos( preg_replace( '@/index.php@', '', $this->menu_slug ), preg_replace( '@/[a-z]+@', '', $screen->id ) ) === 0
        ) {
            wp_enqueue_style( 'iWorksSportResultsStylesheet' );
        }
    }

    public function admin_menu()
    {
        add_object_page( 'Results', __('Results', 'iworks_results'), 'edit_posts', $this->menu_slug, '', plugins_url( $this->dir.'/images/admin_menu.png'));
        add_submenu_page( $this->menu_slug, __('Settings' ), __( 'Settings'), 'edit_posts', $this->menu_slug.'index.php' );
    }

    public function init()
    {
        $this->upgrade();
        $this->register_post_type();
    }

    public function register_post_type()
    {
        register_post_type(
            $this->post_type( 'contest' ),
            array(
                'labels' => array(
                    'add_new_item'  => __( 'New contest',  'iworks_contests' ),
                    'edit_item'     => __( 'Edit contest', 'iworks_contests' ),
                    'menu_name'     => __( 'Contests',     'iworks_contests' ),
                    'name'          => __( 'Contests',     'iworks_contests' ),
                    'singular_name' => __( 'Contest',      'iworks_contests' ),
                ),
                'supports' => array (
                    'comments',
                    'editor',
                    'excerpt',
                    'revisions',
                    'thumbnail',
                    'title',
                    'trackbacks',
                ),
                'public'       => true,
                'show_in_menu' => $this->menu_slug,
                'rewrite'      => array(
                    'slug' => get_option( $this->meta( 'contest_slug' ), 'contest' )
                ),
                'show_ui'      => true,
                'taxonomies'   => array( 'post_tag' )
            )
        );
        register_post_type(
            $this->post_type( 'result' ),
            array(
                'labels' => array(
                    'add_new_item'  => __( 'New result',  'iworks_results' ),
                    'edit_item'     => __( 'Edit result', 'iworks_results' ),
                    'menu_name'     => __( 'Results',     'iworks_results' ),
                    'name'          => __( 'Results',     'iworks_results' ),
                    'singular_name' => __( 'Result',      'iworks_results' ),
                ),
                'supports' => array (
                    'editor',
                ),
                'public'       => true,
                'show_in_menu' => $this->menu_slug,
                'rewrite'      => array(
                    'slug' => get_option( $this->meta( 'result_slug' ), 'result' )
                ),
                'show_ui'      => true,
                'show_in_admin_bar' => true

            )
        );
        register_post_type(
            $this->post_type( 'team' ),
            array(
                'labels' => array(
                    'add_new_item'  => __( 'New team',  'iworks_teams' ),
                    'edit_item'     => __( 'Edit team', 'iworks_teams' ),
                    'menu_name'     => __( 'Teams',     'iworks_teams' ),
                    'name'          => __( 'Teams',     'iworks_teams' ),
                    'singular_name' => __( 'Team',      'iworks_teams' ),
                ),
                'supports' => array (
                    'comments',
                    'editor',
                    'excerpt',
                    'revisions',
                    'thumbnail',
                    'title',
                    'trackbacks',
                ),
                'public'       => true,
                'show_in_menu' => $this->menu_slug,
                'rewrite'      => array(
                    'slug' => get_option( $this->meta( 'team_slug' ), 'team' )
                ),
                'show_ui'      => true,
                'taxonomies'   => array( 'post_tag' )
            )
        );
        register_post_type(
            $this->post_type( 'player' ),
            array(
                'labels' => array(
                    'add_new_item'  => __( 'New player',  'iworks_players' ),
                    'edit_item'     => __( 'Edit player', 'iworks_players' ),
                    'menu_name'     => __( 'Players',     'iworks_players' ),
                    'name'          => __( 'Players',     'iworks_players' ),
                    'singular_name' => __( 'Player',      'iworks_players' ),
                ),
                'supports' => array (
                    'comments',
                    'editor',
                    'excerpt',
                    'revisions',
                    'thumbnail',
                    'title',
                    'trackbacks',
                ),
                'public'       => true,
                'show_in_menu' => $this->menu_slug,
                'rewrite'      => array(
                    'slug' => get_option( $this->meta( 'player_slug' ), 'player' )
                ),
                'show_ui'      => true,
                'taxonomies'   => array( 'post_tag' )
            )
        );
    }

    public function the_title( $title, $post_ID = null )
    {
        if ( null == $post_ID ) {
            return $title;
        }
        if ( $this->post_type( 'result' ) != get_post_type( $post_ID ) ) {
            return $title;
        }
        $player_id  = get_post_meta( $post_ID, $this->meta( 'player' ),  true );
        $contest_id = get_post_meta( $post_ID, $this->meta( 'contest' ), true );
        $posts = array();
        global $wpdb;
        foreach( $wpdb->get_results( 'select * from '.$wpdb->posts.' where ID in ( '.$player_id.', '.$contest_id.')' ) as $post ) {
            $posts[ $post->ID ] = $post;
        }
        $title = '';
        $title .= isset( $posts[ $player_id ] )? $posts[ $player_id ]->post_title:__( 'Unknown player', 'iworks_results' );
        $title .= ' - ';
        $title .= isset( $posts[ $contest_id ] )? $posts[ $contest_id ]->post_title:__( 'Unknown contest', 'iworks_results' );
        return $title;
    }

    public function activate()
    {
        add_option( $this->meta( 'version' ), IWORKS_RESULTS_VERSION );
    }

    public function deactivate()
    {
        delete_option( $this->meta( 'version' ) );
    }

    public function right_now_content_table_end()
    {
        if ( !current_user_can( 'edit_posts' ) ) {
            return;
        }
        $count_posts = wp_count_posts( $this->post_type );
        $link = get_admin_url( null, 'edit.php?post_type='.$this->post_type( 'result' ) );
        echo '<tr><td class="first b b-result">';
        printf( '<a href="%s">%d</>', $link, $count_posts->publish );
        echo '</td><td class="t result">';
        printf( '<a href="%s">%s</>', $link, __( 'results', 'iworks_results' ) );
        echo '</td></tr>';
    }

    public function upgrade()
    {
        $version = get_option( 'iworks_results_version', 0 );
    }

}
/**
 * helpers
 */
function iworks_results_get_contests_list( $value )
{
    global $iworks_results;
    $iworks_results->get_contests_list( $value );
}
function iworks_results_get_players_list( $value )
{
    global $iworks_results;
    $iworks_results->get_players_list( $value );
}


