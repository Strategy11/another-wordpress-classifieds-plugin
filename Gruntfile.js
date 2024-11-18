/*global module: false, require: false*/
/*jslint indent: 2*/

const path = require('path');
const fs = require('fs');
const _ = require('underscore');
const prefix = 'awpcp-';

module.exports = function( grunt ) {
	grunt.config.set('compress.version', '');

	grunt.wpbdp = {
		registered: {},
		registerModule: function( config ) {
			var basedir = config.path;
			var id      = config.name || path.basename(basedir).replace(prefix, '');
			config.id   = id;
			config.version = 'AWPCP_' + id.replace( '-', '_' ).toUpperCase() + '_MODULE_DB_VERSION';

			this.registered[ id ] = config;

			config.pluginPath = config.folder + '/';

			grunt.config.set( 'path.' + id, config.pluginPath );
			grunt.wpbdp.registerSetVersionTasks( config );
			grunt.wpbdp.registerZipTasks( config );

			if ( config.less ) {
				grunt.wpbdp.registerLessTask( config );
			}

			if ( config.concat ) {
				grunt.wpbdp.registerJavaScriptTasks( config );
			}

			if ( config.i18n ) {
				grunt.wpbdp.registerMakePot( config );
			}
		},

		registerZipTasks: function( config ) {
			// Compress config.
			grunt.config.set( 'compress.' + config.id, {
				options: {
					archive: './../' + path.basename(config.folder) + '-<%= compress.version %>.zip',
					mode: 'zip'
				},
				expand: true,
				cwd: config.pluginPath,
				dest: path.basename(config.folder),
				src: [
					'**/*', '!**/*~', '!**/**.less', '!**/less/**', '!**/**.css.map',
					'!**/.*', '!phpcs.xml', '!composer.json', '!**/**.sh',
					'!composer.lock', '!grunt/**', '!Gruntfile.js',
					'!**/*.src.js', '!**/stubs.php', '!**/**.neon',
					'!node_modules/**', '!package.json', '!package-lock.json',
					'!phpunit.xml', '!Pipfile*', '!tasks.py',
					'!Vagrantfile', '!tests/**', '!bin/**', '!vendor/**',
					'vendor/authorize*/**',
					'vendor/autoload.php', 'vendor/composer/**'
				]
			} );
		},

		registerSetVersionTasks: function( config ) {
			grunt.wpbdp.registerStableTag( config );
			grunt.wpbdp.registerCommentVersion( config );

			grunt.config.set( 'replace.setversion-' + config.id, {
				src: [
					config.pluginPath + prefix + config.id + '.php',
					config.pluginPath + prefix + config.id + '-module.php',
					// Replace - with _
					config.pluginPath + ( prefix + config.id ).replace('-', '_') + '_module.php',
					config.pluginPath + config.slug + '.php',
					config.pluginPath + 'includes/class-' + config.id + '.php'
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
						from: /define\( \'AWPCP_STRIPE_MODULE_DB_VERSION\', \'(\d+\.)(\d+\.)?(\*|\d+)?([\da-z-A-Z-]+(?:\.[\da-z-A-Z-]+)*)?\'/g,
						to: 'define( \'AWPCP_STRIPE_MODULE_DB_VERSION\', \'<%= compress.version %>\''
					},
					{
						from: new RegExp("define\\( '" + config.version + "', '(\\d+\\.)(\\d+\\.)?(\\*|\\d+)?([\\da-z-A-Z-]+(?:\\.[\\da-z-A-Z-]+)*)?'", "g"),
						to: 'define( \'' + config.version + '\', \'<%= compress.version %>\''
					}
				]
			});
		},

		registerStableTag: function( config ) {
			grunt.config.set( 'replace.stabletag-' + config.name, {
				src: [
					config.pluginPath +'README.txt',
					config.pluginPath + 'README.TXT'
				],
				overwrite: true,
				replacements: [
					{
						from: /Stable tag:\ .*/g,
						to: 'Stable tag: <%= compress.version %>'
					}
				]
			});
		},

		registerCommentVersion: function( config ) {
			grunt.config.set( 'replace.comment-' + config.name, {
				src: [
					config.pluginPath + '*.php',
					config.pluginPath + '**/*.php',
					config.pluginPath + '**/*.js',
					'!' + config.pluginPath + '**/*.min.js',
					'!' + config.pluginPath + 'Gruntfile.js',
					config.pluginPath + '!node_modules/**',
					config.pluginPath + '!vendor/**',
					config.pluginPath + '!vendors/**',
					config.pluginPath + '!translations/**',
					config.pluginPath + '!languages/**',
					config.pluginPath + '!bin/**',
					config.pluginPath + '!tests/**'
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
		},

		registerMakePot: function( config ) {
			var textDomain = config.i18n.textDomain || prefix + config.name,
				basedir = config.pluginPath,
				domainPath = path.join( basedir, config.i18n.domainPath || 'translations/' ),
				makepot_config = {},
				potomo_config  = {};

			if ( ! fs.existsSync( domainPath ) ) {
				domainPath = path.join( basedir, config.i18n.domainPath || 'languages/' );
			}

			if ( fs.existsSync( domainPath ) ) {
				makepot_config = {
					options: {
						cwd: basedir,
						domainPath: domainPath.replace(basedir, ''),
						potFilename: textDomain + '.pot',
						exclude: ['vendors/.*', 'build/.*'],
						updatePoFiles: true
					}
				};
				potomo_config = {
					options: {
						poDel: false
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

			if ( makepot_config ) {
				grunt.config.set( 'makepot.' + config.name, makepot_config );
				grunt.config.set( 'potomo.' + config.name, potomo_config );
			}
		},

		registerLessTask: function( config ) {
			var folder = '<%= path.' + config.name + ' %>';

			grunt.config.set( 'less.' + config.slug, config.less );
			grunt.config.set( 'watch.' + config.name + '-css', {
				files: [ folder + 'resources/less/**/*.less' ],
				tasks: [ 'less:' + config.slug ]
			} );
		},

		registerJavaScriptTasks: function( config ) {
			const basedir = config.pluginPath

			grunt.config.set( 'concat.' + config.slug, config.concat );

			grunt.config.set( 'watch.' + config.name + '-js', {
				files: [
					path.join(basedir, '**/*.js'),
					'!' + path.join(basedir, 'vendors/**/*'),
					'!' + path.join(basedir, '**/*.src.js'),
					'!' + path.join(basedir, '**/*.min.js'),
					'!' + path.join(basedir, 'assets/vendor/**/*')
				],
				tasks: ['concat:' + config.slug, 'uglify:' + config.slug]
			} );

			let targetFiles = grunt.task.normalizeMultiTaskFiles( config.concat );

			grunt.wpbdp.registerJSHintTask( config, targetFiles );
			grunt.wpbdp.registerUglifyTask( config, targetFiles );
		},

		registerJSHintTask: function( config, targetFiles ) {
			const folder = '<%= path.' + config.name + ' %>';

			let filesToCheck = _.flatten( _.map( targetFiles, function( value ) {
				return value.orig.src;
			} ) );

			grunt.config.set( 'jshint.' + config.slug, filesToCheck.concat( ['!' + folder + '/js/**/*.min.js'] ) );
		},

		registerUglifyTask: function( config, targetFiles ) {
			_.each( targetFiles, function( value ) {
				var source = value.dest,
				target = source.replace( 'src', 'min' );

				grunt.config.set( 'uglify.' + config.slug + '.files.' + target.replace( /\./g, '\\.' ), value.orig.src );
			} );
		}
	};

	var config = {
		pkg: grunt.file.readJSON('package.json'),

		concat: {
			options: {
				separator: ';'
			}
		},

		jshint: {
			options: {
				es3: true,
				bitwise: true,
				curly: true,
				eqeqeq: true,
				forin: true,
				immed: true,
				sub: true,
				boss: true,
				eqnull: true,
				indent: 4,
				latedef: 'nofunc',
				newcap: true,
				noarg: true,
				noempty: true,
				nonew: true,
				plusplus: true,
				quotmark: true,
				regexp: true,
				undef: true,
				unused: true,
				trailing: true,
				// relaxing options
				evil: false,
				regexdash: true,
				white: false,
				// environments
				browser: true,
				force: true,
				jquery: true
			},
			project: [ 'Gruntfile.js' ]
		},

		uglify: {
			options: {
				report: 'gzip'
			}
		},

		less: {
			options: {
				cleancss: false,
				compress: true,
				strictImports: true
			}
		},

		clean: ['<%= path.awpcp %>/js/awpcp.src.js']
	};

	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-wp-i18n');
	grunt.loadNpmTasks('grunt-potomo');
	grunt.loadNpmTasks('grunt-contrib-compress');
	grunt.loadNpmTasks('grunt-text-replace');

	grunt.initConfig( config );

	grunt.loadTasks( 'grunt' );

	/*
	grunt.loadTasks( '../awpcp-buddypress-listings/grunt' );
	grunt.loadTasks( '../awpcp-mark-as-sold/grunt' );
	grunt.loadTasks( '../awpcp-campaign-manager/grunt' );
	grunt.loadTasks( '../awpcp-category-icons/grunt' );
	grunt.loadTasks( '../awpcp-comments-ratings/grunt' );
	grunt.loadTasks( '../awpcp-fee-per-category/grunt' );
	grunt.loadTasks( '../awpcp-region-control/grunt' );
	grunt.loadTasks( '../awpcp-restricted-categories/grunt' );
	grunt.loadTasks( '../awpcp-videos/grunt' );
	grunt.loadTasks( '../awpcp-zip-code-search/grunt' );
	*/

	grunt.registerTask('default', ['watch', 'concat', 'jshint', 'uglify', 'less']);

	grunt.registerTask('i18n', '', function(t) {
		grunt.task.run('makepot:' + t);
		grunt.task.run('potomo:' + t);
	});
	grunt.registerTask( 'minify', '', function(t) {
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

		t = t.replace( '.', '-' );
		if ( 'undefined' === typeof grunt.config.get( 'compress.' + t ) ) {
			return;
		}

		grunt.config.set('compress.version', v);

		grunt.task.run('setversion:' + t + ':' + v );
		if ( 'undefined' !== typeof grunt.config.get( 'concat.' + t ) ) {
			grunt.task.run('concat:' + t);
		}
		grunt.task.run('minify:' + t);
		grunt.task.run('i18n:' + t);
		grunt.task.run('compress:' + t );
	});

};
