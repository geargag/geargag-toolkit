import gulp, { src, dest } from "gulp";
import readme from "gulp-readme-to-markdown";
import replace from "gulp-replace-task";
const { plugin } = require("../../package");

export function readmeToMarkdown() {
	return src("src/plugin/readme.txt")
		.pipe(
			readme({
				details: false,
				extract: {
					changelog: "CHANGELOG",
					"Frequently Asked Questions": "FAQ",
				},
			}),
		)
		.pipe(dest("."));
}

export function replaceNamespaceText() {
	return gulp
		.src("src/plugin/**/*")
		.pipe(
			replace({
				patterns: [
					{
						json: {
							namespace: plugin.namespace,
						},
					},
				],
				prefix: "vnh_",
			}),
		)
		.pipe(gulp.dest("src/plugin"));
}
