/*global module: false, require: false*/
/*jslint indent: 2*/

var _ = require('underscore');

module.exports = function( grunt ) {
  grunt.awpcp = {
    registerPluginTasks: function( config ) {
      grunt.config.set( 'path.' + config.name, config.path );

      if ( config.less ) {
        grunt.awpcp.registerLessTask( config );
      }

      if ( config.concat ) {
        grunt.awpcp.registerJavaScriptTasks( config );
      }
    },

    registerLessTask: function( config ) {
      var path = '<%= path.' + config.name + ' %>';

      grunt.config.set( 'less.' + config.slug, config.less );
      grunt.config.set( 'watch.' + config.name + '-css', {
        files: [ path + '/less/**/*.less' ],
        tasks: [ 'less:' + config.slug ]
      } );
    },

    registerJavaScriptTasks: function( config ) {
      var path = '<%= path.' + config.name + ' %>', targetFiles;

      grunt.config.set( 'concat.' + config.slug, config.concat );

      grunt.config.set( 'watch.' + config.name + '-js', {
        files: [path + '/js/**/*.js', '!' + path + '/js/**/*.src.js', '!' + path + '/js/**/*.min.js'],
        tasks: ['concat:' + config.slug, 'uglify:' + config.slug]
      } );

      targetFiles = grunt.task.normalizeMultiTaskFiles( config.concat );

      grunt.awpcp.registerJSHintTask( config, targetFiles );
      grunt.awpcp.registerUglifyTask( config, targetFiles );
    },

    registerJSHintTask: function( config, targetFiles ) {
      var path = '<%= path.' + config.name + ' %>', filesToCheck;

      filesToCheck = _.flatten( _.map( targetFiles, function( value ) {
        return value.orig.src;
      } ) );

      grunt.config.set( 'jshint.' + config.slug, filesToCheck.concat( ['!' + path + '/js/**/*.min.js'] ) );
    },

    registerUglifyTask: function( config, targetFiles ) {
      _.each( targetFiles, function( value ) {
        var source = value.dest,
            target = source.replace( 'src', 'min' );

        grunt.config.set( 'uglify.' + config.slug + '.files.' + target.replace( /\./g, '\\.' ), source );
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

  grunt.initConfig( config );

  grunt.loadTasks( 'grunt' );

  grunt.registerTask('default', ['concat', 'jshint', 'uglify', 'less']);
};
