import './bootstrap';
import { initDioramaAnimations } from './animations/diorama-animations';

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        initDioramaAnimations();
    });
} else {
    initDioramaAnimations();
}
