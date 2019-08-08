<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'pemuda' );

/** MySQL database username */
define( 'DB_USER', 'root' );

/** MySQL database password */
define( 'DB_PASSWORD', '' );

/** MySQL hostname */
define( 'DB_HOST', 'localhost' );

/** Database Charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The Database Collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '8d-1e.tBkLxZ7aV<?6NE~sB]u%&:kiy$[|FW@sKa1>|2;mP:_L`MOqdQ}Mn1W._t' );
define( 'SECURE_AUTH_KEY',  'FrjhzxVy@&t@.0Q$L%2r2k4=oUI50^|_H8(6qA:X0N2A{6%F_6 t*|BWmCh~KUkT' );
define( 'LOGGED_IN_KEY',    '(0qF3&WcM0u,RFgC{K0DDGzBhY:o%[or];0OYEWDC`A~=gL,twLSa5n;zBJbEemh' );
define( 'NONCE_KEY',        'O=[5OLAr(iOP6G;A+q;>AsG};yXLC|Wi:}[RCSmx*nxt,]m/cDr?$4 HRe.Z|FHk' );
define( 'AUTH_SALT',        'Ll^QsFA oZgU;r;re~cR@A{Z}y>J80( l  3U=k:+K4>xak~dwuF(H>h&yK}XDqK' );
define( 'SECURE_AUTH_SALT', 'D%vn(-/dm,@Oj:vJ,uv:=gbCAY`(%?%<[`h:HP+?`|NDHR[$9P5vnglDSE+KA`h ' );
define( 'LOGGED_IN_SALT',   '!BED`tf|__$nWb_b8Dpy<sF-mg:-p<beJ9xc3-+(q6DPcv)Dh3/o@_YuNt.tl}<[' );
define( 'NONCE_SALT',       '2hFBhym1G1j%u:ruG}=UJ<>Xg!IEHa@zae2sEHf@!6R>[q>#]t>u754]l!x>SaE9' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'pra_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
