import { task, parallel, series } from "gulp";

import { bsLocal } from "./browserSync";
import { watchFiles } from "./watch";
import { cleanDist, cleanDSStore, copyPlugin, deleteEmptyDir } from "./release";
import { getPluginSize, replacePluginTexts, zipPlugin } from "./release";
import { pushPlugins } from "./deploy";
import { readmeToMarkdown } from "./general";
import { buildPluginPotFile } from "./language";

task("push:plugin", pushPlugins);
task("build:lang", buildPluginPotFile);
task("release:forGithub", series(cleanDist, cleanDSStore, copyPlugin, deleteEmptyDir, replacePluginTexts, zipPlugin));
task(
	"release:plugin",
	series(cleanDist, cleanDSStore, copyPlugin, deleteEmptyDir, replacePluginTexts, zipPlugin, readmeToMarkdown, getPluginSize),
);
task("default", parallel(watchFiles, bsLocal));
