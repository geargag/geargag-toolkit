import { init } from "browser-sync";

const { plugin } = require("../paths").default;

const notify = {
	styles: {
		backgroundColor: "#222",
		fontSize: "1.2em",
		top: "50%",
		borderBottomLeftRadius: 0,
		fontFamily: "inherit",
	},
};

export function bsLocal() {
	init({
		ui: false,
		files: plugin.files,
		notify,
	});
}
