const path = require('path');
const plugins = [require('precss'),require('autoprefixer')()];
const MiniCssExtractPlugin = require('mini-css-extract-plugin');

module.exports = {
    test: /\.(scss)$/,
    use: [
        {
            loader: 'style-loader',
        },
        {
            loader: MiniCssExtractPlugin.loader,
        },
        {
            loader: 'css-loader',
            options: {
                importLoaders: 1,
            },
        },
        {
            loader: 'postcss-loader',
            options: {
                plugins,
            },
        },
        {
            loader: 'sass-loader',
        },
    ],
};
