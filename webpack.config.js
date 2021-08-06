var Encore = require('@symfony/webpack-encore');
var webpack = require('webpack');
var path = require('path');

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    //.setManifestKeyPrefix('build/')

    .addPlugin(new webpack.IgnorePlugin(/\.\/locale$/))

    /*
     * ENTRY CONFIG
     *
     * Add 1 entry for each "page" of your app
     * (including one that's included on every page - e.g. "app")
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if you JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/app.js')
    // .addEntry('professionals', './assets/js/professionals.js')
    .addEntry('companies', './assets/js/companies.js')
    .addEntry('lessons', './assets/js/lessons.js')
    .addEntry('profile', './assets/js/profile.js')
    .addEntry('create_request', './assets/js/create_request.js')
    .addEntry('edit_request', './assets/js/edit_request.js')
    .addEntry('educator_profile', './assets/js/educator_profile.js')
    .addEntry('chat', './assets/js/chat.js')
    .addEntry('report_dashboard', './assets/js/report_dashboard.js')
    .addEntry('report_builder', './assets/js/report_builder.js')

    //.addEntry('page1', './assets/js/page1.js')
    //.addEntry('page2', './assets/js/page2.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    /*.splitEntryChunks()*/

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    /*.enableSingleRuntimeChunk()*/
    .disableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning()

    // enables @babel/preset-env polyfills
    .configureBabel(() => {}, {
        useBuiltIns: 'usage',
        corejs: 3
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // enable post CSS loader
    .enablePostCssLoader()

    .copyFiles([{
        from: './assets/images',
        to: 'images/[path][name].[hash:8].[ext]'
    }, {
        from: './assets/static',
        to: 'static/[path][name].[hash:8].[ext]'
    }])

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes()

    // allow react
    .enableReactPreset()

    // uncomment if you're having problems with a jQuery plugin
    .autoProvidejQuery()

    // uncomment if you use API Platform Admin (composer req api-admin)
    //.enableReactPreset()
    //.addEntry('admin', './assets/js/admin.js')

;

var config = Encore.getWebpackConfig();

config.resolve.alias = {
    'jquery': path.resolve(__dirname, 'node_modules/jquery/dist/jquery.js'),
    'jquery-ui': path.resolve(__dirname, 'node_modules/jquery-ui-bundle/jquery-ui.js')
};

module.exports = config;
