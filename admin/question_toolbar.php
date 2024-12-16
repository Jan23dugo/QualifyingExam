<!-- question_toolbar.php -->
<div class="editor-toolbar template" style="visibility: hidden;">
    <div class="btn-group">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="bold" title="Bold">
            <i class="fas fa-bold"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="italic" title="Italic">
            <i class="fas fa-italic"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="underline" title="Underline">
            <i class="fas fa-underline"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="strikeThrough" title="Strike Through">
            <i class="fas fa-strikethrough"></i>
        </button>
    </div>
    <div class="btn-group ms-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertUnorderedList" title="Bullet List">
            <i class="fas fa-list-ul"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="insertOrderedList" title="Numbered List">
            <i class="fas fa-list-ol"></i>
        </button>
    </div>
    <div class="btn-group ms-2">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="subscript" title="Subscript">
            <i class="fas fa-subscript"></i>
        </button>
        <button type="button" class="btn btn-sm btn-outline-secondary" data-command="superscript" title="Superscript">
            <i class="fas fa-superscript"></i>
        </button>
    </div>
</div>

<style>
.editor-wrapper {
    position: relative;
    margin-bottom: 1rem;
}

.editor-toolbar:not(.template) {
    position: absolute;
    bottom: 100%;
    left: 0;
    right: 0;
    padding: 5px;
    background: #f8f9fa;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    margin-bottom: 5px;
    z-index: 1000;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.rich-text-editor {
    border: 1px solid #dee2e6;
    border-radius: 4px;
    padding: 10px;
    width: 100%;
    min-height: 40px;
    background: #fff;
}

.rich-text-editor:focus {
    outline: none;
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
}

.editor-toolbar .btn-group {
    display: inline-flex;
}

.editor-toolbar button {
    padding: 0.25rem 0.5rem;
}

.editor-toolbar button:hover {
    background-color: #e9ecef;
}

.editor-toolbar.template {
    display: none !important;
    visibility: hidden;
}
</style>

<script>
// Update the JavaScript part
document.addEventListener('DOMContentLoaded', function() {
    function initializeToolbar() {
        const toolbarTemplate = document.querySelector('.editor-toolbar.template');
        
        function createToolbarForField(field) {
            // Check if toolbar is already initialized for this field
            if (field.closest('.editor-wrapper')) return;
            
            if (!toolbarTemplate) return;

            // Create wrapper
            const wrapper = document.createElement('div');
            wrapper.classList.add('editor-wrapper');
            field.parentNode.insertBefore(wrapper, field);

            // Clone toolbar
            const toolbar = toolbarTemplate.cloneNode(true);
            toolbar.classList.remove('template');
            toolbar.style.display = 'none';
            
            // Create editor
            const editor = document.createElement('div');
            editor.setAttribute('contenteditable', 'true');
            editor.classList.add('form-control', 'rich-text-editor');
            editor.innerHTML = field.value || '';
            
            // Add to wrapper
            wrapper.appendChild(toolbar);
            wrapper.appendChild(editor);
            wrapper.appendChild(field); // Move original field into wrapper
            
            // Hide original field
            field.style.display = 'none';
            
            // Show/hide toolbar
            editor.addEventListener('focus', () => {
                toolbar.style.display = 'block';
            });
            
            editor.addEventListener('blur', (e) => {
                setTimeout(() => {
                    if (!toolbar.contains(document.activeElement)) {
                        toolbar.style.display = 'none';
                    }
                }, 100);
            });
            
            // Sync content
            editor.addEventListener('input', () => {
                field.value = editor.innerHTML;
            });
            
            // Toolbar buttons
            toolbar.querySelectorAll('button[data-command]').forEach(button => {
                button.addEventListener('click', (e) => {
                    e.preventDefault();
                    const command = button.getAttribute('data-command');
                    document.execCommand(command, false, null);
                    editor.focus();
                });
            });
        }

        // Initialize existing fields
        document.querySelectorAll('input[name^="section_title"], textarea[placeholder="Description (optional)"]')
            .forEach(createToolbarForField);
        
        // Add event delegation for new sections
        document.addEventListener('click', function(e) {
            if (e.target && e.target.matches('.add-section-btn')) {
                setTimeout(() => {
                    const newSection = e.target.closest('.section-block');
                    if (newSection) {
                        const fields = newSection.querySelectorAll('input[name^="section_title"], textarea[placeholder="Description (optional)"]');
                        fields.forEach(createToolbarForField);
                    }
                }, 100);
            }
        });
    }

    initializeToolbar();
});
</script>