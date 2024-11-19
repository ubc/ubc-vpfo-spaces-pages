// webpack.mix.js

let mix = require( 'laravel-mix' );
let url = 'https://ubccms.test';

mix
	.disableSuccessNotifications()
	.js( 'src/js/classrooms-image-gallery-glider.js', 'js/classrooms-image-gallery-glider.js' )
	.js( 'src/js/accordion.js', 'js/accordion.js' )
	.js( 'src/js/layout-modal.js', 'js/layout-modal.js' )
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