// Function to check if editor is loaded
function checkEditorLoaded(maxAttempts = 20, currentAttempt = 0) {
    console.log(`Checking for CKEditor (attempt ${currentAttempt + 1}/${maxAttempts})`);
    
    if (typeof ClassicEditor !== 'undefined') {
        console.log('CKEditor is loaded, initializing EditorManager');
        initializeEditorManager();
    } else if (currentAttempt < maxAttempts) {
        console.log('CKEditor not loaded yet, waiting...');
        setTimeout(() => checkEditorLoaded(maxAttempts, currentAttempt + 1), 200);
    } else {
        console.error('CKEditor failed to load after maximum attempts');
    }
}

// Function to initialize EditorManager
function initializeEditorManager() {
    if (window.EditorManager) {
        console.log('EditorManager already initialized');
        return;
    }
    
    // Simple editor management without wrapper
    window.EditorManager = {
        createEditor: function(element, config = {}) {
            console.log('Creating editor for element:', element);
            const defaultConfig = {
                toolbar: {
                    items: [
                        'heading',
                        '|',
                        'bold',
                        'italic',
                        'link',
                        'bulletedList',
                        'numberedList',
                        '|',
                        'indent',
                        'outdent',
                        '|',
                        'blockQuote',
                        'insertTable',
                        'undo',
                        'redo'
                    ]
                },
                placeholder: element.dataset.placeholder || 'Enter text here'
            };

            const editorConfig = { ...defaultConfig, ...config };
            try {
                return ClassicEditor
                    .create(element, editorConfig)
                    .then(editor => {
                        window.editorInstances.set(element, editor);
                        return editor;
                    });
            } catch (error) {
                console.error('Error creating editor:', error);
                return null;
            }
        },

        initializeEditor: async function(container) {
            console.log('Initializing editor for container:', container);
            if (!container || container.editorInstance) {
                console.log('Container is invalid or already has an editor');
                return null;
            }

            try {
                const editor = await this.createEditor(container);
                container.editorInstance = editor;
                
                console.log('Editor initialized successfully');
                return editor;
            } catch (error) {
                console.error('Error initializing editor:', error);
                return null;
            }
        },

        destroyEditor: function(container) {
            if (container.editorInstance) {
                container.editorInstance.destroy();
                window.editorInstances.delete(container);
                delete container.editorInstance;
            }
        }
    };

    console.log('EditorManager initialized, dispatching ready event');
    // Dispatch an event when manager is ready
    window.dispatchEvent(new Event('EditorManagerReady'));
}

// Start checking when window loads
window.addEventListener('load', function() {
    console.log('DOMContentLoaded in editor-management.js');
    checkEditorLoaded();
});