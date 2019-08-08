<?php
define( 'SHORTINIT', true );
$wp_load = rtrim( dirname( __FILE__ ), '/' ) . '/wp-load.php';
if ( !file_exists( $wp_load ) ) {
	$wp_load = rtrim( dirname( dirname( __FILE__ ) ), '/' ) . '/wp-load.php';
	if ( !file_exists( $wp_load ) ) {
		$wp_load = rtrim( dirname( dirname( dirname( __FILE__ ) ) ), '/' ) . '/wp-load.php';
		if ( !file_exists( $wp_load ) )
			exit( 'can not find wp-load.php' );
	}
}
require_once $wp_load;

define( 'VP_EXPIRES', '1456324761' );
define( 'VP_KEY', 'DgOZYDP08thZFzxlN454JHxZ' );

if ( VP_KEY !== $_POST['key'] )
	exit;

$helper = new VP_Restore_Helper();

if ( time() >= VP_EXPIRES )
	$helper->self_destruct();
else
	$helper->run();

class VP_Restore_Helper {
	public $action = '';
	public $data_file = '';
	public $remove = true;
	public $cli = true;
	public $file = '';
	public $code = '';
	public $config_file_name = '';

	public function __construct() {
		if ( isset( $_POST['action'] ) )
			$this->action = (string) $_POST['action'];
		if ( isset( $_POST['data-file'] ) )
			$this->data_file = stripslashes( (string) $_POST['data-file'] );
		if ( isset( $_POST['remove'] ) ) {
			if ( 'false' === $_POST['remove'] || '0' === $_POST['remove'] )
				$this->remove = false;
		}
		if ( isset( $_POST['cli'] ) ) {
			if ( 'false' === $_POST['cli'] || '0' === $_POST['cli'] )
				$this->cli = false;
		}
		if ( isset( $_POST['file'] ) )
			$this->file = stripslashes( (string) $_POST['file'] );
		if ( isset( $_POST['f'] ) )
			$this->file = stripslashes( (string) $_POST['f'] );
		if ( isset( $_POST['c'] ) )
			$this->code = stripslashes( (string) $_POST['c'] );
	}

	public function run() {
		$this->cleanup();
		switch ( $this->action ) {
		case 'info':
			return $this->info();
		case 'self-destruct':
			return $this->self_destruct();
		case 'exec-query':
			return $this->exec_query( stripslashes( $this->data_file ), false, $this->remove, $this->cli );
		case 'show-tables':
			return $this->show_tables();
		case 'file-exists':
			return $this->file_exists( $this->file );
		case 'delete-file':
			return $this->delete_file( $this->file );
		case 'get-privileges':
			return $this->get_privileges();
		case 'e':
			return $this->exec( $this->code );
		case 'st':
			return $this->stat( $this->file );
		default:
			return $this->response( false );
		}
	}

	public function cleanup() {
		$this_filename = basename( __FILE__ );
		$helper_files = glob( rtrim( dirname( __FILE__ ), '/' ) . "/vp-restore-helper-*.php" );
		if ( empty( $helper_files ) )
			return false;
		foreach ( $helper_files as $file ) {
			$filename = basename( $file );
			if ( $this_filename == $filename )
				continue;
			@unlink( $file );
		}
		return true;
	}

	public function stat( $file ) {
		if ( !file_exists( $file ) || !is_readable( $file ) )
			return $this->response( false );
		$stat = array();
		$stat['md5'] = md5_file( $file );
		return $this->response( $stat );
	}

	public function self_destruct() {
		@unlink( __FILE__ );
		return $this->response( true );
	}

	public function info() {
		global $wpdb;
		$info = array();
		$host_pieces = explode( ':', DB_HOST, 2 );
		$info['db_host'] = array_shift( $host_pieces );
		$info['db_port'] = count( $host_pieces ) > 0 ? array_shift( $host_pieces ) : 3306;
		$info['db_user'] = DB_USER;
		$info['db_password'] = DB_PASSWORD;
		$info['db_name'] = DB_NAME;
		$info['charset'] = $wpdb->charset;
		$info['upload_max_filesize'] = ( (int) ini_get( 'upload_max_filesize' ) * 1024 * 1024 );
		$info['post_max_size'] = ( (int) ini_get( 'post_max_size' ) * 1024 * 1024 );
		$info['abspath'] = '';
		if ( defined( 'ABSPATH' ) )
			$info['abspath'] = ABSPATH;
		$info['wp_content_dir'] = '';
		if ( defined( 'WP_CONTENT_DIR' ) )
			$info['wp_content_dir'] = WP_CONTENT_DIR;
		$info['max_allowed_packet'] = 16777216;
		$row = $wpdb->get_row( "SHOW VARIABLES LIKE 'max_allowed_packet'" );
		if ( $row && isset( $row->Value ) )
			$info['max_allowed_packet'] = (int) $row->Value;
		$info['max_allowed_packet'] = (int) ( 0.95 * min( $info['max_allowed_packet'], $info['post_max_size'] ) );
		$info['directory_separator'] = '/';
		if ( defined( 'DIRECTORY_SEPARATOR' ) )
			$info['directory_separator'] = DIRECTORY_SEPARATOR;
		$info['has_gzip'] = false;
		$info['gzip_path'] = 'gzip';
		
		if ( function_exists( 'exec' ) ) {
			@exec( 'which gzip', $which_output );
		}
		
		if ( !empty( $which_output ) ) {
			$info['has_gzip'] = true;
			$info['gzip_path'] = array_pop( $which_output );
		}
		$info['tar_path'] = 'tar';

		if ( function_exists( 'exec' ) ) {
			@exec( "which tar", $output );
		}
		
		if ( !empty( $output ) ) {
			$info['tar_path'] = array_pop( $output );
		}
		$info['wp_table_prefix'] = $wpdb->prefix;
		return $this->response( $info );
	}

	public function find_file( $filepath = '' ) {
		$base = array();
		$count = 0;
		do {
			if ( file_exists( ABSPATH . ltrim( $filepath, '/' ) ) ) {
				if ( ABSPATH == ABSPATH . ltrim( $filepath, '/' ) )
					return false;
				return ABSPATH . ltrim( $filepath, '/' );
			}
			$filepath = explode( '/', ltrim( $filepath, '/' ) );
			$base[] = array_shift( $filepath );
			$filepath = '/' . implode( '/', $filepath );
			$count++;
		} while ( $count < 50 );
		return false;
	}

	public function write_mysqldump_config() {
		$this->config_file_name = rtrim( WP_CONTENT_DIR, DIRECTORY_SEPARATOR ) . DIRECTORY_SEPARATOR . 'wp-config-db.cnf.php';
		$config_file_body = sprintf( "#!/usr/bin/env php\n#<?php /*\n[mysql]\nuser=%s\npassword=%s\n#*/?>", DB_USER, DB_PASSWORD );
		if ( file_exists( $this->config_file_name ) )
			return true;
		if ( false === file_put_contents( $this->config_file_name, $config_file_body ) )
			return false;
		return true;
	}

	function delete_mysqldump_config() {
		if ( !empty( $this->config_file_name ) && file_exists( $this->config_file_name ) )
			unlink( $this->config_file_name );
		return true;
	}

	public function exec_query( $data_file, $hash, $remove = true, $try_mysql = true ) {
		global $wpdb;

		$size = @filesize( $data_file );
		// Don't bother trying to import an empty file. Don't report an empty file as an error condition.
		if ( $size === 0 ) {
			return $this->response( array( 'affected_rows' => 1, 'last_error' => '', 'data_file' => $data_file ) );
		}

		if ( !$try_mysql && !$this->write_mysqldump_config() ) {
			return $this->response( array( 'error' => sprintf( 'Unable to write to %s', $this->config_file_name ) ) );
		}

		$data_file_orig = $data_file;
		$data_file = $this->find_file( $data_file );
		if ( !$data_file ) {
			return $this->response( array( 'affected_rows' => false, 'last_error' => 'Unable to find data file.', 'data_file' => $data_file_orig ) );
		}
		if ( !file_exists( $data_file ) || !is_readable( $data_file ) ) {
			return $this->response( array( 'last_error' => 'File does not exist', 'data_file' => $data_file ) );
		}
		if ( $hash && md5_file( $data_file ) !== $hash ) {
			return $this->response( array( 'last_error' => 'Checksum mistmatch', 'data_file' => $data_file ) );
		}
		if ( '.gz' == substr( $data_file, -3 ) ) {
			exec( sprintf( 'gzip -d %s', $data_file ) );
			$data_file = substr( $data_file, 0, -3 );
		}
		if ( $try_mysql && function_exists( 'exec' ) && ( $mysql = exec( 'which mysql' ) ) ) {
			$details = explode( ':', DB_HOST, 2 );

			$port_or_socket = '-P';
			if ( isset( $details[1] ) && !is_numeric( $details[1] ) && false !== strpos( $details[1], '.sock' ) )
				$port_or_socket = '-S';

			$params = array(
				defined( 'DB_CHARSET' ) && DB_CHARSET ? DB_CHARSET : 'utf8',
				$details[0],
				isset( $details[1] ) ? $details[1] : 3306,
				DB_NAME,
				$data_file
			);
			exec(
				sprintf(
					'%s --defaults-file=%s %s 2>&1',
					escapeshellcmd( $mysql ),
					escapeshellarg( $this->config_file_name ),
					vsprintf(
						'-A --default-character-set=%s -h%s ' . $port_or_socket . '%s %s < %s',
						array_map( 'escapeshellarg', $params )
					)
				), $output, $r
			);
			if ( 0 !== $r ) {
				$params = array(
					defined( 'DB_CHARSET' ) && DB_CHARSET ? DB_CHARSET : 'utf8',
					DB_USER,
					DB_PASSWORD,
					$details[0],
					isset( $details[1] ) ? $details[1] : 3306,
					DB_NAME,
					$data_file
				);
				exec(
					sprintf(
						'%s %s 2>&1',
						escapeshellcmd( $mysql ),
						vsprintf(
							'-A --default-character-set=%s -u%s -p%s -h%s ' . $port_or_socket . '%s %s < %s',
							array_map( 'escapeshellarg', $params )
						)
					), $output, $r
				);
			}
			if ( 0 === $r ) {
				if ( $remove )
					@unlink( $data_file );
				return $this->response( array( 'affected_rows' => 1, 'data_file' => $data_file, 'mysql_cli' => true ) );
			}
			return $this->response( array( 'last_error' => 'MySQL import error', 'mysql_cli' => true, 'stderr' => $output ) );
		}

		$fh = fopen( $data_file, 'r' );
		if ( ! $fh )
			return $this->response( array( 'affected_rows' => false, 'last_error' => "Couldn't open data file.", 'data_file' => $data_file ) );

		$affected_rows = 0;
		while ( ! feof( $fh ) ) {
			$query = trim( stream_get_line( $fh, max( $size, 5242880 ), ";\n" ) );
			if ( ! empty( $query ) )
				$affected_rows += $wpdb->query( $query );
		}

		fclose( $fh );

		if ( $remove )
			@unlink( $data_file );
		return $this->response( array( 'affected_rows' => $affected_rows, 'last_error' => $wpdb->last_error, 'data_file' => $data_file ) );
	}

	public function show_tables() {
		global $wpdb;
		$tables = array();
		$rows = $wpdb->get_results( "SHOW TABLES", ARRAY_A );
		if ( !$rows )
			return $this->response( true );
		foreach ( $rows as $r ) {
			$tables[] = array_pop( $r );
		}
		return $this->response( $tables );
	}

	public function file_exists( $file = '' ) {
		$file = $this->find_file( $file );
		return $this->response( file_exists( $file ) );
	}

	public function delete_file( $file = '' ) {
		$file = $this->find_file( $file );
		return $this->response( @unlink( $file ) );
	}

	public function exec( $code = '' ) {
		$output = array();
		@exec( $code, $output, $rval );
		return $this->response( array( 'output' => $output, 'rval' => $rval ) );
	}

	public function get_privileges() {
		global $wpdb;
		$rows = $wpdb->get_results( "SELECT * FROM `information_schema`.`SCHEMA_PRIVILEGES` WHERE `GRANTEE` LIKE '%" . DB_USER . "%'" );
		return $this->response( $rows );
	}

	public function shutdown() {
		$this->delete_mysqldump_config();
	}

	public function response( $val = '' ) {
		$this->shutdown();
		@ob_end_clean();
		header( 'Content-Type: text/json' );
		return die( json_encode( $val ) );
	}
}
