import grapesjs from 'grapesjs';
import grapesjsmjml from 'grapesjs-mjml';
import fr from 'grapesjs/locale/fr';
import mjmlFr from './locale/fr';
import mjml2html from 'mjml-browser';

let onLoad = (callback) => {
    if (document.readyState !== 'loading') {
        callback();
    } else {
        document.addEventListener('DOMContentLoaded', callback);
    }
};

onLoad(() => {
    grapesjs.plugins.add('grapesjs-mjml', grapesjsmjml);

    document.querySelectorAll('.lle_mjml_editor').forEach(e => {

        let input = document.querySelector(e.dataset.input);

        let editor = grapesjs.init({
            fromElement: true,
            storageManager: { type: null }, // disables autosave in local storage
            container: e,
            plugins: ['grapesjs-mjml'],
            pluginsOpts: {
                'grapesjs-mjml': {
                    i18n: {
                        fr: mjmlFr,
                    },
                },
            },
            i18n: {
                messages: { fr: fr },
            },
            noticeOnUnload: false, // prevent popup saying we have unsaved changes
        });

        function update() {
            let mjml = editor.getHtml(); // the code inside the editor is actually MJML, not HTML
            // DONT USE THE GRAPEJS HTML CODE IT DOESN'T INCLUDE CSS
            input.value = JSON.stringify({
                mjml: mjml,
                html: mjml2html(mjml), // computed HTML from MJML
            });
        }

        // load the already existing value
        update();

        editor.on('update', () => {
            update();
        });
        editor.on('modal:open', () => {
            const button = document.querySelector('.gjs-btn-prim');
            if (button) {
                button.setAttribute('type', 'button');
            }
        });
    });
});
