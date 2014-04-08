module.exports = function(grunt) {
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-less');
	grunt.loadNpmTasks('grunt-contrib-jshint');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');

	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),

		concat: {
			options: {
				separator: ';'
			},
			vendor: {
				src: [
					'www/static/js/vendor/jquery.markitup.js',
					'www/static/js/vendor/markitup.bbcode.js',
					'www/static/js/vendor/jquery.cookie.js'
				],
				dest: 'www/static/js/c/vendor.js'
			},
			anqh: {
				src: [
					'www/static/js/anqh/anqh.js',
					'www/static/js/anqh/jquery.form.js',
					'www/static/js/anqh/jquery.googlemap.js',
					'www/static/js/anqh/jquery.dialogify.js',
					'www/static/js/anqh/jquery.ajaxify.js',
					'www/static/js/anqh/jquery.autocomplete.event.js',
					'www/static/js/anqh/jquery.autocomplete.geo.js',
					'www/static/js/anqh/jquery.autocomplete.user.js',
					'www/static/js/anqh/jquery.autocomplete.venue.js',
					'www/static/js/anqh/jquery.notes.js'
				],
				dest: 'www/static/js/c/anqh.js'
			}
		},

		jshint: {
			anqh: [ 'www/static/js/anqh/*.js' ]
		},

		less: {
			options: {
				cleancss: true,
				report:   'min'
			},
			anqh: {
				files: {
					'www/static/css/anqh.css': 'www/static/css/anqh.less'
				}
			}
		},

		uglify: {
			options: {
				mangle: false
			},
			vendor: {
				files: {
					'www/static/js/c/vendor.min.js': 'www/static/js/c/vendor.js'
				}
			},
			anqh: {
				files: {
					'www/static/js/c/anqh.min.js': 'www/static/js/c/anqh.js'
				}
			}
		},

		watch: {
			css: {
				files: [ 'www/static/css/*.less' ],
				tasks: [ 'less' ]
			},
			js: {
				files: [ '<%= concat.vendor.src %>', '<%= concat.anqh.src %>' ],
				tasks: [ 'js' ]
			}
		}

	});

	grunt.registerTask('js', [ 'concat', 'uglify' ]);
	grunt.registerTask('css', [ 'less' ]);
	grunt.registerTask('default', [ 'js', 'css' ]);
};
