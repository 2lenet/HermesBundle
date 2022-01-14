import grapesjs from "grapesjs";
import grapesjsmjml from "grapesjs-mjml";
import fr from "grapesjs/locale/fr";
import mjmlFr from "./locale/fr";

let onLoad = (callback) => {
    if (document.readyState !== "loading") {
        callback();
    } else {
        document.addEventListener("DOMContentLoaded", callback);
    }
}

onLoad(() => {
    grapesjs.plugins.add("grapesjs-mjml", grapesjsmjml);

    document.querySelectorAll(".lle_mjml_editor").forEach(e => {

        let input = document.querySelector(e.dataset.input);

        let editor = grapesjs.init({
            fromElement: true,
            storageManager: { type: null }, // disables autosave in local storage
            container: e,
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
            },
            noticeOnUnload: false, // prevent popup saying we have unsaved changes
        });

        function update() {
            let mjml = editor.getHtml(); // the code inside the editor is actually MJML, not HTML
            let code = editor.runCommand("mjml-get-code");

            for (let error of code.errors) {
                // TODO: Show the error to the user
                console.error(error.formattedMessage);
            }

            input.value = JSON.stringify({
                mjml: mjml,
                html: code.html, // computed HTML from MJML
            });
        }

        // load the already existing value
        update();

        editor.on("update", () => {
            update();
        });
    });
});
