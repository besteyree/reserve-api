const CracoAlias = require("craco-alias");
const CracoLessPlugin = require("craco-less");

module.exports = {
  plugins: [
    {
      plugin: CracoLessPlugin,
      options: {
        lessLoaderOptions: {
          lessOptions: {
              modifyVars: {
                  "brand-primary" : "#594943",
                  "brand-primary-tap" : "#594943",
                  "primary-color": "#594943",
                  "link-color": "#0DD078",
                  // "success-color": "#34853",
                  "border-radius-base": "10px",
                  "tabs-color": "#db2b39"
                  // "tabs-height": 43.5 * @hd;
                  // "tabs-font-size-heading": 15 * @hd;
                  // "tabs-ink-bar-height": @border-width-lg;
              },
              javascriptEnabled: true,
          },
        }
      }
    },
    {
      plugin: CracoAlias,
      options: {
        source: "tsconfig",
        baseUrl: "./",
        tsConfigPath: "./tsconfig.extend.json",
      }
    }
  ]
};