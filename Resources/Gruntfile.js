module.exports = function(grunt) {
   grunt.initConfig({
      pkg: grunt.file.readJSON('package.json'),

      chill: {
         folders: {
            pub: './public',
            fonts: '<%= chill.folders.pub %>/fonts',
            bower: './bower_components/',
            css: '<%= chill.folders.pub %>/css/',
            js: '<%= chill.folders.pub %>/js/',
            sass: '<%= chill.folders.css %>/sass/',
         }
      },
      bower: {
         install: {
            options: {
               targetDir: '<%= chill.folders.bower %>',
               install: true,
               copy: false,
               //cleanBowerDir: true,
               verbose: true
            }
         }
      },
      copy: {
         scratch: {
            files: [
               {
                  cwd: '<%= chill.folders.bower %>Scratch-CSS-Design/stylesheets/sass',
                  src: ['**', '!_custom.scss'],
                  dest: '<%= chill.folders.sass %>',
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
         select2: {
            files: [
               {
                  cwd: '<%= chill.folders.bower %>select2',
                  src: ['*.js'],
                  dest: '<%= chill.folders.js %>select2',
                  expand: true,
               },
               {
                  cwd: '<%= chill.folders.bower %>select2',
                  src: ['*.css', 'select2.png'],
                  dest: '<%= chill.folders.css %>select2',
                  expand: true,
               }
            ]
         },
         pikaday: {
            files: [
               {
                  cwd: '<%= chill.folders.bower %>pikaday/css',
                  src: ['pikaday.css'],
                  dest: '<%= chill.folders.css %>',
                  expand: true,
               },
               {
                  cwd: '<%= chill.folders.bower %>pikaday',
                  src: ['pikaday.js',  'plugins/pikaday.jquery.js'],
                  dest: '<%= chill.folders.js %>pikaday',
                  expand: true,
               }
            ]
         },
         moment: {
            files: [
                {
                    cwd: '<%= chill.folders.bower %>moment',
                    src: ['moment.js'],
                    dest: '<%= chill.folders.js %>',
                    expand: true,
                }
            ]
         },
         chill_standard: { /* copy all files in chill standard (done by app/console assets:install) */
            files: [
               {
                  cwd: './public',
                  src: '**',
                  dest: '../../../../web/bundles/chillmain/',
                  expand: true,
               }
            ]
         },
         jquery: {
            src: '<%= chill.folders.bower %>jquery/dist/jquery.js',
            dest: '<%= chill.folders.js %>/jquery.js'
         }
      },
      sass: {
         dist: {
            options: {
               debugInfo: true,
            },
            files: [{
               expand: true,
               cwd: '<%= chill.folders.sass.src %>',
               src: ['*.scss'],
               dest: '<%= chill.folders.css %>',
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
         /*css: ['<%= chill.folders.css %>*',  '!<%= chill.folders.css %>sass/_custom.scss'], */
         js: ['<%= chill.folders.js %>/select2*', '<%= chill.folders.js %>/pikaday*', '<%= chill.folders.js %>/moment*', '<%= chill.folders.js %>/jquery*'],
         chill_standard: ['../../../../web/bundles/chillmain/'],
         bowerDir: ['<%= chill.folders.bower %>'] 
      }
   });

   grunt.loadNpmTasks('grunt-bower-task');
   grunt.loadNpmTasks('grunt-contrib-copy');
   grunt.loadNpmTasks('grunt-contrib-sass');
   grunt.loadNpmTasks('grunt-contrib-watch');
   grunt.loadNpmTasks('grunt-contrib-clean');

   grunt.registerTask('generatecss', [/*'clean:css',*/ 'copy:scratch', 'sass']);
   grunt.registerTask('dependencies', ['bower', 'copy']);
   grunt.registerTask('default', ['dependencies', 'generatecss']);
};