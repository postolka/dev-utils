// Generated using webpack-cli https://github.com/webpack/webpack-cli

const path = require("path");
const webpack = require("webpack");

const isProduction = process.env.NODE_ENV == "production";

const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const config = {
	// entry: "./src/index.ts",
	entry: {
		vendor: [
			'jquery',
			'jquery-ui',
			'bootstrap',
			'highlight.js/lib/common',
			'ace-builds/src-noconflict/ace',
			'ace-builds/src-noconflict/mode-html',
			'ace-builds/src-noconflict/theme-dracula',
		],
		utils: "./src/index.ts",
	},
	output: {
		path: path.resolve(__dirname, "www"),
		filename: '[name].js',
	},
	plugins: [
		new MiniCssExtractPlugin({
			filename: 'utils.css',
		}),
		new webpack.ProvidePlugin({
			"$": "jquery",
			"jQuery": "jquery",
			"window.jQuery": "jquery",
			"datepicker": "jquery-ui/ui/widgets/datepicker",
		}),
	],
	module: {
		rules: [
			{
				test: /\.(ts|tsx)$/i,
				loader: "ts-loader",
				exclude: ["/node_modules/"],
			},
			{
				test: /\.s[ac]ss$/i,
				use: [MiniCssExtractPlugin.loader, "css-loader", "sass-loader"],
			},
			{
				test: /\.(png|jpg|gif)$/i,
				type: "asset/resource",
				generator: {
					filename: 'img/[name][ext]'
				}
			},
			{
				test: /\.(eot|svg|ttf|woff|woff2)$/i,
				type: "asset/resource",
				generator: {
					filename: 'fonts/[name][ext]'
				}
			},
			{
				test: require.resolve('jquery'),
				use: [{
					loader: 'expose-loader',
					options: {
						// globalName: "$",
						exposes: [
							"jquery",
							"$",
						]
					}
				}]
			}
			// Add your rules for custom modules here
			// Learn more about loaders from https://webpack.js.org/loaders/
		],
	},
	resolve: {
		extensions: [".tsx", ".ts", ".js"],
	},
};

module.exports = () => {
	if (isProduction) {
		config.mode = "production";
	} else {
		config.mode = "development";
	}
	return config;
};
