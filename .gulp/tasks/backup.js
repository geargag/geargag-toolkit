import { exec } from "child_process";
import dateFormat from "dateformat";

const time = dateFormat(new Date(), "HH-MM_dd-mm-yyyy");

export function backupLocalDB(done) {
	let cmd = `wp db export .backup/local_${time}.sql`,
		run = exec(cmd);

	run.stdout.pipe(process.stdout);
	run.stderr.pipe(process.stderr);

	done();
}

export function backupRemoteDB(done) {
	let cmd = `wp @live db export - > .backup/remote_${time}.sql`,
		run = exec(cmd);

	run.stdout.pipe(process.stdout);
	run.stderr.pipe(process.stderr);

	done();
}
