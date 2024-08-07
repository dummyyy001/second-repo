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
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'wordpress' );

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
define( 'AUTH_KEY',         'sj8mg#3.p_zVkVd93_nxpax)h|u$Tv4.-+NAXtG~ EEhi!Qhx=?vJNFhc<hW:=>0' );
define( 'SECURE_AUTH_KEY',  '5 w4GP@:e}=}9Ne_5gs[Pz2::MuZzB.$sk]g5dLAV,DMR=3a7yK5WDbkH2781tA)' );
define( 'LOGGED_IN_KEY',    'lTu:F)`=?9-_/>H#Xx} 5a7A~O_(mxrN}lE#0V2uc7zmPkxo 8$,9@u1e#Z.S>K%' );
define( 'NONCE_KEY',        'vT.#+m,I</yX8Q$V&k27ZDFWZ@c3yGkSoi&md|czY>1KIu~U:xQ!T$fS%&_8/v:,' );
define( 'AUTH_SALT',        'S^3OS,adRDzw37}geh.t;/W$Mi)~#T1go+W}^-,:eFw6n]G>/{?22&q2+}n[PSnK' );
define( 'SECURE_AUTH_SALT', '>OfH3DFDY<$py{CIqAynaVO={)`&K8zf=r!lUAaIKKq#S8TSCy?}?Udiuj2Z_e|}' );
define( 'LOGGED_IN_SALT',   '(z#V6yq+vA7X{InB[=~X)yd4N|]viPSO#1EGdi-0/cNGA`1M1G=F}a)=w>V.U4[=' );
define( 'NONCE_SALT',       ']F#bNcYa{KQeAWT-_s%@Ts}t[};k?, YKtbTZ` F{>7MIhd._[zgl@T$Xc>G>Q2n' );

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', true);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
