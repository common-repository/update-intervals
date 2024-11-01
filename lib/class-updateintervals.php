<?php
/**
 * Update Intervals
 *
 * @package    Update Intervals
 * @subpackage UpdateIntervals Main function
/*  Copyright (c) 2020- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$updateintervals = new UpdateIntervals();

/** ==================================================
 * Class Main function
 *
 * @since 1.00
 */
class UpdateIntervals {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'admin_menu', array( $this, 'add_pages' ) );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );

		/* original hook */
		add_action( 'uiv_intervals', array( $this, 'schedule_select_form' ), 10, 2 );
	}

	/** ==================================================
	 * Add a "Settings" link to the plugins page
	 *
	 * @param  array  $links  links array.
	 * @param  string $file   file.
	 * @return array  $links  links array.
	 * @since 1.00
	 */
	public function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'update-intervals/updateintervals.php';
		}
		if ( $file === $this_plugin ) {
			$links[] = '<a href="' . admin_url( 'index.php?page=updateintervals' ) . '">' . __( 'Settings' ) . '</a>';
		}
			return $links;
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function add_pages() {

		add_dashboard_page(
			__( 'Update intervals', 'update-intervals' ),
			__( 'Update intervals', 'update-intervals' ),
			'manage_options',
			'updateintervals',
			array( $this, 'settings_page' )
		);
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function settings_page() {

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		$scriptname = admin_url( 'index.php?page=updateintervals' );

		if ( isset( $_POST['update-intervals-save'] ) && ! empty( $_POST['update-intervals-save'] ) ) {
			if ( check_admin_referer( 'uiv_schedules', 'updateintervals_schedules' ) ) {
				if ( ! empty( $_POST['schedule_name_wp_version_check'] ) ) {
					$cron_jobs = get_option( 'cron' );
					foreach ( $cron_jobs as $key1 => $value1 ) {
						if ( is_array( $value1 ) ) {
							$update_arr = array_keys( $value1 );
							if ( in_array( 'wp_version_check', $update_arr ) ) {
								$schedule_name = sanitize_textarea_field( wp_unslash( $_POST['schedule_name_wp_version_check'] ) );
								$this->options_updated( 'wp_version_check', $schedule_name, $key1, $value1, $cron_jobs );
							}
						}
					}
				}
				if ( ! empty( $_POST['schedule_name_wp_update_plugins'] ) ) {
					$cron_jobs = get_option( 'cron' );
					foreach ( $cron_jobs as $key1 => $value1 ) {
						if ( is_array( $value1 ) ) {
							$update_arr = array_keys( $value1 );
							if ( in_array( 'wp_update_plugins', $update_arr ) ) {
								$schedule_name = sanitize_textarea_field( wp_unslash( $_POST['schedule_name_wp_update_plugins'] ) );
								$this->options_updated( 'wp_update_plugins', $schedule_name, $key1, $value1, $cron_jobs );
							}
						}
					}
				}
				if ( ! empty( $_POST['schedule_name_wp_update_themes'] ) ) {
					$cron_jobs = get_option( 'cron' );
					foreach ( $cron_jobs as $key1 => $value1 ) {
						if ( is_array( $value1 ) ) {
							$update_arr = array_keys( $value1 );
							if ( in_array( 'wp_update_themes', $update_arr ) ) {
								$schedule_name = sanitize_textarea_field( wp_unslash( $_POST['schedule_name_wp_update_themes'] ) );
								$this->options_updated( 'wp_update_themes', $schedule_name, $key1, $value1, $cron_jobs );
							}
						}
					}
				}
			}
		}

		if ( is_multisite() ) {
			$wp_crontrol_install_url = network_admin_url( 'plugin-install.php?tab=plugin-information&plugin=wp-crontrol' );
		} else {
			$wp_crontrol_install_url = admin_url( 'plugin-install.php?tab=plugin-information&plugin=wp-crontrol' );
		}

		?>
		<div class="wrap">
		<h2>Update Intervals</h2>

			<details>
			<summary><strong><?php esc_html_e( 'Various links of this plugin', 'update-intervals' ); ?></strong></summary>
			<?php $this->credit(); ?>
			</details>

			<div class="wrap">
				<h2><?php esc_html_e( 'Settings' ); ?></h2>	
				<form method="post">
				<?php wp_nonce_field( 'uiv_schedules', 'updateintervals_schedules' ); ?>
				<?php do_action( 'uiv_intervals', 'wp_version_check', wp_get_schedule( 'wp_version_check' ) ); ?>
				<?php do_action( 'uiv_intervals', 'wp_update_plugins', wp_get_schedule( 'wp_update_plugins' ) ); ?>
				<?php do_action( 'uiv_intervals', 'wp_update_themes', wp_get_schedule( 'wp_update_themes' ) ); ?>
				<div style="margin: 5px; padding: 5px;">
					<details style="margin-bottom: 5px;">
					<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><?php esc_html_e( 'Add Cron Schedule : It is possible with the following plugin.', 'update-intervals' ); ?></summary>
						<div style="margin: 5px; padding: 5px;">
						<a href="<?php echo esc_url( $wp_crontrol_install_url ); ?>" class="page-title-action">WP Crontrol</a>
						</div>
					</details>
				</div>
				<?php submit_button( __( 'Update' ), 'primary', 'update-intervals-save', true ); ?>
				</form>
			</div>
		</div>
		<?php
	}

	/** ==================================================
	 * Schedule select form
	 *
	 * @param string $update_hook  Hook name.
	 * @param int    $current_key  Schedule name.
	 * @since 1.00
	 */
	public function schedule_select_form( $update_hook, $current_key ) {

		$schedules = wp_get_schedules();

		switch ( $update_hook ) {
			case 'wp_version_check':
				$update_text = 'WordPress';
				break;
			case 'wp_update_plugins':
				$update_text = __( 'Plugins' );
				break;
			case 'wp_update_themes':
				$update_text = __( 'Themes' );
				break;
		}

		?>
		<div style="margin: 5px; padding: 5px;">
			<details style="margin-bottom: 5px;" open>
			<summary style="cursor: pointer; padding: 10px; border: 1px solid #ddd; background: #f4f4f4; color: #000;"><?php echo esc_html( $update_text ); ?></summary>
				<div style="margin: 5px; padding: 5px;">
					<select name="schedule_name_<?php echo esc_attr( $update_hook ); ?>">
					<?php
					foreach ( $schedules as $key => $value ) {
						if ( $key === $current_key ) {
							?>
							<option value="<?php echo esc_attr( $key ); ?>" selected><?php echo esc_html( $value['display'] ); ?></option>
							<?php
						} else {
							?>
							<option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value['display'] ); ?></option>
							<?php
						}
					}
					?>
					</select>
				</div>
			</details>
		</div>
		<?php
	}

	/** ==================================================
	 * Update wp_options table.
	 *
	 * @param  string $update_hook  Hook name.
	 * @param  string $schedule_name  New schedule name.
	 * @param  int    $key1  Cron run time.
	 * @param  array  $value1  Cron jobs to change.
	 * @param  array  $cron_jobs  Current all cron jobs.
	 * @since 1.00
	 */
	private function options_updated( $update_hook, $schedule_name, $key1, $value1, $cron_jobs ) {

		foreach ( $value1 as $key2 => $value2 ) {
			if ( $update_hook === $key2 ) {
				$key3 = key( $value2 );
				switch ( $update_hook ) {
					case 'wp_version_check':
						$update_text = 'WordPress';
						break;
					case 'wp_update_plugins':
						$update_text = __( 'Plugins' );
						break;
					case 'wp_update_themes':
						$update_text = __( 'Themes' );
						break;
				}
				$current_interval = $cron_jobs[ $key1 ][ $update_hook ][ $key3 ]['interval'];
				$schedules = wp_get_schedules();
				$new_interval = $schedules[ $schedule_name ]['interval'];
				if ( $current_interval <> $new_interval ) {
					$cron_jobs[ $key1 ][ $update_hook ][ $key3 ] = array(
						'schedule' => $schedule_name,
						'args' => array(),
						'interval' => $new_interval,
					);
					$old_update_time = get_date_from_gmt( gmdate( 'Y-m-d H:i:s', $key1 ) );
					update_option( 'cron', $cron_jobs );
					/* translators: Save update interval message */
					echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html( sprintf( __( '%1$s : The new update interval will be applied after the end of the event on %2$s.', 'update-intervals' ), $update_text, $old_update_time ) ) . '</li></ul></div>';
				}
			}
		}
	}

	/** ==================================================
	 * Credit
	 *
	 * @since 1.00
	 */
	private function credit() {

		$plugin_name    = null;
		$plugin_ver_num = null;
		$plugin_path    = plugin_dir_path( __DIR__ );
		$plugin_dir     = untrailingslashit( wp_normalize_path( $plugin_path ) );
		$slugs          = explode( '/', $plugin_dir );
		$slug           = end( $slugs );
		$files          = scandir( $plugin_dir );
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file || is_dir( $plugin_path . $file ) ) {
				continue;
			} else {
				$exts = explode( '.', $file );
				$ext  = strtolower( end( $exts ) );
				if ( 'php' === $ext ) {
					$plugin_datas = get_file_data(
						$plugin_path . $file,
						array(
							'name'    => 'Plugin Name',
							'version' => 'Version',
						)
					);
					if ( array_key_exists( 'name', $plugin_datas ) && ! empty( $plugin_datas['name'] ) && array_key_exists( 'version', $plugin_datas ) && ! empty( $plugin_datas['version'] ) ) {
						$plugin_name    = $plugin_datas['name'];
						$plugin_ver_num = $plugin_datas['version'];
						break;
					}
				}
			}
		}
		$plugin_version = __( 'Version:' ) . ' ' . $plugin_ver_num;
		/* translators: FAQ Link & Slug */
		$faq       = sprintf( __( 'https://wordpress.org/plugins/%s/faq', 'update-intervals' ), $slug );
		$support   = 'https://wordpress.org/support/plugin/' . $slug;
		$review    = 'https://wordpress.org/support/view/plugin-reviews/' . $slug;
		$translate = 'https://translate.wordpress.org/projects/wp-plugins/' . $slug;
		$facebook  = 'https://www.facebook.com/katsushikawamori/';
		$twitter   = 'https://twitter.com/dodesyo312';
		$youtube   = 'https://www.youtube.com/channel/UC5zTLeyROkvZm86OgNRcb_w';
		$donate    = __( 'https://shop.riverforest-wp.info/donate/', 'update-intervals' );

		?>
		<span style="font-weight: bold;">
		<div>
		<?php echo esc_html( $plugin_version ); ?> | 
		<a style="text-decoration: none;" href="<?php echo esc_url( $faq ); ?>" target="_blank" rel="noopener noreferrer">FAQ</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $support ); ?>" target="_blank" rel="noopener noreferrer">Support Forums</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $review ); ?>" target="_blank" rel="noopener noreferrer">Reviews</a>
		</div>
		<div>
		<a style="text-decoration: none;" href="<?php echo esc_url( $translate ); ?>" target="_blank" rel="noopener noreferrer">
		<?php
		/* translators: Plugin translation link */
		echo esc_html( sprintf( __( 'Translations for %s' ), $plugin_name ) );
		?>
		</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $youtube ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-video-alt3"></span></a>
		</div>
		</span>

		<div style="width: 250px; height: 180px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
		<h3><?php esc_html_e( 'Please make a donation if you like my work or would like to further the development of this plugin.', 'update-intervals' ); ?></h3>
		<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
		<button type="button" style="margin: 5px; padding: 5px;" onclick="window.open('<?php echo esc_url( $donate ); ?>')"><?php esc_html_e( 'Donate to this plugin &#187;' ); ?></button>
		</div>

		<?php
	}
}


