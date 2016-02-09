module.exports = function(grunt) {

  require('load-grunt-tasks')(grunt);

  var pkg = grunt.file.readJSON('package.json'),
      mode = typeof pkg.folders[grunt.option('mode')] != 'undefined' ? grunt.option('mode') : Object.keys(pkg.folders)[0];

  // set theme and folder
  if(typeof pkg.theme == 'undefined') {
    return;
  }

  // setting browser compatibility
  if(typeof(pkg.supportedBrowsers) == 'undefined') {
    pkg.supportedBrowsers = ['> 5% in DE', 'ie 10'];
  }

  // Project configuration.
  grunt.initConfig({
    pkg: pkg,

    // Configure the less compilation for both dev and prod
    less: {
      options: {
        compress: false,
        yuicompress: false,
        cleancss: false,
        modifyVars : {
          'namespace' : pkg.prefix || '',
          'scope' : pkg.prefix || 'html'
        }
      },
      styles : {
        files: {
          'assets/css/styles.css' : ['src/less/styles.less']
        }
      }      
    },

    autoprefixer: {
      options: {
        browsers: pkg.supportedBrowsers || ['> 5% in DE', 'ie 10']
      },
      build: {
        expand: true,
        cwd: "assets",
        src: ['css/*.css'],
        dest: "assets"
      }
    },

    cssmin: {
      options: {
          shorthandCompacting: false,
          roundingPrecision: -1,
          // rebase: false,
          // restructuring: false,
          // keepBreaks: true,
          compatibility : '+properties.urlQuotes'
        },
        target: {
          files: {
            'assets/css/styles.css' : 'assets/css/styles.css'
          }
        }
    },

    shell: {
      assets : {
        command: 'rsync -rlt ./assets/ ../../../../assets/addons/' + pkg.theme + '/'
      }
    },

    concat: {
      options: {
        separator: ';\n',
        // stripBanners: true;
          process: function(src, filepath) {
            return '\n\n/*************************************************************\n  * ' + filepath + '\n  ************************************************************/\n' + src + '\n';
          }
      },

      default: {
        files: {
          'assets/js/scripts.js' : ['src/js/**/*.js']
        }
      }
    },

    uglify: {
      options: {
        compress: {
          drop_console: true
        }
      },
      files: { 
        src: 'assets/js/*.js',             // source files
        dest: 'assets/js/',      // destination folder
        expand: true,         // allow dynamic building
        flatten: true
      }
    },

    replace: {
      css : {
        src: 'assets/css/*.css',             // source files
        overwrite: true,
        replacements: [
          {
            from: /(url\(.*|filter.*)?(\.|\[class[\$\*\^]?=" ?)([a-zA-Z\-\_][a-zA-Z0-9\-\_]+)/g,
            to: function (matchedWord, index, fullText, regexMatches) {
              if(regexMatches[0] == undefined) {
                return regexMatches[1] + "<%= pkg.prefix %>" + regexMatches[2];
              }
              else {
                return matchedWord;
              }
            }
          }
        ]
      },

      js: {
        src: [],                          // source files mask
        overwrite: true,                 // overwrite matched source files
          replacements: [{
          from: /<%= prefix %>/g,
          to:  "<%= pkg.prefix %>"
        },
        {
          from: /<%= jsnamespace %>/g,
          to:  "<%= pkg.theme %>js"
        },
        {
          from: /<%= theme %>/g,
          to:  "<%= pkg.theme %>"
        },
        {
          from: 'Modernizr',
          to: '<%= pkg.prefix %>Modernizr'
        },
        {
          from: 'noUi-',
          to: '<%= pkg.prefix %>noUi-'
        }]
      }
    },

    watch: {
      grunt: { files: ['Gruntfile.js'] },

      less: {
        files: ['src/less/**/*.less'], // which files to watch
        tasks: ['less', 'autoprefixer', 'replace:css'],
        options: {
          nospawn: true
        }
      },

      js: {
        files: ['src/js/**/*.js'],
        tasks: ['concat', 'replace:js']
      },

      shell: {
        files: ['assets/**/*'],
        tasks: ['shell:assets', 'watch']
      }
    }
  });

  var tasks = ['less', 'autoprefixer'];

  tasks = tasks.concat(['concat', 'replace']);

  if(mode != 'dev') {
    tasks.push('cssmin', 'uglify');
  }

  if(grunt.config.get('shell') &&  Object.keys(grunt.config.get('shell')).length) {
    tasks = tasks.concat(['shell']);
  }

  if(mode == 'dev') {
    tasks.push('watch');
  }

  grunt.registerTask('default','Grunting', tasks);
};