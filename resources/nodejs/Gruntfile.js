const spawn = require('child_process').spawn;
module.exports = function(grunt) {
    grunt.initConfig({
        lambda_invoke: {
            default: {
                options: {
                    file_name: 'gateway.js'
                }
            }
        },
    });

    grunt.loadNpmTasks('grunt-aws-lambda');

};