module.exports = function(grunt) {

    grunt.initConfig({
        concat: {
            js: {
                src: [
                    'node_modules/jquery/dist/jquery.min.js',
                    'node_modules/jquery-ui-dist/jquery-ui.min.js',
                    'src/public/js/*.js'
                ],
                dest: 'storage/tmp/app.js',
            },
            css: {
                src: [
                    'node_modules/jquery-ui-dist/jquery-ui.min.css',
                    'src/public/css/*.css'
                ],
                dest: 'storage/tmp/app.css',
            },
        },
        uglify: {
            js: {
                files: {
                    'public/app.js': ['storage/tmp/app.js']
                }
            }
        },
        cssmin: {
            options: {
                level: {
                    1: {
                        specialComments: 0
                    }
                }
            },
            target: {
                files: [{
                    src: 'storage/tmp/app.css',
                    dest: 'public/app.css',
                }]
            }
        }
    });

    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-concat');
    grunt.loadNpmTasks('grunt-contrib-cssmin');

    grunt.registerTask('default', ['concat', 'uglify', 'cssmin']);
};