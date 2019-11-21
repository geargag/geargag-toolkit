import { resolve } from "path";
import { src, dest } from "gulp";
import notify from "gulp-notify";
import plumber from "gulp-plumber";
import sourcemaps from "gulp-sourcemaps";
import sassGlob from "gulp-sass-glob";
import sass from "gulp-sass";
import postcss from "gulp-postcss";
import inlineSvg from "postcss-inline-svg";
import svgo from "postcss-svgo";
import autoprefixer from "autoprefixer";
import mqpacker from "css-mqpacker";
import pxtorem from "postcss-pxtorem";

const { title } = require("../../package").plugin;
const { plugin } = require("../paths").default;

const processors = {
	modules: [inlineSvg(), svgo(), autoprefixer()],
	modulesFull: [
		inlineSvg(),
		svgo(),
		autoprefixer(),
		mqpacker({ sort: true }),
		pxtorem({
			rootValue: 16,
			unitPrecision: 5,
			propWhiteList: [],
			selectorBlackList: [],
			replace: true,
			mediaQuery: false,
			minPixelValue: 2,
		}),
	],
};

function errorHandler(error) {
	let line, file, message;

	if (error.file) {
		line = `${error.line}:${error.column}`;
		file = error.file.replace(resolve("./src/"), "");
		message = `L${line}:${file}`;
	} else {
		message = "See console!";
	}

	notify({
		title: `${title} | ${error.plugin}`,
		subtitle: "😭Failed!😭",
		message,
		sound: "Sosumi",
	}).write(error);

	console.error(error.message);

	this.emit("end"); // Prevent the 'watch' task from stopping
}

export function buildPluginSass() {
	return src(plugin.sass.generate)
		.pipe(plumber({ errorHandler }))
		.pipe(sourcemaps.init({ largeFile: true }))
		.pipe(sourcemaps.identityMap())
		.pipe(sassGlob())
		.pipe(sass({ outputStyle: "compressed" }))
		.pipe(postcss(processors.modules))
		.pipe(sourcemaps.write("./"))
		.pipe(dest("src/plugin/assets/css"));
}
