// vim: ts=2:sw=2
const path = require('path');
const fs = require('fs');
const glob = require('glob');
const _ = require('underscore');
const { set } = require('grunt');
let lessDestFiles = [];
const prefix = 'awpcp-';

module.exports = function( grunt ) {
	grunt.config.set('compress.version', '');

	grunt.wpbdp = {
		registered: {},
		registerModule: function( config ) {
			var basedir = config.path;
			var id      = config.id || path.basename(basedir).replace(prefix, '');
			var setVersion = 'AWPCP_' + id.replace( '-', '_' ).toUpperCase() + '_MODULE_DB_VERSION';
console.log(setVersion + ' ? AWPCP_EXTRA_FIELDS_MODULE_DB_VERSION');

			this.registered[ id ] = config;

			var less_config    = {};
			var uglify_config  = {};
			var makepot_config = {};
			var potomo_config  = {};

			if ( ! _.isEmpty( config.less ) ) {
				config.less.forEach(function(g) {
					glob.sync( path.join( basedir, g ), {} ).forEach(function(f) {
						if ( f.endsWith( '.min.css' ) ) {
							return;
						}

						if ( ! f.endsWith( '.less' ) && ! f.endsWith( '.css' ) ) {
							return;
						}

						less_config[ f.replace( 'less/', '' ).replace( '.css', '.min.css' ).replace( '.less', '.min.css' ) ] = f;
					});
				});
			}

			if ( ! _.isEmpty( config.js ) ) {
				config.js.forEach(function(g) {
					glob.sync( path.join( basedir, g ), {ignore: ['../**/**.min.js', '**/*.min.js']} ).forEach(function(f) {
						uglify_config[ f.replace( '.js', '.min.js' ) ] = f;
					});
				});
			}

			if ( config.i18n || ! _.isEmpty( config.i18n ) ) {
				var textDomain = config.i18n.textDomain || prefix + id;
				var domainPath = path.join( basedir, config.i18n.domainPath || 'translations/' );

				if ( ! fs.existsSync( domainPath ) ) {
						domainPath = path.join( basedir, config.i18n.domainPath || 'languages/' );
				}

				if ( fs.existsSync( domainPath ) ) {
					makepot_config = {
						options: {
							cwd: basedir,
							domainPath: domainPath.replace(basedir, ''),
							potFilename: textDomain + '.pot',
							exclude: ['vendors/.*','vendor/.*'],
							updatePoFiles: true
						}
					};
					potomo_config = {
						options: {
							poDel: false,
						},
						files: [{
							expand: true,
							cwd: domainPath,
							src: ['*.po'],
							dest: domainPath,
							ext: '.mo',
							nonull: true
						}]
					};
				}
			}

			if ( ! _.isEmpty( less_config ) ) {
				grunt.config.set( 'less.' + id, {
					options: {
						cleancss: false,
						compress: true,
						strictImports: true
					},
					files: less_config
				} );

				grunt.config.set( 'watch.' + id + '_less', {
					files: [path.join(basedir, '**/*.less'), path.join(basedir, '**/**/*.less'), path.join(basedir, '**/*.css'), '!' + path.join(basedir, 'vendors/**/*'), '!' + path.join(basedir, '**/*.min.css'), '!' + path.join(basedir, 'assets/vendor/**/*')],
					tasks: [ 'less:' + id ]
				} );

		lessDestFiles = [...lessDestFiles, ...Object.keys(less_config)];
				grunt.config.set( 'watch.livereload', {
					options: { livereload: true },
					files: lessDestFiles,
				} );
			}

			if ( ! _.isEmpty( uglify_config ) ) {
				grunt.config.set( 'uglify.' + id, {options: { mangle: false }, files: uglify_config} );
				grunt.config.set( 'watch.' + id + '_js', {
					files: [path.join(basedir, '**/*.js'), '!' + path.join(basedir, 'vendors/**/*'), '!' + path.join(basedir, '**/*.min.js'), '!' + path.join(basedir, 'assets/vendor/**/*')],
					tasks: [ 'uglify:' + id ]
				} );
			}

			if ( ! _.isEmpty( makepot_config ) ) {
				grunt.config.set( 'makepot.' + id, makepot_config );
				grunt.config.set( 'potomo.' + id, potomo_config );
			}

			// Compress config.
			grunt.config.set( 'compress.' + id, {
				options: {
					archive: '../' + path.basename(basedir) + '-<%= compress.version %>.zip',
					mode: 'zip'
				},
				expand: true,
				cwd: basedir,
				dest: path.basename(basedir),
					src: [
						'**/*', '!**/*~', '!**/**.less', '!**/tests/**', '!**/**/less',
						'!**/.*', '!**/phpcs.xml', '!**/phpunit.xml', '!**/composer.json',
						'!**/composer.lock',
						'!**/package.json', '!**/package-lock.json', '!**/node_modules/**',
						'!**/*.md', '!**/*.yml', '!**/zip-cli.php',
						'!**/vendor/**',
						'!**/stubs.php', '!**phpstan.**',
						'!**/**.css.map', '!**/**.sh', '!grunt/**', '!Gruntfile.js',
						'!**/*.src.js', '!Pipfile*', '!tasks.py', '!Vagrantfile',
						'vendor/authorize*/**',
						'vendor/autoload.php', 'vendor/composer/**'
				]
			} );

		grunt.config.set( 'replace.setversion-' + id, {
			src: [
				basedir + '/' + prefix + id + '.php',
				basedir + '/' + prefix + id + '-module.php',
				// Replace - with _
				basedir + '/' + ( prefix + id ).replace('-', '_') + '_module.php',
				basedir + '/' + id + '.php',
				basedir + '/includes/class-' + id + '.php'
			],
			overwrite: true,
			replacements: [
				{
					from: /Version:(\s)*(\d+\.)(\d+\.)?(\*|\d+)?([\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?(\b)?/g,
					to: 'Version: <%= compress.version %>'
				},
				{
					from: /\$awpcp_db_version(\s)*= \'(\d+\.)(\d+\.)?(\*|\d+)?([\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\'/g,
					to: '$awpcp_db_version = \'<%= compress.version %>\''
				},
				{
					from: /(\b)*\$this\-\>version(\s)*= \'(\d+\.)(\d+\.)?(\*|\d+)?([\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\'/g,
					to: '$this->version = \'<%= compress.version %>\''
				},
				{
					from: /\"version\"\:(\s)* \"(\d+\.)(\d+\.)?(\*|\d+)?([\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\"/g,
					to: '"version": "<%= compress.version %>"'
				},
				{
					from: /\$version(\s)*\= \'(\d+\.)(\d+\.)?(\*|\d+)?([\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\'/g,
					to: '$version = \'<%= compress.version %>\''
				},
				{
					from: /define\( \'AWPCP_VERSION\', \'(\d+\.)(\d+\.)?(\*|\d+)?([\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\'/g,
					to: 'define( \'AWPCP_VERSION\', \'<%= compress.version %>\''
				},
				{
					from: /define\( \'AWPCP_AUTHORIZE_NET_MODULE_DB_VERSION\', \'(\d+\.)(\d+\.)?(\*|\d+)?([\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\'/g,
					to: 'define( \'AWPCP_AUTHORIZE_NET_MODULE_DB_VERSION\', \'<%= compress.version %>\''
				},
				{
					from: new RegExp("define\\( '" + setVersion + "', '(\\d+\\.)(\\d+\\.)?(\\*|\\d+)?([\\da-z-A-Z-]+(?:\\.[\\da-z-A-Z-]+)*)?'", "g"),
					to: 'define( \'' + setVersion + '\', \'<%= compress.version %>\''
				}
			]
		});

		grunt.config.set( 'replace.stabletag-' + id, {
			src: [
				basedir +'/README.txt',
				basedir + '/README.TXT'
			],
			overwrite: true,
			replacements: [
				{
					from: /Stable tag:\ .*/g,
					to: 'Stable tag: <%= compress.version %>'
				}
			]
		});

		grunt.config.set( 'replace.comment-' + id, {
			src: [
				basedir + '/*.php',
				basedir + '/**/*.php',
				basedir + '/**/*.js',
				'!' + basedir + '/**/*.min.js',
				'!' + basedir + '/Gruntfile.js',
				basedir + '/!node_modules/**',
				basedir + '/!vendor/**',
				basedir + '/!vendors/**',
				basedir + '/!translations/**',
				basedir + '/!languages/**',
				basedir + '/!tests/**'
			],
			overwrite: true,
			replacements: [
				{
					from: 'since x.x',
					to: 'since <%= compress.version %>'
				},
				{
					from: 'deprecated x.x',
					to: 'deprecated <%= compress.version %>'
				}
			]
		});
		}
	};

	var config = {
		pkg: grunt.file.readJSON('package.json'),
		less: {
		},
		uglify: {
		},
	replace: {},
		compress: {
		}
	};

	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-potomo');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-text-replace');

	grunt.initConfig( config );

	grunt.registerTask('default', []);
	grunt.registerTask('i18n', '', function(t) {
		grunt.task.run('makepot:' + t);
		grunt.task.run('potomo:' + t);
	});
	grunt.registerTask('minify', '', function(t) {
		// Release everything.
		if ( 'all' === t ) {
			Object.keys(grunt.wpbdp.registered).forEach(function(i) {
				grunt.task.run('minify:' + i);
			});

			return;
		}

		if ( 'undefined' !== typeof grunt.config.get( 'less.' + t ) ) {
			grunt.task.run('less:' + t);
		}

		if ( 'undefined' !== typeof grunt.config.get( 'uglify.' + t ) ) {
			grunt.task.run('uglify:' + t);
		}
	});

	grunt.registerTask('setversion', function(t, v){
		grunt.config.set('compress.version', v);

		grunt.task.run('replace:setversion-' + t );

		if ( ! v.includes('b') ) {
			// Is stable version.
			grunt.task.run('replace:stabletag-' + t );
			grunt.task.run('replace:comment-' + t );
		}
	});

	grunt.registerTask('release', function(t, v) {
		// Release everything.
		if ( 'all' === t || 'undefined' === typeof t ) {
			Object.keys(grunt.wpbdp.registered).forEach(function(i) {
				grunt.task.run('release:' + i);
			});

			return;
		}

		if ( t === 'core' ) {
			t = 'awpcp';
		}

		if ( 'undefined' === typeof grunt.config.get( 'compress.' + t ) ) {
			return;
		}

		grunt.config.set('compress.version', v);

		grunt.task.run('setversion:' + t + ':' + v );
		grunt.task.run('minify:' + t);
		grunt.task.run('i18n:' + t);
		grunt.task.run('compress:' + t );
	});

	// Core.
	grunt.wpbdp.registerModule({
		path: '../another-wordpress-classifieds-plugin',
		// TODO: update me (see grunt/grunt.js)
		less: [],
		js: [
			'resources/js/**/*.js',
			'!' + path + '/js/**/*.src.js',
			'!' + path + '/js/**/*.min.js'
		],
		i18n: {textDomain: 'another-wordpress-classifieds-plugin', domainPath: 'languages/'}
	});

	// Premium modules.
	grunt.wpbdp.registerModule({path: '../awpcp-extra-fields', js: [], i18n: true});
	grunt.wpbdp.registerModule({path: '../awpcp-stripe', js: [], i18n: true});
};
