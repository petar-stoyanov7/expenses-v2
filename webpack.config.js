require('dotenv-flow').config();

const Encore = require('@symfony/webpack-encore');

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
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app', './assets/js/index.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

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
    .enableVersioning(Encore.isProduction())

    // configure Babel
    // .configureBabel((config) => {
    //     config.plugins.push('@babel/a-babel-plugin');
    // })

    // enables and configure @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = '3.23';
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
    .enableReactPreset()
    .configureDefinePlugin((options) => {
        options['process.env'] = {
            SERVER_URL: JSON.stringify(process.env.SERVER_URL),
            HASH: JSON.stringify(process.env.HASH),
            REGISTER_PATH: JSON.stringify(process.env.REGISTER_PATH),
            LOGIN_PATH: JSON.stringify(process.env.LOGIN_PATH),
            USER_EDIT_PATH: JSON.stringify(process.env.USER_EDIT_PATH),
            GET_USER_PATH: JSON.stringify(process.env.GET_USER_PATH),
            GET_CAR_PATH: JSON.stringify(process.env.GET_CAR_PATH),
            CREATE_CAR_PATH: JSON.stringify(process.env.CREATE_CAR_PATH),
            EDIT_CAR_PATH: JSON.stringify(process.env.EDIT_CAR_PATH),
            DELETE_CAR_PATH: JSON.stringify(process.env.DELETE_CAR_PATH),
            GET_USER_EXPENSES_PATH: JSON.stringify(process.env.GET_USER_EXPENSES_PATH),
            GET_CAR_EXPENSES_PATH: JSON.stringify(process.env.GET_CAR_EXPENSES_PATH),
            GET_FUELS_PATH: JSON.stringify(process.env.GET_FUELS_PATH),
            GET_EXPENSE_TYPES_PATH: JSON.stringify(process.env.GET_EXPENSE_TYPES_PATH),
            ADD_EXPENSE_PATH: JSON.stringify(process.env.ADD_EXPENSE_PATH),
            IMPORT_EXPENSES_PATH: JSON.stringify(process.env.IMPORT_EXPENSES_PATH),
            GET_OVERALL_PATH: JSON.stringify(process.env.GET_OVERALL_PATH),
            GET_LAST_FIVE_PATH: JSON.stringify(process.env.GET_LAST_FIVE_PATH),
            EDIT_EXPENSE_PATH: JSON.stringify(process.env.EDIT_EXPENSE_PATH),
            DELETE_EXPENSE_PATH: JSON.stringify(process.env.DELETE_EXPENSE_PATH)
        }
    })
;

module.exports = Encore.getWebpackConfig();
