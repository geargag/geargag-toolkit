const paths = {
	plugin: {
		sass: {
			watch: ["src/plugin/assets/scss/**/*.scss"],
			generate: "src/plugin/assets/scss/*.scss",
		},
		files: ["src/plugin/assets/css/*.css", "src/plugin/assets/js/*.js", "src/plugin/**/*.php"],
		build: [
			"src/plugin/**",
			"!src/plugin/**/*.scss",
			"!src/plugin/src/**",
			"!src/plugin/assets/js/src/**",
			"!src/plugin/assets/libs/fontawesome-pro/webfonts/*.svg",
			"!src/plugin/assets/libs/fontawesome-pro/webfonts/fa-regular-400*",
			"!src/plugin/assets/js/font/**/*.js",
		],
	},
};

export default paths;
