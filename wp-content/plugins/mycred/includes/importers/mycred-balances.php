<?php
if ( ! defined( 'myCRED_VERSION' ) ) exit;

if ( class_exists( 'WP_Importer' ) ) {
	class myCRED_Importer_Balances extends WP_Importer {

		var $id;
		var $file_url;
		var $import_page;
		var $delimiter;
		var $posts = array();
		var $imported;
		var $skipped;

		/**
		 * Construct
		 */
		public function __construct() {
			$this->import_page = 'mycred_import_balance';
		}

		/**
		 * Registered callback function for the WordPress Importer
		 * Manages the three separate stages of the CSV import process
		 */
		function load() {
			$this->header();

			if ( ! empty( $_POST['delimiter'] ) )
				$this->delimiter = stripslashes( trim( $_POST['delimiter'] ) );

			if ( ! $this->delimiter )
				$this->delimiter = ',';

			$step = empty( $_GET['step'] ) ? 0 : (int) $_GET['step'];
			switch ( $step ) {
				case 0:
					$this->greet();
					break;
				case 1:
					check_admin_referer( 'import-upload' );
					if ( $this->handle_upload() ) {

						if ( $this->id )
							$file = get_attached_file( $this->id );
						else
							$file = ABSPATH . $this->file_url;

						add_filter( 'http_request_timeout', array( $this, 'bump_request_timeout' ) );

						if ( function_exists( 'gc_enable' ) )
							gc_enable();

						@set_time_limit(0);
						@ob_flush();
						@flush();

						$this->import( $file );
					}
					break;
			}
			$this->footer();
		}

		/**
		 * format_data_from_csv function.
		 */
		function format_data_from_csv( $data, $enc ) {
			return ( $enc == 'UTF-8' ) ? $data : utf8_encode( $data );
		}

		/**
		 * import function.
		 */
		function import( $file ) {
			global $wpdb, $mycred;

			$this->imported = $this->skipped = 0;

			if ( ! is_file( $file ) ) {
				echo '<p><strong>' . __( 'Sorry, there has been an error.', 'mycred' ) . '</strong><br />';
				echo __( 'The file does not exist, please try again.', 'mycred' ) . '</p>';
				$this->footer();
				die();
			}

			ini_set( 'auto_detect_line_endings', '1' );

			if ( ( $handle = fopen( $file, "r" ) ) !== FALSE ) {

				$header = fgetcsv( $handle, 0, $this->delimiter );
				$no_of_columns = sizeof( $header );
				if ( $no_of_columns == 3 || $no_of_columns == 4 ) {

					$loop = 0;
					$mycred_types = mycred_get_types();

					while ( ( $row = fgetcsv( $handle, 0, $this->delimiter ) ) !== FALSE ) {

						$log_entry = '';
						if ( $no_of_columns == 3 )
							list( $id, $balance, $point_type ) = $row;
						else
							list( $id, $balance, $point_type, $log_entry ) = $row;

						$user = false;
						if ( is_numeric( $id ) )
							$user = get_userdata( $id );

						if ( $user === false )
							$user = get_user_by( 'email', $id );
						
						if ( $user === false )
							$user = get_user_by( 'login', $id );
							
						if ( $user === false ) {
							$this->skipped ++;
							continue;
						}

						if ( ! isset( $mycred_types[ $point_type ] ) ) {
							if ( $point_type != '' )
								$log_entry = $point_type;
						}

						if ( $point_type == '' )
							$point_type = 'mycred_default';

						$method = trim( $_POST['method'] );
						if ( $method == 'add' ) {
							$current_balance = mycred_get_user_meta( $user->ID, $point_type, '', true );
							$balance = $current_balance+$balance;
						}
						
						mycred_update_user_meta( $user->ID, $point_type, '', $balance );
						
						if ( ! empty( $log_entry ) ) {
							$wpdb->insert(
								$mycred->log_table,
								array(
									'ref'     => 'import',
									'ref_id'  => NULL,
									'user_id' => $user->ID,
									'creds'   => $mycred->number( $balance ),
									'ctype'   => $point_type,
									'time'    => date_i18n( 'U' ),
									'entry'   => sanitize_text_field( $log_entry ),
									'data'    => ''
							)
						);
						}

						$loop ++;
						$this->imported++;
				    }

				} else {

					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'mycred' ) . '</strong><br />';
					echo __( 'The CSV is invalid.', 'mycred' ) . '</p>';
					$this->footer();
					die();

				}

			    fclose( $handle );
			}

			// Show Result
			echo '<div class="updated settings-error below-h2"><p>
				'.sprintf( __( 'Import complete - A total of <strong>%d</strong> balances were successfully imported. <strong>%d</strong> was skipped.', 'mycred' ), $this->imported, $this->skipped ).'
			</p></div>';

			$this->import_end();
		}

		/**
		 * Performs post-import cleanup of files and the cache
		 */
		function import_end() {
			echo '<p><a href="' . admin_url( 'admin.php?page=myCRED' ) . '" class="button button-large button-primary">' . __( 'View Log', 'mycred' ) . '</a> <a href="' . admin_url( 'import.php' ) . '" class="button button-large button-primary">' . __( 'Import More', 'mycred' ) . '</a></p>';

			do_action( 'import_end' );
		}

		/**
		 * Handles the CSV upload and initial parsing of the file to prepare for
		 * displaying author import options
		 * @return bool False if error uploading or invalid file, true otherwise
		 */
		function handle_upload() {

			if ( empty( $_POST['file_url'] ) ) {

				$file = wp_import_handle_upload();

				if ( isset( $file['error'] ) ) {
					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'mycred' ) . '</strong><br />';
					echo esc_html( $file['error'] ) . '</p>';
					return false;
				}

				$this->id = (int) $file['id'];

			} else {

				if ( file_exists( ABSPATH . $_POST['file_url'] ) ) {

					$this->file_url = esc_attr( $_POST['file_url'] );

				} else {

					echo '<p><strong>' . __( 'Sorry, there has been an error.', 'mycred' ) . '</strong></p>';
					return false;

				}

			}

			return true;
		}

		/**
		 * header function.
		 */
		function header() {
			echo '<div class="wrap"><h2>' . __( 'Import Balances', 'mycred' ) . '</h2>';
		}

		/**
		 * footer function.
		 */
		function footer() {
			echo '</div>';
		}

		/**
		 * greet function.
		 */
		function greet() {
			global $mycred;

			echo '<div class="narrow">';
			echo '<p>' . __( 'Import balances from a CSV file.', 'mycred' ).'</p>';

			$action = 'admin.php?import=mycred_import_balance&step=1';

			$bytes = apply_filters( 'import_upload_size_limit', wp_max_upload_size() );
			$size = size_format( $bytes );
			$upload_dir = wp_upload_dir();
			if ( ! empty( $upload_dir['error'] ) ) :
				?><div class="error"><p><?php _e( 'Before you can upload your import file, you will need to fix the following error:', 'mycred' ); ?></p>
				<p><strong><?php echo $upload_dir['error']; ?></strong></p></div><?php
			else :
				?>
				<form enctype="multipart/form-data" id="import-upload-form" method="post" action="<?php echo esc_attr( wp_nonce_url( $action, 'import-upload' ) ); ?>">
					<table class="form-table">
						<tbody>
							<tr>
								<th>
									<label for="upload"><?php _e( 'Choose a file from your computer:', 'mycred' ); ?></label>
								</th>
								<td>
									<input type="file" id="upload" name="import" size="25" />
									<input type="hidden" name="action" value="save" />
									<input type="hidden" name="max_file_size" value="<?php echo $bytes; ?>" />
									<small><?php printf( __( 'Maximum size: %s', 'mycred' ), $size ); ?></small>
								</td>
							</tr>
							<tr>
								<th>
									<label for="file_url"><?php _e( 'OR enter path to file:', 'mycred' ); ?></label>
								</th>
								<td>
									<?php echo ' ' . ABSPATH . ' '; ?><input type="text" id="file_url" name="file_url" size="25" />
								</td>
							</tr>
							<tr>
								<th><label><?php _e( 'Delimiter', 'mycred' ); ?></label><br/></th>
								<td><input type="text" name="delimiter" placeholder="," size="2" /></td>
							</tr>
							<tr>
								<th><label><?php _e( 'Method', 'mycred' ); ?></label><br/></th>
								<td><select name="method">
									<option value=""><?php _e( 'Replace current balances with the amount in this CSV file', 'mycred' ); ?></option>
									<option value="add"><?php _e( 'Adjust current balances according to the amount in this CSV file', 'mycred' ); ?></option>
								</select></td>
							</tr>
						</tbody>
					</table>
					<p class="submit">
						<input type="submit" class="button" value="<?php esc_attr_e( 'Upload file and import' ); ?>" />
					</p>
				</form>
				<?php
			endif;

			echo '</div>';
		}

		/**
		 * Added to http_request_timeout filter to force timeout at 60 seconds during import
		 * @return int 60
		 */
		function bump_request_timeout( $val ) {
			return 60;
		}
	}
}
