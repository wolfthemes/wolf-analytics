module.exports = {
	
	main : {
		options: {
			// noAdvanced: true,
			// compatibility : true,
			// debug : true
			// keepBreaks : true
		},
		files: {
			'<%= app.cssPath %>/message-bar.min.css': [
				'<%= app.cssPath %>/message-bar.css'
			]
		}
	}
};