import { existsSync, readFileSync } from "fs";

function getInfo(keyword, filePath = "./wp-cli.local.yml") {
	let info,
		fileContent = existsSync(filePath) ? readFileSync(filePath, "utf-8") : "",
		lines = fileContent.split(/\r\n|\n|\n\t/);

	for (let line of lines) {
		if (line.includes(keyword)) {
			let arrayOfLine = line.split(" ");

			if (arrayOfLine[1]) {
				info = arrayOfLine[1];
			}

			break;
		}
	}

	return info;
}

export { getInfo };
