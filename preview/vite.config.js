import { defineConfig } from 'vite';

export default defineConfig({
  root: '.',
  base: '/dolibarr-novo-theme/',
  build: {
    outDir: 'dist',
    emptyOutDir: true,
  },
});
