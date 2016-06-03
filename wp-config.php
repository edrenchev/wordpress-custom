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
define('DB_NAME', 'wordpress_custom');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

/** MySQL hostname */
define('DB_HOST', 'localhost');

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
define('AUTH_KEY',         'SBRhvdY`0>?B]Z(p2!]KXpVsT5[d(~@@<nk)%lRv!g.hb`orO>1wQ,v.+calg./=');
define('SECURE_AUTH_KEY',  'j0U}BbfAAE@8(c9VV}#_v6Nb$78Zk/fV@&-_eLog2=w>Y+B<.Tk8nE%q}O$JnSaP');
define('LOGGED_IN_KEY',    'OB7N%q!mX4o&Q8?{`Cogytt~m75n6REviZ2M6db8#GE};Nj3Lidms,YuVvu~tA1=');
define('NONCE_KEY',        'do03chp2b/j4?rTxw9ZjZ;OzSsu)t[L,6_#P&d:^S??pn=cytB@H*`-t[XW9ArMu');
define('AUTH_SALT',        'D6(ojcchDfa`~rUt?SK>KWSj3XZ?je,DvMSx=pQ(z`;:zfaI*M3f sTk=HTc-gII');
define('SECURE_AUTH_SALT', '&Hu2~MCBpY0e4wwI7y|rs-^%u0zfB%x+7 +vuTfthF|g&?<!T.xmcIn+Ehr&A5y/');
define('LOGGED_IN_SALT',   'FZ!h<^<}.=I/b+ <UXC>kF,%s}|SfbluJr+UAo;W^VjltPM=9i?Pu!Wc+qg4`KyY');
define('NONCE_SALT',       '?Jj;4FiF^ui&P*+*xXPz5oDpDAbMV.)f;S2G:,aRN]c[`ett^ehPdD]}E))~vLRh');

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
define('WP_DEBUG', true);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
