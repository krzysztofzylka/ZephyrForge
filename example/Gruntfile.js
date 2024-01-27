module.exports = function(grunt) {

    grunt.initConfig({
        concat: {
            js: {
                src: ['js/*.js'],
                dest: 'public/app.js',
            },
            css: {
                src: ['css/*.css'],
                dest: 'public/app.css',
            },
        }
    });

    grunt.loadNpmTasks('grunt-contrib-concat');

    grunt.registerTask('default', ['concat']);
};