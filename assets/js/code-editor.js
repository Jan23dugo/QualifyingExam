class CodeEditor {
    constructor(language, containerId) {
        this.language = language;
        this.containerId = containerId;
        this.editor = null;
        this.init();
    }

    init() {
        require.config({ paths: { 'vs': 'https://cdn.jsdelivr.net/npm/monaco-editor@0.33.0/min/vs' }});
        require(['vs/editor/editor.main'], () => {
            this.editor = monaco.editor.create(document.getElementById(this.containerId), {
                value: this.getDefaultCode(),
                language: this.getMonacoLanguage(),
                theme: 'vs-dark',
                minimap: { enabled: false },
                automaticLayout: true,
                fontSize: 14,
                scrollBeyondLastLine: false,
                lineNumbers: true,
                lineHeight: 21
            });
        });
    }

    getMonacoLanguage() {
        const languageMap = {
            'python': 'python',
            'java': 'java',
            'c': 'c'
        };
        return languageMap[this.language.toLowerCase()] || 'plaintext';
    }

    getDefaultCode() {
        const templates = {
            'java': 'public class Main {\n    public static void main(String[] args) {\n        // Write your code here\n        \n    }\n}',
            'python': '# Write your code here\n',
            'c': '#include <stdio.h>\n\nint main() {\n    // Write your code here\n    \n    return 0;\n}'
        };
        return templates[this.language.toLowerCase()] || '// Write your code here\n';
    }

    getCode() {
        return this.editor ? this.editor.getValue() : '';
    }

    setCode(code) {
        if (this.editor) {
            this.editor.setValue(code);
        }
    }
} 