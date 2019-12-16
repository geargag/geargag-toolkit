const paths = {
	plugin: {
		sass: {
			watch: ["src/plugin/assets/scss/**/*.scss"],
			generate: "src/plugin/assets/scss/*.scss",
		},
		files: ["src/plugin/assets/css/*.css", "src/plugin/assets/js/*.js", "src/plugin/**/*.php"],
		build: ["src/plugin/**", "!src/plugin/**/*.scss", "!src/plugin/assets/js/src/**"],
	},
};

export default paths;
