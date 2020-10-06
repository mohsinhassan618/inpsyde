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
define( 'DB_NAME', 'inpsyde' );

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
define( 'AUTH_KEY',         '-~?ToCR,Beab#,1:eg|2pa}?T+qtKu!+Byy#CMvt@aGb9`h|]}L-(Q<7JaT{sAr=' );
define( 'SECURE_AUTH_KEY',  ')4o%I{@xwYqG`h]h7](/XSix&h &;Z<dB#Fv{Q8U3K:9$j/H-|p<?_cz8wLy56/Z' );
define( 'LOGGED_IN_KEY',    'Eyq{34qD6-/&5Z+|)ZZ/U,(?MFrI(B}3)-;),SWe{jf?H6X6a>O_g4OWjOHfh8[/' );
define( 'NONCE_KEY',        'CIQ[3ikBm3$EGS.-I9Kk$]$r2_~!|+A8.P 7}MWZXGufL:_63y-&tI{$u}%NSuEw' );
define( 'AUTH_SALT',        '{E<0FkEZLbGGf&ZJ)4q=bgO_87Yf`D0S_~F%fQksVuyfj~YbD0 j<phuG<>^J$*<' );
define( 'SECURE_AUTH_SALT', 'O9o([.(|UX^QLv] ]GM(THX3h&0;=ATD]m,:tON.p<+K_cjILYv#%/K]g5NZfTY.' );
define( 'LOGGED_IN_SALT',   ',X7:JoPsC-8};&unXp8yJ*vx82tzz?@5|aKK<!&tYvPcM[)P$MV)zcCs$ UG(.Md' );
define( 'NONCE_SALT',       'zhB0_D^.[a/3A8(! Q-%KgoVP&IO}@FiJm];scElgymF:tcXqFCL7mM6)]srnGMJ' );

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/**
 *
 *
 */


$domain = 'http://inpsyde.local';

define('WP_SITEURL', "{$domain}/wordpress");
define('WP_HOME',"{$domain}");


define( 'WP_CONTENT_DIR', dirname( __FILE__ ) . '/wp-content' );
define( 'WP_CONTENT_URL', $domain . '/wp-content' );


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
define( 'WP_DEBUG', true );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __FILE__ ) . '/wordpress' );
}

/** Sets up WordPress vars and included files. */
require_once( ABSPATH . 'wp-settings.php' );
