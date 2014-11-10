module.exports = function(grunt) {
   grunt.initConfig({
      pkg: grunt.file.readJSON('package.json'),

      chill: {
         folders: {
            fonts: './public/fonts',
            bower: './bower_components/',
            css: {
               dist: './public/stylesheets/',
            },
            sass: {
               src: './public/stylesheets/sass/',
            }
         }
      },
      bower: {
         install: {
            options: {
               targetDir: '<%= chill.folders.bower %>',
               install: true,
               copy: false
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
            files: [ '<%= chill.folders.sass.src %>/** /*.scss' ],
            tasks: ['css'],
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

   grunt.registerTask('generatecss', ['clean:css', 'sass']);
   grunt.registerTask('dependencies', ['bower', 'copy']);
   grunt.registerTask('default', ['dependencies', 'generatecss']);
};