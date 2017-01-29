const path = require('path');
const webpack = require('webpack');
const ExtractTextPlugin = require('extract-text-webpack-plugin');

module.exports = {
  entry: {
    holiday_admin: './js/holiday_admin',
  },
  output: {
    path: path.join(__dirname, '../static/js'),
    filename: '[name].js',
  },
  resolve: {
    modulesDirectories: ['node_modules'],
    extensions: ['', '.js', '.jsx', '.elm'],
  },
  module: {
    loaders: [
      {
        test: /\.(js|jsx)$/,
        loader: 'babel-loader',
        query: {
          presets: ['es2015', 'react', 'stage-2'],
          plugins: ['transform-class-properties'],
        },
        exclude: [/node_modules/],
      },
    ],

    noParse: /\.elm$/,
  },
  plugins: [
    new ExtractTextPlugin('styles.css'),
  ]
};
