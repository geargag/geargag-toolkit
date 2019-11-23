import { task, series } from "gulp";

import { cleanDist, cleanDSStore, copyPlugin, deleteEmptyDir } from "./release";
import { getPluginSize, replacePluginTexts, zipPlugin } from "./release";
import { readmeToMarkdown } from "./general";
import { buildPluginPotFile } from "./language";

task("build:lang", buildPluginPotFile);
task("build:plugin", series(cleanDist, cleanDSStore, copyPlugin, deleteEmptyDir, replacePluginTexts));
task(
	"release:plugin",
	series(cleanDist, cleanDSStore, copyPlugin, deleteEmptyDir, replacePluginTexts, zipPlugin, readmeToMarkdown, getPluginSize),
);
