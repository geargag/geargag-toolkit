import { exec } from "child_process";
import { join } from "path";
import { getInfo } from "../helpers";

const localPath = getInfo("path");
const remotePath = getInfo("remotePath");
const plugins = "wp-content/plugins";
const paths = {
	local: {
		plugins: join(localPath, plugins),
	},
	remote: {
		plugins: join(remotePath, plugins),
	},
};

export function pushPlugins(done) {
	let cmd = `rsync -avzhe ssh -L dist/done/gearfury-toolkit ${paths.remote.plugins}`,
		run = exec(cmd);

	run.stdout.pipe(process.stdout);
	run.stderr.pipe(process.stderr);

	done();
}
