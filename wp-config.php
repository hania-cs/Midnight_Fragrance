<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://wordpress.org/documentation/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'midnightfragrance' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', '' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         '6Pbs#b+uUXC.,`_,;8gYw4KKmn{des]p]&t!<$=_(:7u!hPwf_G]%GV6VxrYCDv/' );
define( 'SECURE_AUTH_KEY',  'hYZa&7qmb?L?AnD+$11x+!%*ccNpoKYjm>`ymdABeWnZhyl]>IR94TIfyV`c>Rli' );
define( 'LOGGED_IN_KEY',    'C&4~:$qtoAL-j3|r`a $n9Bb39y!YT7Z7FNeEmL,KiW?(-~oyvrmB[D|uE8cX!I9' );
define( 'NONCE_KEY',        'iyZ&Mx$!X)30RW2rhaUR# ># ,e=cYG-Q?}`.4g/8.MJ{f3U1hVn>he bS#,l?Z7' );
define( 'AUTH_SALT',        'psu?VS*%a@W%}Qa$.PoRv$D-m<%vCy%%H~t_WKP9MzzHw~Qu_h8cTBM_B;+LbA[R' );
define( 'SECURE_AUTH_SALT', '8T%O}Ky5@3r0,>t;-dSbVHw*E^binbZ2#sBW@,JURMaXf<XGcdXR d2C/ph]CuoQ' );
define( 'LOGGED_IN_SALT',   ' ose/4]r 5Ee:7!&5b5|/r V6cP~%i<z;j<u2K.hN7 {A,Uq@RbloAcg8-4+-8iJ' );
define( 'NONCE_SALT',       '%>wg}pv3OQ#8NM~kVC^.*.I}{s=bp8<C~#,8!VUF&OZl4|iWic?&xhUX`X7(sLs$' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/documentation/article/debugging-in-wordpress/
 */
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
