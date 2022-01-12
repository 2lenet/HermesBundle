import grapesjs from "grapesjs";
import fr from "grapesjs/locale/fr";
import mjmlFr from "grapesjs-mjml/locale/fr";

let onLoad = (callback) => {
    if (document.readyState !== "loading") {
        callback();
    } else {
        document.addEventListener("DOMContentLoaded", callback);
    }
}

onLoad(() => {
    document.querySelectorAll(".lle_mjml_editor").forEach(e => {
        grapesjs.init({
            fromElement: true,
            container : e,
            plugins: ["grapesjs-mjml"],
            pluginsOpts: {
                "grapesjs-mjml": {
                    i18n: {
                        fr: mjmlFr,
                    }
                }
            },
            i18n: {
                messages: {fr: fr}
            }
        });
    });
});
