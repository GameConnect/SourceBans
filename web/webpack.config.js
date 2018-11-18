const Encore = require('@symfony/webpack-encore');

Encore
    .setOutputPath('public/build')
    .setPublicPath('/build')
    .addEntry('main', './assets/js/main.js')
    .addLoader({
        test: /bootstrap\.native/,
        use: {
            loader: 'bootstrap.native-loader',
            options: {
                only: [
                    'collapse',
                    'dropdown',
                ],
            },
        },
    })
    .disableSingleRuntimeChunk()
    .enableSassLoader()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
;

module.exports = Encore.getWebpackConfig();
