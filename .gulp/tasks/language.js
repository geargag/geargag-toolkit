import { exec } from "child_process";

const { plugin } = require("../../package");
const { team, bugReport, lastTranslator } = require("../../package").languages;
const headers = `{"Language-Team":"${team}","Report-Msgid-Bugs-To":"${bugReport}","Last-Translator":"${lastTranslator}"}`;

export function buildPluginPotFile(done) {
	const run = exec(
		`wp i18n make-pot src/plugin src/plugin/languages/${plugin.name}.pot  --exclude='assets,utils' --headers='${headers}'`,
	);

	run.stdout.pipe(process.stdout);
	run.stderr.pipe(process.stderr);

	done();
}
