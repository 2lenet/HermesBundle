import grapesjs from 'grapesjs';
import grapesjsmjml from 'grapesjs-mjml';
import mjml2html from 'mjml-browser';

window.addEventListener('load', () => {
    grapesjs.plugins.add('grapesjs-mjml', grapesjsmjml);

    document.querySelectorAll('.lle_hermes_mjml_editor').forEach((e) => {
        let input = document.querySelector(e.dataset.input);

        let editor = grapesjs.init({
            fromElement: true,
            storageManager: { type: null },
            container: e,
            plugins: ['grapesjs-mjml'],
            noticeOnUnload: false,
        });

        function update() {
            let mjml = editor.getHtml();

            input.value = JSON.stringify({
                mjml: mjml,
                html: mjml2html(mjml),
            });
        }

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
