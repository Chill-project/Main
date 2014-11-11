module.exports = function(grunt) {
   grunt.initConfig({
      pkg: grunt.file.readJSON('package.json'),

      chill: {
         folders: {
            pub: './public',
            fonts: '<%= chill.folders.pub %>/fonts',
            bower: './bower_components/',
            css: {
               dist: '<%= chill.folders.pub %>/stylesheets/',
            },
            sass: {
               src: '<%= chill.folders.css.dist %>/sass/',
            }
         }
      },
      bower: {
         install: {
            options: {
               targetDir: '<%= chill.folders.bower %>',
               install: true,
               copy: false,
               cleanBowerDir: true,
               verbose: true
            }
         }
      },
      copy: {
         scratch: {
            files: [
               {
                  cwd: '<%= chill.folders.bower %>Scratch-CSS-Design/stylesheets/sass',
                  src: ['*', '**',  '!_custom.scss'],
                  dest: '<%= chill.folders.sass.src %>',
                  expand: true,
               },
               {
                  cwd: '<%= chill.folders.bower %>Scratch-CSS-Design/fonts/',
                  src: '**',
                  dest: '<%= chill.folders.fonts %>',
                  expand: true,
               }
            ]
         },
         pikaday: {
            files: [
                {
                    cwd: '<%= chill.folders.bower %>pikaday',
                    src: ['css/pikaday.css', 'pikaday.js',  'plugins/pikaday.jquery.js'],
                    dest: '<%= chill.folders.pub %>',
                    expand: true,
                }
            ]
         },
         moment: {
            files: [
                {
                    cwd: '<%= chill.folders.bower %>moment',
                    src: ['moment.js'],
                    dest: '<%= chill.folders.pub %>',
                    expand: true,
                }
            ]
         },
         chill_standard: {
            files: [
               {
                  cwd: './public',
                  src: '**',
                  dest: '../../../../web/bundles/chillmain/',
                  expand: true,
               }
            ]
         }
      },
      sass: {
         dist: {
            files: [{
               expand: true,
               cwd: '<%= chill.folders.sass.src %>',
               src: ['*.scss'],
               dest: '<%= chill.folders.css.dist %>',
               ext: '.css'
            }]
         }
      },
      watch: {
         css: {
            files: [ '<%= chill.folders.sass.src %>/*.scss', '<%= chill.folders.sass.src %>/**/*.scss' ],
            tasks: ['generatecss'],
            /*
            options: {
               spawn: false,
               interrupt: true,
            }
            */
         }
      },
      clean: {
         css: ['<%= chill.folders.css.dist %>/*.css'],
      }
   });

   grunt.loadNpmTasks('grunt-bower-task');
   grunt.loadNpmTasks('grunt-contrib-copy');
   grunt.loadNpmTasks('grunt-contrib-sass');
   grunt.loadNpmTasks('grunt-contrib-watch');
   grunt.loadNpmTasks('grunt-contrib-clean');

   grunt.registerTask('generatecss', ['clean:css', 'sass', 'copy:chill_standard']);
   grunt.registerTask('dependencies', ['bower', 'copy']);
   grunt.registerTask('default', ['dependencies', 'generatecss']);
};