import { watch, series } from "gulp";

import { buildPluginSass } from "./sass";

const { plugin } = require("../paths").default;

export function watchFiles() {
	watch(plugin.sass.watch, series(buildPluginSass));
}
