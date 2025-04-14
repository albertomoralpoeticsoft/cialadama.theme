/*
OVH hv75111-ovh pwd -> same as all
*/

const path = require('path')

const MiniCssExtractPlugin = require('mini-css-extract-plugin')
const LiveReloadPlugin = require('webpack-livereload-plugin')
const PolyfillInjectorPlugin = require('webpack-polyfill-injector')

module.exports = env => { 

  const paths ={
    entryjs: './src/app/main.js',
    entryscss: './src/scss/main.scss',
    output: __dirname + '/astra-child-cialadama',
    public: '/wp-content/themes/astra-child-cialadama',
    cssfilename: 'main.css'
  }

  return {
    context: __dirname,
    stats: 'minimal',
    watch: true,
    name: 'blank',
    entry: {
      main: `webpack-polyfill-injector?${JSON.stringify({
        modules: [
          paths.entryjs,
          paths.entryscss
        ]
      })}!`
    },
    output: {
      path: paths.output,
      publicPath: paths.public,
      filename: '[name].js'
    },
    mode: 'development',
    devtool: false,
    module: {
      rules: [
        {
          test: /\.jsx?$/,
          exclude: /node_modules/,
          use: [          
            { 
              loader: 'babel-loader',
              options: {
                presets: [
                  '@babel/preset-env',
                  '@babel/preset-react'
                ]
              }
            }
          ]
        },
        {
          test: /\.scss$/,
          exclude: /node_modules/,
          use: [
            { 
              loader: MiniCssExtractPlugin.loader
            },
            {
              loader: 'css-loader'
            },
            {
              loader: 'sass-loader'
            }
          ]
        },
        {
          test: /\.css$/,
          include: /node_modules/,
          use: [
            'style-loader',
            'css-loader'
          ]
        }
      ]
    },
    plugins: [
      new PolyfillInjectorPlugin({
        singleFile: true,
        polyfills: [
          'Array.prototype.fill',
          'Array.prototype.find',
          'Array.prototype.findIndex',
          'Array.prototype.includes',
          'String.prototype.startsWith',
          'Array.from',
          'Object.entries',
          'Object.values',
          'Object.assign', 
          'fetch',
          'Promise',
        ]
      }),
      new MiniCssExtractPlugin({
        filename: paths.cssfilename
      }),
      new LiveReloadPlugin({
        protocol: 'http',
        hostname: 'localhost',
        port: 0,
        delay: 2000,
        appendScriptTag: false
      })
    ],
    resolve: {
      extensions: ['.js', '.jsx']
    }
  }
}