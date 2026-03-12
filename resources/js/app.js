import './bootstrap';

import Alpine from 'alpinejs';
import { installThemeController } from './theme';

window.Alpine = Alpine;

installThemeController(Alpine);

Alpine.start();
