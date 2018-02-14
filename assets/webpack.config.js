const path = require('path');
const webpack = require('webpack');
const merge = require('webpack-merge');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');

const TARGET = process.env.npm_lifecycle_event;

const common = {
  entry: {
    adminEventGroup: './src/entries/admin/eventGroup',
    adminHolidayAdjust: './src/entries/admin/holidayAdjust',
    adminPayment: './src/entries/admin/payment',
    adminPolicy: './src/entries/admin/policy',
    adminRecipient: './src/entries/admin/recipient',
    adminRoom: './src/entries/admin/room',
    holidayTeam: './src/entries/holidayTeam',
    room: './src/entries/room',
    userMe: './src/entries/userMe',
  },
  output: {
    path: path.join(__dirname, '../web/static/dist'),
    filename: '[name].[chunkhash].js',
  },
  resolve: {
    modules: ['node_modules'],
    extensions: ['.js', '.jsx', '.elm'],
    enforceExtension: false,
  },
  module: {
    rules: [
      {
        enforce: 'pre',
        test: /\.(js|jsx)$/,
        exclude: /(node_modules|bower_components|lib)/,
        loader: 'eslint-loader',
        options: {
          parser: 'babel-eslint',
        },
      },
      {
        test: /\.(js|jsx)$/,
        exclude: /(node_modules|bower_components|lib)/,
        loader: 'babel-loader',
        options: {
          presets: ['babel-preset-react-app'],
        },
      },
      {
        test: /\.(css|less)$/,
        loader: ExtractTextPlugin.extract({
          fallback: 'style-loader',
          use: ['css-loader', 'less-loader'],
        }),
      },
      {
        test: /\.(woff|woff2|eot|ttf|svg)$/,
        loader: 'url-loader',
      },
      {
        test: /\.(gif|png|jpe?g)$/,
        loader: 'file-loader',
      },
    ],
  },
  plugins: [
    new webpack.optimize.CommonsChunkPlugin({
      name: 'common',
      minChunk: 2,
    }),
    new ExtractTextPlugin({
      filename: '[name].[chunkhash].css',
    }),
    new ManifestPlugin({
      fileName: 'manifest.json',
      publicPath: '/static/dist/',
    }),
  ],
};

if (TARGET === 'build') {
  module.exports = merge(common, {
    plugins: [
      new webpack.DefinePlugin({
        process_env: {
          NODE_ENV: '"production"',
        },
      }),
      new webpack.optimize.UglifyJsPlugin({
        compress: {
          warnings: false,
        },
      }),
    ],
  });
} else if (TARGET === 'debug') {
  module.exports = merge(common, {});
}
