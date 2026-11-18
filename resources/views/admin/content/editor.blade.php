<script>
    {
        const wrapper = document.getElementById('rich-editor');
        const editor = document.getElementById('editor-content');
        const input = document.getElementById('body');
        const source = document.getElementById('editor-source');
        const toggle = document.getElementById('toggle-source');
        let previousSource = input.value;
        wrapper.hidden = false;
        source.hidden = true;
        editor.innerHTML = input.value;

        editor.addEventListener('input', () => { input.value = editor.innerHTML; });
        editor.addEventListener('paste', event => {
            event.preventDefault();
            document.execCommand('insertText', false, event.clipboardData.getData('text/plain'));
        });
        editor.addEventListener('drop', event => event.preventDefault());

        wrapper.querySelectorAll('button').forEach(button => {
            if (button !== toggle) {
                button.addEventListener('mousedown', event => event.preventDefault());
            }
        });
        wrapper.querySelectorAll('[data-format]').forEach(button => {
            button.addEventListener('click', () => {
                if (!source.hidden) return;
                editor.focus();
                document.execCommand(button.dataset.format, false, button.dataset.value ?? null);
                input.value = editor.innerHTML;
            });
        });
        document.getElementById('make-link').addEventListener('click', () => {
            if (!source.hidden) return;
            const url = window.prompt('Enter a link address');
            if (url && /^(https?:\/\/|\/(?!\/)|#)/i.test(url)) {
                editor.focus();
                document.execCommand('createLink', false, url);
                input.value = editor.innerHTML;
            }
        });
        toggle.addEventListener('click', () => {
            if (source.hidden) {
                previousSource = input.value;
                source.hidden = false;
                editor.hidden = true;
                toggle.textContent = 'Edit visually';
                toggle.setAttribute('aria-expanded', 'true');
                input.focus();
            } else {
                if (input.value !== previousSource) {
                    window.alert('Save your HTML changes before switching back to visual editing.');
                    return;
                }
                editor.hidden = false;
                source.hidden = true;
                toggle.textContent = 'Edit HTML';
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }
</script>
