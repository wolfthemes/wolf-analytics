module.exports = {

	build: {

		options : {
			banner : '/*! <%= app.name %> Wordpress Plugin v<%= app.version %> */ \n'
			// preserveComments : 'some'
		},

		files: {

			// '<%= app.jsPath %>/min/message-bar.min.js': [
			// 	'<%= app.jsPath %>/message-bar.js'
			// ],
		}
	}
};