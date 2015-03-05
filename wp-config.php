<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'katzyoonDBypbks');

/** MySQL database username */
define('DB_USER', 'katzyoonDBypbks');

/** MySQL database password */
define('DB_PASSWORD', '1zorR7AD1d');

/** MySQL hostname */
define('DB_HOST', '127.0.0.1');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

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
define('AUTH_KEY',         '*%YI9kY]PT-7irxoH}+HL-0+9k`T>,c5HuV&)[RW2UJjZd|g_ ~P@!dM9y^ Xy5,');
define('SECURE_AUTH_KEY',  'hSPRb9%e/WivxK5Bfho]*Bov-74MVgK4Lokz~Dx=DQc+xfF2+R<6NJ->O`U9_nWq');
define('LOGGED_IN_KEY',    'Ps?-xbCQmhZRiq7_5j)^6/@W`B3JC%(c0pC+XN`Y)]CCt?@$-hP{f:UZ[`5g}tQe');
define('NONCE_KEY',        '+AMONa}H+grWX>ReI-!jDT2h3#04X;5S?AJ,xJ+IrIg?_XRAb,,Tm+: 1~oz$```');
define('AUTH_SALT',        'Y:2U45]BPlf04,x$i~hO_6LJd4%)JVE6rQI@(E*dd6UE<=B3G5M<nWv|hM!Vu*g1');
define('SECURE_AUTH_SALT', '6iYjxYn;irMhj~E<hKA1ClUNg96EKi=M/tDY|:Lq_B*vMI+A5;95;TZ0h> 9Kg=M');
define('LOGGED_IN_SALT',   'aPqF(G9D++VZX_+ ?/WD3L@!+b:F41-&92/X2B?v,uQpgI7-T%1ddFuYlQ^I_L&z');
define('NONCE_SALT',       '%e|E_[xTAW=PM>ZY87gg[^mDcVwF3B WpR8a$^!%qN/;rZrDVKO~Bwyr?:Xj`UtJ');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'dvn_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', '');

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
