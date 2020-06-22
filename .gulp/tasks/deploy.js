import { exec } from "child_process";
import { join } from "path";
import { getInfo } from "../helpers";

const repoPath = join(__dirname, "..");
const localPath = getInfo("path");
const localDomain = getInfo("localDomain");
const remotePath = getInfo("remotePath");
const remoteDomain = getInfo("remoteDomain");
const themes = "wp-content/themes/";
const uploads = "wp-content/uploads/";
const plugins = "wp-content/plugins/";
const paths = {
	local: {
		themes: join(localPath, themes),
		uploads: join(localPath, uploads),
		plugins: join(localPath, plugins),
	},
	remote: {
		themes: join(remotePath, themes),
		uploads: join(remotePath, uploads),
		plugins: join(remotePath, plugins),
	},
};

export function pushPlugin(done) {
	let upPlugin = `rsync -avzhe ssh --delete -L --exclude-from .gulp/deploy_exclude.txt ./src/plugin/** ${paths.remote.plugins}/geargag-toolkit-dev/`,
		run = exec(upPlugin);

	run.stdout.pipe(process.stdout);
	run.stderr.pipe(process.stderr);

	done();
}
