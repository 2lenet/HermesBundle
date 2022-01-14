const Encore = require("@symfony/webpack-encore");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It"s useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || "dev");
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath("./src/Resources/public")
    // public path used by the web server to access the output path
    .setPublicPath("./")
    // only needed for CDN"s or sub-directory deploy
    .setManifestKeyPrefix("bundles/hermes")
    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry("app", "./assets/js/app.js")
    .addStyleEntry("style", "./assets/css/style.scss")

    .copyFiles({
        from: "./assets/img",
        to: "img/[path][name].[ext]",
    })

    .disableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    
    .enableSourceMaps(!Encore.isProduction())
    
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .enableEslintLoader()

    .enableSassLoader()
;

module.exports = Encore.getWebpackConfig();
