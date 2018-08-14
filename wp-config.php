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
define('WP_CACHE', true);
define( 'WPCACHEHOME', '/Applications/MAMP/htdocs/tolmar/wp-content/plugins/wp-super-cache/' );
define('DB_NAME', 'dev_db');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'Tolmar33');

/** MySQL hostname */
define('DB_HOST', 'localhost:3306');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         '{SYo$E7 !,}el^RlBG,){3PZDSKN(#YJu7i^nQJ|7>3)x] ui;fF:T<J}&VP=hS7');
define('SECURE_AUTH_KEY',  'GCdVO}Xf?8lko}/zZ.rG]>X%eXx9}}.RB_1n$fgs&HI#,bpXrVD5s*MQ+[jLFeZK');
define('LOGGED_IN_KEY',    'P{HHS=rGmJ.2dR^2vrm5=rQxj5Lx%-yy?gkoVT:Utj<&+%jcG%cNe]RUS7h!([/a');
define('NONCE_KEY',        'uho|xU?j=6nwR)PmV=/-y;}Kk^ SxC5eeEF<vXm8MI2]o-0ZtEbFt?>>}lT`Dw{h');
define('AUTH_SALT',        ' Vk_mqp.ad%BU8UW5MDifHK}5F0Y) dpRHDc@iPxyuS70+vFe1nu@}];kLK=%TW!');
define('SECURE_AUTH_SALT', ')ueNp3Z+rbKkDpT8lJivZDz:`H7zoP&p`Fd},6+GF%TuVe$){@Q> P1*31?U;zOe');
define('LOGGED_IN_SALT',   'n*CVk~z5H!QSLK 806L;7Inp>+Z.x$qH8@ANdr3.ux|A.-]);8V-J8BJB6seL3<W');
define('NONCE_SALT',       'k5@c7i*A7eXrh:#gf}ilv]Jy0Dg7 [R3|W~`4.RsbCym_1<%7;JQVYDuiXcowF{-');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
