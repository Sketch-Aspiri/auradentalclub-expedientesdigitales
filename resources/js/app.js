import './bootstrap';
import { initNavigationOverlay } from './nav-loader';
import { initButtonLoading } from './button-loader';

// Los dos mecanismos de feedback de carga del sistema (CLAUDE.md §10 /
// .claude/agents/ux-ui-designer.md "Feedback de carga"). Vite empaqueta este script como
// módulo (comportamiento equivalente a `defer`), así que el DOM ya está parseado cuando
// se ejecuta este código — no hace falta esperar a `DOMContentLoaded`.
initNavigationOverlay();
initButtonLoading();
