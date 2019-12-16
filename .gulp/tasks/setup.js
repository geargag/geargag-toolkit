import { resolve } from "path";
import { exec } from "child_process";
import { getInfo } from "../helpers";

const localPath = getInfo("path");
const { plugin } = require("../../package");

export function linkPlugin(done) {
	let pluginFinalPath = resolve(`dist/done/${plugin.slug}`),
		pluginPath = resolve("src/plugin"),
		targetPluginPath = resolve(localPath, "wp-content/plugins");

	let linkFinalPlugin = `ln -s ${pluginFinalPath} ${targetPluginPath}`,
		linkPlugin = `ln -s ${pluginPath} ${targetPluginPath}`,
		renamePlugin = `mv ${targetPluginPath}/plugin ${targetPluginPath}/${plugin.slug}-dev`,
		cmd = `${linkFinalPlugin} && ${linkPlugin} && ${renamePlugin}`,
		run = exec(cmd);

	run.stdout.pipe(process.stdout);
	run.stderr.pipe(process.stderr);

	done();
}
