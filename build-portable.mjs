import { build } from 'vite';
import config from './vite.config.js';
// Portable build for environments without child-process execution.
await build({ ...config, configFile: false, esbuild: false, resolve: { preserveSymlinks: true }, build: { minify: false, cssMinify: false } });
