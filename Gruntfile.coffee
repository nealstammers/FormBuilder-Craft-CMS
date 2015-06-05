module.exports = (grunt) ->
  grunt.initConfig

    # =============================================
    # VARIABLES
    # =============================================
    ScssDirectory: 'resources/css'
    CoffeeDirectory: 'resources/js'
    DistributionDirectory: 'resources/'

    # =============================================
    # WATCH FOR CHANGE
    # =============================================
    watch:
      css:
        files: ['<%= ScssDirectory %>/formbuilder-form.scss', '<%= ScssDirectory %>/formbuilder.scss']
        tasks: ['sass']
      scripts:
        files: ['<%= CoffeeDirectory %>/formbuilder-form.coffee']
        tasks: ['coffee']
      options:
        livereload: false

    # =============================================
    # SASS COMPILE
    # =============================================
    # https://github.com/gruntjs/grunt-contrib-sass
    # =============================================
    sass:
      compile:
        options:
          compress: false
          sourcemap: 'none' # none, file, inline, none
          style: 'compact' # nested, compact, compressed, expanded
        files: 
          '<%= DistributionDirectory %>/css/formbuilder.css': '<%= ScssDirectory %>/formbuilder.scss',
          '<%= DistributionDirectory %>/css/formbuilder-form.css': '<%= ScssDirectory %>/formbuilder-form.scss'

    # =============================================
    # COFFEE COMPILING
    # =============================================
    # https://github.com/gruntjs/grunt-contrib-coffee
    # =============================================
    coffee:
      options:
        join: true
        bare: true
      compile:
        files:
          '<%= DistributionDirectory %>/js/formbuilder-form.js': ['<%= CoffeeDirectory %>/formbuilder-form.coffee']

    # =============================================
    # UGLIFY JAVASCRIPT
    # =============================================
    # https://github.com/gruntjs/grunt-contrib-uglify
    # =============================================
    uglify:
      options:
        sourceMap: true
        mangle: false
        beautify: false
        compress: true
      dist:
        files:
          '<%= DistributionDirectory %>/js/formbuilder-form.min.js': ['<%= DistributionDirectory %>/js/formbuilder-form.js']

    # =============================================
    # LOAD PLUGINS
    # =============================================
    grunt.loadNpmTasks 'grunt-contrib-sass'
    grunt.loadNpmTasks 'grunt-contrib-coffee'
    grunt.loadNpmTasks 'grunt-contrib-watch'
    grunt.loadNpmTasks 'grunt-contrib-uglify'

    # =============================================
    # TASKS
    # =============================================
    grunt.registerTask 'default', ['watch']
    grunt.registerTask 'minify', ['uglify']