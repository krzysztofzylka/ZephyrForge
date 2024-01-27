module.exports = function(grunt) {

    grunt.initConfig({
        concat: {
            js: {
                src: [
                    'node_modules/jquery/dist/jquery.min.js',
                    'js/*.js'
                ],
                dest: 'storage/tmp/app.js',
            },
            css: {
                src: ['css/*.css'],
                dest: 'public/app.css',
            },
        },
        uglify: {
            js: {
                files: {
                    'public/app.js': ['storage/tmp/app.js']
                }
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');

    grunt.registerTask('default', ['concat', 'uglify']);
};