import { src, dest } from "gulp";
import sort from "gulp-sort";
import wpPot from "gulp-wp-pot";

const { plugin } = require("../../package");
const { team, bugReport, lastTranslator } = require("../../package").languages;

export function buildPluginPotFile() {
	return src("src/plugin/**/*.php")
		.pipe(sort())
		.pipe(wpPot({ bugReport, team, lastTranslator }))
		.pipe(dest(`src/plugin/languages/${plugin.slug}.pot`));
}
