module.exports = function(grunt) {
   // Project configuration.
   grunt.initConfig({
      pkg: grunt.file.readJSON('package.json'),

      scratch: {
         folders: {
            fonts: './fonts',
            bower: './bower_components/',
            css: {
               dist: './stylesheets/',
            },
            sass: {
               src: './stylesheets/sass/',
               contrib: './stylesheets/sass/contrib',
            }
         }
      },
      bower: {
         install: {
            options: {
               targetDir: '<%= scratch.folders.bower %>',
               install: true,
               copy: false
            }
         }
      },
      copy: {
         gridle: {
            files: [{
               cwd: '<%= scratch.folders.bower %>gridle/sass/',
               src: '**',
               dest: 'stylesheets/sass/contrib/gridle/',
               expand: true,
            }]
         },
         normalize: {
            src: '<%= scratch.folders.bower %>/normalize.css/normalize.css',
            dest: '<%= scratch.folders.sass.contrib %>/normalize/_normalize.scss'
         },
         fontawesome: {
            files: [
               {
                  cwd: '<%= scratch.folders.bower %>/fontawesome/scss/',
                  src: '*',
                  dest: '<%= scratch.folders.sass.contrib %>/fontawesome',
                  expand: true,
               },
               {
                  cwd: '<%= scratch.folders.bower %>/fontawesome/fonts/',
                  src: '*',
                  dest: '<%= scratch.folders.fonts %>',
                  expand: true,
               }
            ]
         },
         bourbon: {
            files: [{
               cwd: '<%= scratch.folders.bower %>bourbon/dist/',
               src: '**',
               dest: '<%= scratch.folders.sass.contrib %>/bourbon/',
               expand: true,
            }]
         },
      },
      sass: {
         dist: {
            files: [{
               expand: true,
               cwd: '<%= scratch.folders.sass.src %>',
               src: ['*.scss'],
               dest: '<%= scratch.folders.css.dist %>',
               ext: '.css'
            }]
         }
      },
      watch: {
         css: {
            files: [ '<%= scratch.folders.sass.src %>/**/*.scss' ],
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
         css: ['<%= scratch.folders.css.dist %>/*.css'],
         sasscontrib: [ '<%= scratch.folders.sass.contrib %>' ]
      }
   });

   grunt.loadNpmTasks('grunt-bower-task');
   grunt.loadNpmTasks('grunt-contrib-copy');
   grunt.loadNpmTasks('grunt-contrib-sass');
   grunt.loadNpmTasks('grunt-contrib-watch');
   grunt.loadNpmTasks('grunt-contrib-clean');

   grunt.registerTask('css', ['clean:css', 'sass'])
   grunt.registerTask('dependencies', ['bower', 'clean:sasscontrib', 'copy'])
   //The default task
   grunt.registerTask('default', ['dependencies', 'css']);
};