const glob = require("glob")
const globImporter = require("node-sass-glob-importer")
const MiniCssExtractPlugin = require("mini-css-extract-plugin")
const path = require("path")

module.exports = {
  entry: {
    main: [ 
      "./entrypoint.js",
      "./entrypoint.scss" 
    ]
    //.concat( glob.sync( path.resolve(__dirname + '/../../', 'linotype') + "/Block/**/*.js" ) )
    //.concat( glob.sync( path.resolve(__dirname + '/../../', 'linotype') + "/Block/**/*.scss" ) )
  },
  module: {
    rules: [
      {
        test: /\.s?css$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: "css-loader",
            options: {
              sourceMap: false,
              modules: false,
              localIdentName: "[local]___[hash:base64:5]"
            }
          },
          {
            loader: "sass-loader",
            options: {
              sourceMap: false,
              importer: globImporter(),
            }
          }
        ]
      },
      {
        test: /\.js$/,
        exclude: /(node_modules)/,
        use: {
          loader: "babel-loader",
          options: {
            sourceMap: false,
            presets: ["@babel/preset-env"],
            plugins: ["@babel/plugin-proposal-class-properties"]
          }
        }
      },
      {
        test: /\.(png|jpe?g|gif|svg)$/i,
        use: {
          loader: 'file-loader',
          options: {
            esModule: false,
            name: path.resolve(__dirname + '/../../public', 'assets/img') + "/[name].[ext]"
          }
        }
      }
    ]
  },
  resolve: {
    modules: ["node_modules", path.resolve(__dirname, "node_modules")],
    extensions: ['.scss', '.js', '.jsx', '.json']
  },
  devtool: "source-map",
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname + '/../../public', 'assets/js'),
    sourceMapFilename: "[name].map"
  },
  plugins: [
    new MiniCssExtractPlugin({
      filename: "./../css/[name].css",
    }),
  ]
};