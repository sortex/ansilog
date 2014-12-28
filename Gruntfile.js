module.exports = function(grunt) {

	// Portable Unix shell commands for Node.js
	// See: https://github.com/arturadib/shelljs
	var shell = require('shelljs'),
		fatal = grunt.fail.fatal;

	// Add any new plugins you use here:
	grunt.loadNpmTasks('grunt-contrib-clean');
	grunt.loadNpmTasks('grunt-contrib-concat');
	grunt.loadNpmTasks('grunt-contrib-copy');
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');

	// Registering our plugin settings
	grunt.initConfig(plugins());

	// -------------------------------------------------------
	// TASKS
	// -------------------------------------------------------

	/**
	 * DEFAULT
	 * Compile stylesheets for all apps and admin
	 */
	grunt.registerTask('default', 'Compile admin styles and all sites', function(environment) {
		environment = environment || 'development';

		// Compiles admin's LESS stylesheet
//		grunt.task.run(['less_admin']);

		grunt.task.run('less:'+environment);
	});

	/**
	 * INSTALL
	 * The task to install composer and browser packages
	 */
	grunt.registerTask('install', 'Install project\'s dependencies', function () {
		var cmds = [
			'composer install',
			'bower update'
		];

		cmds.forEach(function (cmd) {
			if (shell.exec(cmd).code !== 0)
				fatal('Error while running: '+cmd);
		});

		// Copy, concat, and clean
		grunt.task.run([ 'copy:update', 'concat', 'clean' ]);
	});

	/**
	 * BUILD
	 * Build this thing for production/staging deployment:
	 * composer install, requirejs optimize, uglify admin js,
	 * compile admin css, uglify theme js
	 */
	grunt.registerTask('build', 'Prepares a new deployed build', function(environment) {
		environment = environment || 'development';

		grunt.task.run([
			'install',
			'less:'+environment
//			'requirejs',
//			'uglify:admin',
//			'less_admin',
//			'uglify:themes',
//			'copy:uglified_themes'
		]);
	});

	/**
	 * LESS: Compile sites' stylesheets
	 * Copies an App's compiled CSS into:
	 *  - Development: srv/http/media/dev/css
	 *  - Others: app/media/css
	 */
	grunt.registerTask('less', 'Compiles LESS styles', function(environment) {
		environment = environment || 'development';
		var src = 'var/cache/build/media/less/site',
			dests = {
				development: 'srv/http/media/dev/css/site',
				production: 'app/media/css/site'
			},
			dest = dests[environment],
			opts = environment == 'development' ? '' : '-x';

		if ( ! grunt.file.isDir(dest)) {
			grunt.file.mkdir(dest);
		}

		var cmds = [
			'bin/minion media:build --pattern=site',
			'$(npm bin)/lessc '+opts+' '+src+'/style.less '+dest+'/style.css'
		];

		cmds.forEach(function (cmd) {
			if (shell.exec(cmd).code !== 0)
				fatal('Error while running: '+cmd);
		});
	});

	/**
	 * LESS: Compile admin's stylesheets
	 */
	grunt.registerTask('less_admin', 'Compiles admin styles', function() {
		var src = 'var/cache/_admin/media/less/admin',
			dest = 'src/Ansilog/media/css/admin',
			cmds = [
				'bin/minion media:build --pattern=admin',
				'$(npm bin)/lessc -x '+src+'/style.less '+dest+'/style.css'
			];

		if ( ! grunt.file.isDir(dest)) {
			grunt.file.mkdir(dest);
		}

		cmds.forEach(function (cmd) {
			if (shell.exec(cmd).code !== 0)
				fatal('Error while running: '+cmd);
		});
	});

	/**
	 * REQUIREJS
	 * Builds and compiles requirejs modules
	 */
	grunt.registerTask('requirejs', 'Optimize Javascripts with RequireJS', function () {
		var src = 'var/cache/requirejs',
			pwd = shell.pwd(),
			done = this.async(),
			build = grunt.file.readJSON('build.js'),
			requirejs = require('requirejs'),
			controllers,
			entities,
			named = [],
			main;

		if (shell.exec('bin/minion _kala media:build --pattern=requirejs').code !== 0)
			fatal('Error while running minion');

		if (shell.exec('cp build.js '+src+'/').code !== 0)
			fatal('Error while copying build.js');

		// Find the 'main' module, and collect
		// all the already-named modules for later exclusion.
		build.modules.forEach(function (module) {
			named.push(module.name);
			if (module.name == 'main') {
				main = module;
			}
		});

		// Discover controllers
		controllers = shell.find(src+'/media/js/apps')
			.filter(function (file) {
				return file.match(/_controller(.*)\.js$/);
			});

		if (shell.error()) fatal(shell.error());

		// Prepare and append each discovered controller as a module to be
		// optimized. Also makes sure it isn't duplicating any module.
		controllers.forEach(function (file) {
			file = file.replace(src+'/media/js/', '').replace('.js', '');
			build.modules.forEach(function (module) {
				if (module.name == file) {
					file = false;
					return false;
				}
			});
			// Append the new module to the build.js, and exclude
			// other modules/packages.
			if (file) {
				build.modules.push({
					'name': file,
					'excludeShallow': [ 'jqueryui' ],
					'exclude': named
				});
			}
		});

		// Discover entities, excluding _base
		entities = shell.find(src+'/media/js/entities')
			.filter(function (file) {
				return file.match(/entities\/(?!\_base\/).*\.js$/);
			});

		if (shell.error()) fatal(shell.error());

		// Prepare and append each discovered entity to the 'main' module
		entities.forEach(function (file) {
			file = file.replace(src+'/media/js/', '').replace('.js', '');
			main.include.forEach(function (module_name) {
				if (module_name == file) {
					file = false;
					return false;
				}
			});
			// Append the new entity to the 'main' module
			if (file) {
				main.include.push(file);
			}
		});

		// Change working directory and run RequireJS's optimizer
		shell.cd(src);
		requirejs.optimize(build, function () {
			shell.cd(pwd);
			grunt.log.ok('RequireJS build complete!');
			done();
		}, function (err) {
			fatal('Error while optimizing with RequireJS: '+err);
		});
	});

	/**
	 * WATCH_TRIGGER
	 * Compile styles for a specific App
	 */
	grunt.registerTask('watch_trigger', 'Watches and compiles LESS file changes', function() {
		// Watch event: Should we compile admin styles instead of app?
		var filepath = grunt.option('filechanged');
		if (filepath && filepath.match(/^src\//)) {
			grunt.task.run('less_admin');
		} else {
			grunt.task.run('less');
		}
	});

	/**
	 * Watch event: Store the changed file path for task usage
	 */
	grunt.event.on('watch', function(action, filepath) {
		grunt.option('filechanged', filepath);
	});

	// -------------------------------------------------------
	// PLUGINS' TASK SETTINGS
	// -------------------------------------------------------
	function plugins() {
		return {

		/**
		* OUR NPM DEPENDENCIES
		*/
		pkg: grunt.file.readJSON('package.json'),

		/**
			* UGLIFY
			* Uglify (minimize, compress and mangle) javascript files
			* Keep jQuery and Backone unmangled, meaning it won't change their
			* variable names.
			*
			* EXCLUDES already minimized libraries, like bootstrap.
			*
			* https://www.npmjs.org/package/grunt-contrib-uglify
			*/
		uglify: {
			options: {
				mangle: {
					except: [ 'jQuery', 'Backbone' ]
				}
			},
			admin: {
				files: [{
					cwd: 'apps/_kala/cache/media/compiled',
					src: [
						'**/*.js',
						'!vendor/bootstrap/bootstrap.min.js',
						'!vendor/jquery/*.js',
						'!vendor/jquery.cycle2/*.js',
						'!vendor/jquery.touchswipe/*.js',
						'!vendor/less/*.js',
						'!js/vendor/*.min.js'
					],
					dest: 'src/core/media',
					expand: true,
					filter: 'isFile'
				}]
			},
			themes: {
				files: [{
					cwd: 'themes/',
					src: [ '*/media/js/**/*.js', '!**/*.min.js' ],
					dest: 'apps/_kala/cache/media/themes',
					expand: true,
					filter: 'isFile'
				}]
			}
		},

		/**
		* WATCH
		* Patterns to watch and trigger
		*
		* https://www.npmjs.org/package/grunt-contrib-watch
		*/
		watch: {
			apps: {
				options: {
					spawn: false
				},
				tasks: [ 'watch_trigger' ],
				files: [
					'srv/http/*/media/**/*.less',
					'src/*/media/**/*.less'
				]
			}
		},

		/**
		* COPY
		*
		* https://npmjs.org/package/grunt-contrib-copy
		*/
		copy: {
			uglified_themes: {
				files: [
					{ cwd: 'apps/_themes/cache/media/themes',
						src: '**',
						dest: 'themes/',
						expand: true
					}
				]
			},
			update: {
				files: [
					{ cwd: 'app/media/vendor/backbone.babysitter/lib/',
						src: [ 'backbone.babysitter.js' ],
						dest: 'app/media/vendor/backbone.babysitter/',
						expand: true, filter: 'isFile'
					},
					{ cwd: 'app/media/vendor/marionette/lib/',
						src: [ 'backbone.marionette.js' ],
						dest: 'app/media/vendor/marionette/',
						expand: true, filter: 'isFile'
					},
					{ cwd: 'app/media/vendor/marionette.backbone.syphon/lib/',
						src: [ 'backbone.syphon.js' ],
						dest: 'app/media/vendor/marionette.backbone.syphon/',
						expand: true, filter: 'isFile'
					},
					{ cwd: 'app/media/vendor/backbone.wreqr/lib/',
						src: [ 'backbone.wreqr.js' ],
						dest: 'app/media/vendor/backbone.wreqr/',
						expand: true, filter: 'isFile'
					},
					{ cwd: 'app/media/vendor/bootstrap/js/',
						src: [ '*.js' ],
						dest: 'app/media/vendor/bootstrap/',
						expand: true, filter: 'isFile'
					},
					{ cwd: 'app/media/vendor/jquery/dist/',
						src: [ 'jquery.min.js' ],
						dest: 'app/media/vendor/jquery/',
						expand: true, filter: 'isFile'
					},
					{ cwd: 'app/media/vendor/jquery-file-upload/js/',
						src: [ '**' ],
						dest: 'app/media/vendor/jquery-file-upload/',
						expand: true, filter: 'isFile'
					},
					{ cwd: 'app/media/vendor/jquery-pagination/src/',
						src: [ 'jquery.pagination.js' ],
						dest: 'app/media/vendor/jquery-pagination/',
						expand: true, filter: 'isFile'
					},
					{
						src: 'app/media/vendor/speakingurl/lib/index.js',
						dest: 'app/media/vendor/speakingurl/speakingurl.js'
					},
					{ cwd: 'app/media/vendor/less.js/dist/',
						src: [ 'less-1.7.5.min.js' ],
						dest: 'app/media/vendor/less.js/',
						expand: true, filter: 'isFile'
					}
				]
			}
		},

		/**
		* CONCAT plugin
		* This is for completely rebelious plugins we'll need to AMD'fy.
		* A "banner" and "footer" sections will be injected into js file,
		* while making sure this process is idempotent, meaning it will not
		* keep appending those sections if you run this task again.
		*
		* https://npmjs.org/package/grunt-contrib-concat
		*/
		concat: [
			{
				options: {
					banner: '/*!*/(function (root) { var amdExports; define([ \'jquery\' ], function (jQuery) { (function () {/*!*/',
					footer: '/*!*/}.call(root)); return amdExports; }); }(this));/*!*/',
					process: function (src) {
						return src.replace(/\/\*!\*\/.+\/\*!\*\//g, '');
					}
				},
				src: 'app/media/vendor/jquery-chosen/chosen.jquery.js',
				dest: 'app/media/vendor/jquery-chosen/chosen.jquery.js'
			}
		],

		/**
		* CLEAN
		* This is risky and quite "neat-freakie". However, we're do this house
		* cleaning for each library also for security. These files are going
		* straight to the public folder, so we should examine and clean each one.
		*
		* https://npmjs.org/package/grunt-contrib-clean
		*/
		clean: [
			// General garbage
			'app/media/vendor/**/.editorconfig',
			'app/media/vendor/**/.npmignore',
			'app/media/vendor/**/.eslintrc',
			'app/media/vendor/**/.mailmap',
			'app/media/vendor/**/.rvmrc',
			'app/media/vendor/**/.travis.yml',
			'app/media/vendor/**/.DS_Store',
			'app/media/vendor/**/.git*',
			'app/media/vendor/**/.js*',
			'app/media/vendor/**/*.md',
			'app/media/vendor/**/*.nuspec',
			'app/media/vendor/**/bower.json',
			'app/media/vendor/**/package.json',
			'app/media/vendor/**/Gruntfile.js',

			// Cleaning misc
			'app/media/vendor/backbone/!(backbone.js)*',
			'app/media/vendor/backbone.babysitter/!(backbone.babysitter.js)*',
			'app/media/vendor/marionette/!(backbone.marionette.js)*',
			'app/media/vendor/marionette.backbone.syphon/!(backbone.syphon.js)*',
			'app/media/vendor/backbone.wreqr/!(backbone.wreqr.js)*',
			'app/media/vendor/jquery-file-upload/!(*.js)*',
			'app/media/vendor/jquery-file-upload/jquery.fileupload-angular.js',
			'app/media/vendor/jquery-file-upload/jquery.fileupload-audio.js',
			'app/media/vendor/jquery-file-upload/jquery.fileupload-image.js',
			'app/media/vendor/jquery-file-upload/jquery.fileupload-jquery-ui.js',
			'app/media/vendor/jquery-file-upload/jquery.fileupload-ui.js',
			'app/media/vendor/jquery-file-upload/jquery.fileupload-video.js',
			'app/media/vendor/blueimp-canvas-to-blob/js/canvas-to-blob.min.js',
			'app/media/vendor/blueimp-load-image/js/load-image.all.min.js',
			'app/media/vendor/blueimp-tmpl/js/tmpl.min.js',
			'app/media/vendor/bootstrap/!(*.js|less|fonts)*',
			'app/media/vendor/fastclick/!(lib)*',
			'app/media/vendor/foundation/css',
			'app/media/vendor/foundation/js/*(*.js|vendor)',
			'app/media/vendor/jquery/!(jquery.min.js)*',
			'app/media/vendor/jquery-ajaxQueue/!(src)*',
			'app/media/vendor/jquery-chosen/!(chosen)*',
			'app/media/vendor/jquery-chosen/chosen.proto*.js',
			'app/media/vendor/jquery-chosen/*.min.*',
			'app/media/vendor/jquery-pagination/!(jquery.pagination.js)*',
			'app/media/vendor/jquery-placeholder/!(jquery.placeholder.js)*',
			'app/media/vendor/jquery.transit/!(jquery.transit.js)*',
			'app/media/vendor/JSON-js/!(json2.js)*',
			'app/media/vendor/less.js/!(less-1.7.5.min.js)*',
			'app/media/vendor/modernizr/!(modernizr.js)*',
			'app/media/vendor/moment/!(moment.js)*',
			'app/media/vendor/mustache/!(mustache.js)*',
			'app/media/vendor/requirejs/!(require.js)*',
			'app/media/vendor/requirejs-plugins/!(src)*',
			'app/media/vendor/slickgrid/!(*.js|*.css)*',
			'app/media/vendor/speakingurl/!(speakingurl.js)*',
			'app/media/vendor/stache/!(stache.js)*',
			'app/media/vendor/requirejs-text/!(text.js)*',
			'app/media/vendor/toastr/!(toastr.js)*',
			'app/media/vendor/underscore/!(underscore.js)*'
		]
	};}

};
