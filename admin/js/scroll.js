// Modern scroll animation handler with IntersectionObserver
class ScrollHandler {
    constructor() {
        this.observedElements = new Set();
        this.setupObserver();
        this.scanForElements();
    }

    setupObserver() {
        // Setup IntersectionObserver for scroll animations
        this.observer = new IntersectionObserver(
            entries => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                        // Optional: unobserve after animation
                        // this.observer.unobserve(entry.target);
                    }
                });
            },
            {
                root: null,
                rootMargin: '20px',
                threshold: 0.1
            }
        );
    }

    scanForElements() {
        // Find all elements that need scroll animations
        document.querySelectorAll('.scroll-animate, .ql-editor, .question-block, .section-block').forEach(element => {
            this.observeElement(element);
        });
    }

    observeElement(element) {
        if (!this.observedElements.has(element)) {
            this.observer.observe(element);
            this.observedElements.add(element);
        }
    }

    unobserveElement(element) {
        if (this.observedElements.has(element)) {
            this.observer.unobserve(element);
            this.observedElements.delete(element);
        }
    }

    destroy() {
        if (this.observer) {
            this.observer.disconnect();
        }
        this.observedElements.clear();
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        if (!window.scrollHandler) {
            window.scrollHandler = new ScrollHandler();
        }
    });
} else {
    if (!window.scrollHandler) {
        window.scrollHandler = new ScrollHandler();
    }
}
