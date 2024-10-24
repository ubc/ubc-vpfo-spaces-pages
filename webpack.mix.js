// webpack.mix.js

let mix = require( 'laravel-mix' );
let url = 'https://ubccms.test';

mix
	.disableSuccessNotifications()
	//.js( 'src/js/index.js', 'main.js' )
	.sass(
		'src/style.scss',
		'style.css',
		{
			sassOptions: {
				outputStyle: 'compressed'
			}
		}
	)
	.options(
		{
			processCssUrls: false
		}
	)
	.setPublicPath( '/' )
	.browserSync( url )
	.copy( 'src/images/svg', 'images/svg' );