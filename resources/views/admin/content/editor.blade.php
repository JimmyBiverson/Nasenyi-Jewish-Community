<script>
    {
        const wrapper = document.getElementById('rich-editor');
        const editor = document.getElementById('editor-content');
        const input = document.getElementById('body');
        const source = document.getElementById('editor-source');
        const toggle = document.getElementById('toggle-source');
        const title = document.getElementById('title');
        const slug = document.getElementById('slug');
        const searchTitle = document.getElementById('seo-title');
        const searchDescription = document.getElementById('seo-description');
        const summary = document.getElementById('summary');
        const baseUrl = {{ Illuminate\Support\Js::from(url('/')) }};
        const updatePreview = () => {
            const heading = searchTitle.value.trim() || title.value.trim();
            const description = searchDescription.value.trim() || summary.value.trim();
            document.getElementById('seo-preview-title').textContent = heading;
            document.getElementById('seo-preview-address').textContent = baseUrl + '/' + slug.value.trim();
            document.getElementById('seo-preview-description').textContent = description;
            document.getElementById('seo-preview-length').textContent = `Title: ${heading.length} characters. Description: ${description.length} characters.`;
        };
        [title, slug, searchTitle, searchDescription, summary].forEach(field => field.addEventListener('input', updatePreview));
        updatePreview();
        let previousSource = input.value;
        if (@json($editorHtml)) {
            wrapper.hidden = false;
            source.hidden = true;
            editor.innerHTML = input.value;
        } else {
            input.readOnly = true;
        }

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
