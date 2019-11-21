import { task, parallel, series } from "gulp";

import { bsLocal } from "./browserSync";
import { watchFiles } from "./watch";
import { cleanDist, cleanDSStore, copyPlugin, deleteEmptyDir } from "./release";
import { getPluginSize, replacePluginTexts, zipPlugin } from "./release";
import { pushPlugins } from "./deploy";

task("push:plugin", pushPlugins);
task("release:plugin", series(cleanDist, cleanDSStore, copyPlugin, deleteEmptyDir, replacePluginTexts, zipPlugin, getPluginSize));
task("default", parallel(watchFiles, bsLocal));
