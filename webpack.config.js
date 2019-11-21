const { resolve } = require("path");
const webpack = require("webpack");
const argv = require("yargs").argv;

const BundleAnalyzer = require("webpack-bundle-analyzer").BundleAnalyzerPlugin;
const MiniCssExtract = require("mini-css-extract-plugin");
const ErrorNotification = require("webpack-error-notification");
const DuplicatePackageChecker = require("duplicate-package-checker-webpack-plugin");

const webpackModules = {
	rules: [
		{
			test: /\.js$/,
			use: ["babel-loader"],
			exclude: /node_modules/,
		},
		{
			test: /\.css$/,
			use: [MiniCssExtract.loader, "css-loader"],
		},
		{
			test: /\.scss$/,
			use: [MiniCssExtract.loader, "css-loader", "sass-loader"],
		},
		{
			test: /\.(png|gif|jpg|jpeg|woff|woff2|eot|ttf|svg)$/,
			use: ["url-loader"],
		},
	],
};

const pluginConfig = {
	mode: "development",
	entry: {
		plugin: "./src/plugin/assets/js/src/index.js",
	},
	output: {
		path: resolve("./src/plugin/assets/js/dist"),
		filename: "[name].js",
	},
	module: webpackModules,
	externals: {
		lodash: "lodash",
		jquery: "jQuery",
	},
	stats: {
		colors: true,
	},
	plugins: [
		new webpack.ProvidePlugin({
			_: "lodash",
			_merge: ["lodash", "merge"],
			_map: ["lodash", "map"],
			_times: ["lodash", "times"],
			_each: ["lodash", "each"],
			_isNumber: ["lodash", "isNumber"],
			$: "jquery",
			jQuery: "jquery",
		}),
		new MiniCssExtract({
			filename: "../../scss/generic/gen_[name].scss",
		}),
		new ErrorNotification(),
		new DuplicatePackageChecker(),
	],
	devtool: "cheap-module-source-map",
};

if (argv.mode === "production") {
	delete pluginConfig.devtool;
}

if (argv.analytic) {
	pluginConfig.plugins.push(new BundleAnalyzer());
	delete pluginConfig.devtool;
}

module.exports = pluginConfig;
