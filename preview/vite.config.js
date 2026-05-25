import { defineConfig } from 'vite';

export default defineConfig({
  root: '.',
  base: '/dolibarr-ui-skin/',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
  },
});
