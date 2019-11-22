import { src, dest } from "gulp";
import readme from "gulp-readme-to-markdown";

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
