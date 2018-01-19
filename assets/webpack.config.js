const path = require('path');
const webpack = require('webpack');
const merge = require('webpack-merge');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const ManifestPlugin = require('webpack-manifest-plugin');

const TARGET = process.env.npm_lifecycle_event;

const common = {
  entry: {
    policy: ['babel-polyfill', './js/components/Policy'],
    recipient: ['babel-polyfill', './js/components/Recipient'],
    room: ['babel-polyfill', './js/components/Room'],
    eventGroup: ['babel-polyfill', './js/components/EventGroup'],
    scheduler: ['babel-polyfill', './js/components/Scheduler'],
    holiday_adjust: './js/components/HolidayAdjust',
    me: ['babel-polyfill', './js/components/Me'],
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
        test: /\.jsx$/,
        enforce: 'pre',
        use: {
          loader: 'eslint-loader',
        },
      },
      {
        test: /\.jsx$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          query: {
            presets: ['env', 'react', 'stage-2'],
          },
        },
      },
      {
        test: /\.css|less$/,
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
      chunks: ['policy', 'recipient', 'room', 'eventGroup', 'scheduler', 'holiday_adjust', 'me'],
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
